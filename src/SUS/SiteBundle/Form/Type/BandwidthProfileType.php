<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SUS\SiteBundle\Form\Type;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;

use SUS\SiteBundle\Form\DataTransformer\EntityToIntTransformer;

class BandwidthProfileType extends AbstractType
{
    protected $entityToIdTransformer;

    public function __construct(EntityToIntTransformer $entityToMmIdTransformer) {
        $this->entityToIdTransformer = $entityToMmIdTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->entityToIdTransformer->setEntityClass('SUS\SiteBundle\Entity\Circuits\BandwidthProfile');
        $this->entityToIdTransformer->setEntityType('SUS\SiteBundle\Entity\Circuits\BandwidthProfile');
        $this->entityToIdTransformer->setEntityRepository('SUS\SiteBundle\Entity\Circuits\BandwidthProfile');
        $builder->addViewTransformer($this->entityToIdTransformer);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['filters'] = $options['filters'];
        if(isset($options['dependentField'])) {
            $id = $view->parent->children[$options['dependentField']]->vars['id'];
            if($id == '') {
                throw new \Exception('Dependent id not found');
            }
            $view->vars['dependentProperty'] = $options['dependentProperty'];
            $view->vars['dependentField'] = $options['dependentField'];
            $view->vars['dependentFieldId'] = $id;
        }
    }


    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'configs' => array(
                'path' => 'get_bandwidth_profiles',
                'field_id' => 'id',
                'field_name' => 'bandwidth',
                'ajax' => array(
                    'quietMillis' => 300,
                ),
            ),
            'class' => null,
            'filters' => array(),
        ));
        $resolver->setRequired(array(
            'dependentProperty',
            'dependentField',
        ));
    }

    public function getParent() {
        return 'genemu_jqueryselect2_hidden';
    }

    public function getName()
    {
        return 'bandwidth_profile';
    }
}
