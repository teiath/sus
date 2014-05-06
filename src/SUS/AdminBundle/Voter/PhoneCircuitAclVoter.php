<?php
namespace SUS\AdminBundle\Voter;

use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Acl\Voter\AclVoter;

use SUS\SiteBundle\Entity\Circuits\PhoneCircuit;
use Sonata\AdminBundle\Admin\Pool;

class PhoneCircuitAclVoter extends AclVoter
{
    protected $pool;

    public function __construct($aclProvider, $oidRetrievalStrategy, $sidRetrievalStrategy, $permissionMap, Pool $pool, $logger = null, $allowIfObjectIdentityUnavailable = true)
    {
        $this->pool = $pool;
        return parent::__construct($aclProvider, $oidRetrievalStrategy, $sidRetrievalStrategy, $permissionMap, $logger, $allowIfObjectIdentityUnavailable);
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if(($user = $token->getUser()) instanceof UserInterface && $object instanceof PhoneCircuit) {
            // All users should be able to directly edit noLease circuits
            foreach ($attributes as $attribute) {
                if($attribute == 'VIEW') {
                    return self::ACCESS_GRANTED;
                } else if($attribute == 'EDIT' || $attribute == 'DELETE') {
                    if($object->getConnectivityType()->getNoLease() == true) {
                        return self::ACCESS_GRANTED;
                    }
                }
            }
            // Helpdesk user can do nothing at this point so we deny
            if($user->hasRole('ROLE_HELPDESK')) {
                return self::ACCESS_DENIED;
            } else if($user->hasRole('ROLE_KEDO')) {
                if($attribute == 'EDIT' || $attribute == 'DELETE') {
                    // Kedo user can edit if the circuit is not finalized
                    $admin = $this->pool->getAdminByAdminCode('sonata.admin.phonecircuits.kedo');
                    if(!$admin->circuitNoLease($object) && $admin->circuitFinalized($object)) {
                        return self::ACCESS_DENIED;
                    }
                }
            }
        }
        return self::ACCESS_ABSTAIN;
    }
}