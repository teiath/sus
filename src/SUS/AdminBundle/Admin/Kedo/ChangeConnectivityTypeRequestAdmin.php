<?php
namespace SUS\AdminBundle\Admin\Kedo;

use SUS\AdminBundle\Admin\ChangeConnectivityTypeRequestAdmin as BaseChangeConnectivityTypeRequestAdmin;
use Sonata\AdminBundle\Form\FormMapper;

class ChangeConnectivityTypeRequestAdmin extends BaseChangeConnectivityTypeRequestAdmin
{
    protected $baseRouteName = 'admin_lms_changeconnectivitytyperequest_kedo';
    protected $baseRoutePattern = 'changeconnectivitytyperequest_kedo';

    protected function configureFormFields(FormMapper $formMapper)
    {
        parent::configureFormFields($formMapper);
        $formMapper
            ->add('newConnectivityType', null, array('disabled' => true, 'query_builder' => $this->getServiceConnectivityTypes()))
            ->add('newBandwidthProfile', 'bandwidth_profile', array('disabled' => true, 'dependentProperty' => 'connectivityType', 'dependentField' => 'newConnectivityType'))
            ->add('status', 'requeststatus', array('class' => $this->getClass()))
        ;
    }
}