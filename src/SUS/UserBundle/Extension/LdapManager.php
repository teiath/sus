<?php

namespace SUS\UserBundle\Extension;

use FR3D\LdapBundle\Ldap\LdapManager as BaseLdapManager;

use FR3D\LdapBundle\Driver\LdapDriverInterface;
use FR3D\LdapBundle\Model\LdapUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

class LdapManager extends BaseLdapManager
{
    protected $container;

    public function __construct(LdapDriverInterface $driver, $userManager, array $params, $container)
    {
        $this->container = $container;
        parent::__construct($driver, $userManager, $params);
    }

    public function findUserBy(array $criteria)
    {
        if (($entries = $this->container->get('cache')->fetch('sus_ldapentry_'.$criteria['uid']))) {
            // Entries are loaded from the cache
        } else {
            $filter  = $this->buildFilter($criteria);
            $entries = $this->driver->search($this->params['baseDn'], $filter, $this->ldapAttributes);
            if ($entries['count'] > 1) {
                throw new \Exception('This search can only return a single user');
            }

            if ($entries['count'] == 0) {
                return false;
            }
            $this->container->get('cache')->save('sus_ldapentry_'.$criteria['uid'], $entries);
        }
        $user = $this->userManager->createUser();
        $this->hydrate($user, $entries[0]);

        return $user;
    }

    protected function hydrate(UserInterface $user, array $entry)
    {
        // Object isn't cached
        parent::hydrate($user, $entry);
        $em = $this->container->get('doctrine')->getManager();
        $em->getConnection()->executeQuery('DELETE FROM Users WHERE username = "'.$entry['uid'][0].'"');
        // If the user is has the eduPerson objectClass then they get ROLE_USER
        /*$kedo = false;
        if(isset($entry['memberof'])) {
            $groups = explode(';', $entry['memberof'][0]);
            if(in_array('lms', $groups)) {
                $kedo = true;
            }
        }
        if($kedo == true) {
            $user->setRoles(array('ROLE_KEDO'));
        } else {
            $user->setRoles(array('ROLE_HELPDESK'));
        }
        // Set the unit
        $mmservice = $this->container->get('sus.mm.service');
        $unit = $mmservice->findOneUnitBy(array(
            'ldapuid' => $entry['uid'][0],
        ));
        if(count($unit) > 0) {
            $user->setUnit($unit);
        } else {
            throw new \Exception('Δεν βρέθηκε η μονάδα (mm_id) του χρήστη');
        }*/
    }
}