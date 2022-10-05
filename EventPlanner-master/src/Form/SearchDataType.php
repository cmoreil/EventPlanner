<?php

namespace App\Form;

use App\Entity\SearchData;
use App\Entity\Site;
use App\Repository\SiteRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchDataType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('referentSite', EntityType::class, [
                'class' => Site::class,
                'placeholder' => 'Choose a referent site',
                'required' => false,
                'choice_label' => 'name',
                'query_builder' => function (SiteRepository $sr) {
                return $sr->createQueryBuilder('s')
                            ->orderBy('s.name','ASC');
                }
            ])
            ->add('eventNameContains', TextType::class, [
                'label' => 'The name of the event contains ',
                'required' => false
            ])
            ->add('fromSearchDateTime', DateType::class, [
                'label' => 'From',
                'widget' => 'single_text',
                'html5' => true,
                'required' => false
            ])
            ->add('toSearchDateTime', DateType::class, [
                'label' => 'To',
                'widget' => 'single_text',
                'html5' => true,
                'required' => false
            ])
            ->add('connectedUserIsOrganizing', CheckboxType::class, [
                'label' => 'Events I am organizing',
                'required' => false
                ])
            ->add('connectedUserIsRegistered', CheckboxType::class, [
                'label' => 'Events I registered',
                'required' => false
            ])
            ->add('connectedUserIsNotRegistered', CheckboxType::class, [
                'label' => 'Events I did not registered',
                'required' => false
            ])
            ->add('cancelledEvents', CheckboxType::class, [
                'label' => 'Cancelled events',
                'required' => false
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SearchData::class,
        ]);
    }
}
