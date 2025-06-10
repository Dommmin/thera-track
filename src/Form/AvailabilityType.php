<?php

namespace App\Form;

use App\Entity\Availability;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AvailabilityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dayOfWeek', ChoiceType::class, [
                'choices' => [
                    'Monday' => '1',
                    'Tuesday' => '2',
                    'Wednesday' => '3',
                    'Thursday' => '4',
                    'Friday' => '5',
                    'Saturday' => '6',
                    'Sunday' => '7',
                ],
                'label' => 'Day of Week',
            ])
            ->add('startHour', TimeType::class, [
                'input' => 'datetime',
                'widget' => 'single_text',
                'label' => 'Start Time',
            ])
            ->add('endHour', TimeType::class, [
                'input' => 'datetime',
                'widget' => 'single_text',
                'label' => 'End Time',
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