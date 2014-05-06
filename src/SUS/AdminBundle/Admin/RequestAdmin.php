<?php
namespace SUS\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;

abstract class RequestAdmin extends Admin
{
    protected $datagridValues = array(
        '_sort_order' => 'ASC', // Descendant ordering (default = 'ASC')
        '_sort_by' => 'id' // name of the ordered field (default = the model id
    );

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('acl')
        ;
    }

    /**
     * @param \Sonata\AdminBundle\Show\ShowMapper $showMapper
     *
     * @return void
     */
    protected function configureShowField(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id', 'string')
            ->add('createdBy')
            ->add('status', 'trans')
        ;
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     *
     * @return void
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('comments')
        ;
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\ListMapper $listMapper
     *
     * @return void
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array('template' => 'SUSAdminBundle:Button:show_button.html.twig'),
                    'edit' => array('template' => 'SUSAdminBundle:Button:edit_button.html.twig'),
            )))
            ->addIdentifier('id')
            ->add('status', 'trans')
        ;
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\DatagridMapper $datagridMapper
     *
     * @return void
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('createdBy', null, array(), null, array('attr' => array('placeholder' => 'LDAP Username')))
            ->add('status', null, array(), 'requeststatus', array('class' => $this->getClass(), 'confirmApproved' => false))
        ;
    }

    public function getExportFields()
    {
        return array(
            'id',
            'unit.name',
            'unit.state',
            'unit.mmId',
            'unit.categoryName',
            'unit.fy',
            'createdBy',
            'status',
        );
    }

    public function getBatchActions()
    {
        return array();
    }
}