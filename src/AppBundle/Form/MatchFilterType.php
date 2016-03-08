<?php

namespace AppBundle\Form;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MatchFilterType extends AbstractType
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
    }

    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        $categories = [];
        $areas = [];
        foreach ($this->em->getRepository('AppBundle:Category')->findAll() as $cat) {
            $categories[$cat->getId()] = $cat->getName();
        }
        foreach ($this->em->getRepository('AppBundle:Municipality')->findAll() as $area) {
            $areas[$area->getId()] = $area->getName();
        }
        $form
            ->add('category', 'choice', [
                'label' => 'Interests',
                'placeholder' => 'All',
                'choices' => $categories,
                'mapped' => false,
            ])
            ->add('ageFrom', 'choice', [
                'label' => 'Age From',
                'data' => 0,
                'choices' => range(18, 100),
                'mapped' => false,
            ])
            ->add('ageTo', 'choice', [
                'label' => 'Age To',
                'data' => 82,
                'choices' => range(18, 100),
                'mapped' => false,
            ])
            ->add('gender', 'choice', [
                'label' => 'Gender',
                'placeholder' => 'All',
                'choices' => ['M' => 'Man', 'W' => 'Woman'],
                'mapped' => false,
            ])
            ->add('hasChildren', 'choice', [
                'label' => 'Children',
                'placeholder' => 'Doesn\'t matter',
                'choices' => ['0' => 'No', '1' => 'Yes'],
                'mapped' => false,
            ])
            ->add('from', 'country', [
                'label' => 'Country',
                'placeholder' => 'All',
                'mapped' => false,
            ])
            ->add('municipality', 'choice', [
                'label' => 'Area',
                'placeholder' => 'All',
                'choices' => $areas,
                'mapped' => false,
            ])
            ->add('musicFriend', 'choice', [
                'label' => 'Type',
                'placeholder' => 'Any',
                'choices' => ['0' => 'Fikabuddy', '1' => 'Musicbuddy'],
                'mapped' => false,
            ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
    }

    public function getName()
    {
        return 'matchFilter';
    }
}
