<?php
namespace SUS\AdminBundle\Admin;

use SUS\SiteBundle\Entity\Circuits\PhoneCircuit;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class PhoneCircuitAdmin extends CircuitAdmin
{
    protected function configureShowField(ShowMapper $showMapper)
    {
        parent::configureShowField($showMapper);
        $showMapper
            ->add('connectivityType.name', 'trans')
            ->add('number')
            ->add('paidByPsd')
            ->add('bandwidthProfile', 'trans')
            ->add('realspeed', 'trans')
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        parent::configureFormFields($formMapper);
        $formMapper
            ->add('connectivityType', null, array('disabled' => !$this->circuitNoLease($this->getSubject()), 'query_builder' => $this->circuitNoLease($this->getSubject()) ? $this->getAllowedConnectivityTypes() : null, 'help' => ($this->circuitNoLease($this->getSubject())? '&nbsp;Επιτρέπονται μόνο τύποι που δεν εμπεριέχουν μίσθωση για το ΠΣΔ. <BR />&nbsp;Για άλλους τύπους πρέπει να δημιουργηθεί Αίτημα Νέου Κυκλώματος.' : '')))
        ;
        if ($this->id($this->getSubject()) && !$this->circuitNoLease($this->getSubject())) {
          // ONLY SHOW FIELDS IF SHOWING A NO-LEASE CIRCUIT
            $formMapper
                ->add('number')
                ->add('paidByPsd', null, array('required' => false, 'disabled' => !$this->circuitNoLease($this->getSubject())))
            ;
        }
        $formMapper
            ->add('bandwidthProfile', 'bandwidth_profile', array('disabled' => !$this->circuitNoLease($this->getSubject()), 'dependentProperty' => 'connectivityType', 'dependentField' => 'connectivityType'))
            ->add('realspeed')
        ;
    }

    private function getAllowedConnectivityTypes() {
        $ctRepository = $this->getModelManager()->getEntityManager('SUS\SiteBundle\Entity\Circuits\ConnectivityType')->getRepository('SUS\SiteBundle\Entity\Circuits\ConnectivityType');
        return $ctRepository->getConnectivityTypesQb(array('noLease' => true));
    }

    protected function configureListFields(ListMapper $listMapper) {
        parent::configureListFields($listMapper);
        $listMapper
            ->add('connectivityType.name', 'trans')
            ->add('number')
            ->add('bandwidthProfile', 'trans')
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        parent::configureDatagridFilters($datagridMapper);
        $datagridMapper
            ->add('number')
            ->add('paidByPsd')
            ->add('connectivityType')
        ;
    }

    public function getFilterParameters()
    {
        $this->datagridValues = array_merge(
            array('paidByPsd' => array(
                'type' => 2,
                'value' => 1
            )),
            $this->datagridValues
        );

        return parent::getFilterParameters();
    }

    public function getExportFields()
    {
        return array_merge(parent::getExportFields(),array(
            //'connectivityType.name',
            'number',
            'paidByPsd',
            'bandwidthProfile',
            'realspeed',
        ));
    }
}