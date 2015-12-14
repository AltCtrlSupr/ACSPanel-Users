<?php

namespace ACS\ACSPanelBundle\Form;

use ACS\ACSPanelBundle\Form\DataTransformer\UserToStringTransformer;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

class UserAllowedServiceType extends AbstractType
{
    public $entity;
    public $em;

    public function __construct($entity, $em)
    {
        $this->entity = $entity;
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entity = $this->entity;
        $id = $entity->getId();

        // this assumes that the entity manager was passed in as an option
        $entityManager = $this->em;
        $transformer = new UserToStringTransformer($entityManager);

        $builder
            ->add('uservices', 'entity', array(
                'class' => 'ACS\ACSPanelBundle\Entity\Service',
                'label' => 'Select a plan:',
                'mapped' => false
            ))
        ;
    }

    public function getName()
    {
        return 'acs_acspanelbundle_userplantype';
    }
}
