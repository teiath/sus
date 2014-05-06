<?php
namespace SUS\AdminBundle\Admin\Helpdesk;

use SUS\AdminBundle\Admin\PhoneCircuitAdmin as BasePhoneCircuitAdmin;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;

class PhoneCircuitAdmin extends BasePhoneCircuitAdmin
{
    protected $baseRouteName = 'admin_lms_circuit_user';
    protected $baseRoutePattern = 'circuit_user';

    protected $securityContext;

    protected function configureListFields(ListMapper $listMapper) {
        parent::configureListFields($listMapper);
        $listMapper->remove('unit.fy');
    }

    /*public function createQuery($context = 'list')
    {
        if($context === 'list') {
            // User should only see the lines belong to their FY
            $fyName = $this->securityContext->getToken()->getUser()->getUnit()->getFy()->getName();
            $repository = $this->getModelManager()->getEntityManager($this->getClass())->getRepository($this->getClass());

            $qb = $repository->createQueryBuilder('c')
                    ->join('c.unit', 'u')
                    ->andWhere('u.fyName = :fyName')
                    ->setParameter('fyName', $fyName)
            ;

            return new ProxyQuery($qb);
        }
    }*/

    public function setSecurityContext(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }
}