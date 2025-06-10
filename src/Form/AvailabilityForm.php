<?php

namespace App\Form;

use App\Entity\Availability;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AvailabilityForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startHour', ChoiceType::class, [
                'choices' => array_combine(range(9, 17), range(9, 17)),
                'label' => 'Start Hour',
                'placeholder' => 'Select start hour',
            ])
            ->add('endHour', ChoiceType::class, [
                'choices' => array_combine(range(10, 18), range(10, 18)),
                'label' => 'End Hour',
                'placeholder' => 'Select end hour',
            ])
            ->add('dayOfWeek', ChoiceType::class, [
                'choices' => [
                    'Monday' => 1,
                    'Tuesday' => 2,
                    'Wednesday' => 3,
                    'Thursday' => 4,
                    'Friday' => 5,
                ],
                'label' => 'Day of Week',
                'placeholder' => 'Select day',
            ])
            ->add('excludedDate', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => 'Exclude Date',
                'help' => 'Add a date when you won\'t be available',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Availability::class,
        ]);
    }
} 
