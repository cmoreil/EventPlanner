<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Event;
use App\Entity\Location;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UpdateEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('name', TextType::class, [
                'required' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Ce champ est obligatoire !']),
                    new Length([
                        'min' => 1,
                        'max' => 255
                    ])
                ]])
            ->add('startDateTime', DateTimeType::class, [
                'required' => false,
                'html5' => true,
                'widget' => 'single_text'
            ])
            ->add('endDateTime', DateTimeType::class, [
                'required' => false,
                'html5' => true,
                'widget' => 'single_text'
            ])
            ->add('registrationLimit', DateTimeType::class, [
                'required' => false,
                'html5' => true,
                'widget' => 'single_text'
            ])
            ->add('maxCapacity', IntegerType::class, [
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Ce champ est obligatoire !']),
                    new Length([
                        'min' => 1,
                        'max' => 2500
                    ])
                ]])
            ->add('city', EntityType::class, [
                'mapped' => false,
                'required' => false,
                'class' => City::class,
                'choice_label' => 'name',
                'label' => "Ville",
                'attr' => ['class' => 'city_label']
            ])
        ;
        // ajout d'un event listener sur ville + appel du champ imbriqué lieu
        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event) {
                $data = $event->getData();
                //dd($data);
                 /**
                  * @var  Location $loc
                  */
                $loc = $data->getLocation();
                $form = $event->getForm();
                if ($loc){
                    /**
                     * @var City $city
                     */
                    $city = $loc->getCity();
                    $this->addLocationField($form, $city);
                    $form->get('city')->setData($city);
                }else{
                    $this->addLocationField($form,  null);
                }
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }

    private function addLocationField(FormInterface $form, ?City $city)
    {
        $builder = $form->getConfig()->getFormFactory()->createNamedBuilder(
            'location',
            EntityType::class,
            null, [
                'mapped' => false,
                'class' => Location::class,
                'choice_label' => 'name',
                'required' => false,
                'auto_initialize' => false,
                'choices' =>$city ?  $city->getLocations() : []
            ]
        );
        $form->add($builder->getForm());
    }
}
