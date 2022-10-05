<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Location;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class LocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'This field is required']),
                    new Length([
                        'min' => 1,
                        'max' => 255
                    ])
                ]])
            ->add('street', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'This field is required']),
                    new Length([
                        'min' => 1,
                        'max' => 255
                    ])
                ]])
            ->add('latitude', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'This field is required']),
                    new Length([
                        'min' => 1,
                        'max' => 50
                    ])
                ]])
            ->add('longitude', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'This field is required']),
                    new Length([
                        'min' => 1,
                        'max' => 50
                    ])
                ]])
            ->add('city', EntityType::class, [
                'required' => true,
                'class' => City::class,
                'choice_label' => 'name',
                'label' => "Ville",
                'attr' => ['class' => 'city_label'],
                'placeholder' => 'Choisir une ville'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Location::class,
        ]);
    }

}
