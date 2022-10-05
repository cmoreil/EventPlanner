<?php

namespace App\Form;

use App\Entity\Site;
use App\Entity\User;
use App\Repository\SiteRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class)
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('phoneNumber', TextType::class)
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class, [
                'mapped'=>false,
                'required'=>false
            ])
            ->add('confirmPassword', PasswordType::class, [
                'mapped'=>false,
                'required'=>false
            ])
            ->add('site', EntityType::class, [
                'class'=>Site::class,
                'choice_label'=>'name'
            ])
            ->add('picture', FileType::class, [
                'mapped'=>false,
                'required'=>false,
                'constraints'=>[
                    new Image([
                        'maxSize'=>'10000k',
                        'maxSizeMessage'=>'file too large ! ({{limit}} max)',
                        'mimeTypesMessage'=>'Image format not allowed'
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
