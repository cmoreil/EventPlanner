<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Event;
use App\Entity\Location;
use App\Repository\CityRepository;
use App\Repository\LocationRepository;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label_attr' => [
                    'class' => "col-sm-8"
                ],
                'constraints' => [
                    new NotBlank(['message' => 'This field is required']),
                    new Length([
                        'min' => 1,
                        'max' => 255
                    ])
                ]])
            ->add('startDateTime', DateTimeType::class, [
                'by_reference' => true,
                'required' => false,
                'html5' => true,
                'widget' => 'single_text',
                'label_attr' => [
                    'class' => "col-sm-8"
                ]
            ])
            ->add('endDateTime', DateTimeType::class, [
                'by_reference' => true,
                'required' => false,
                'html5' => true,
                'widget' => 'single_text',
                'label_attr' => [
                    'class' => "col-sm-8"
                ]
            ])
            ->add('registrationLimit', DateTimeType::class, [
                'by_reference' => true,
                'required' => false,
                'html5' => true,
                'widget' => 'single_text',
                'label_attr' => [
                    'class' => "col-sm-8"
                ]
            ])
            ->add('maxCapacity', IntegerType::class, [
                'required' => false,
                'label_attr' => [
                    'class' => "col-sm-8"
                ]
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label_attr' => [
                    'class' => "col-sm-8"
                ],
                'constraints' => [
                    new NotBlank(['message' => 'This field is required !']),
                    new Length([
                        'min' => 1,
                        'max' => 2500
                    ])
                ]])
            ->add('city', EntityType::class, [
                'required' => false,
                'mapped' => false,
                'class' => City::class,
                'choice_label' => 'name',
                'label' => "City",
                'attr' => ['class' => 'city_label'],
                'placeholder' => 'Chose a city'
            ])
            ->add('location', ChoiceType::class, [
                'required' => false,
                'mapped' => false,
                'label'=>'Location',
                'placeholder' => 'Chose a location'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
