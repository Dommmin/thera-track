<?php

namespace App\Form;

use App\Entity\Specialization;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class TherapistRegistrationForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'input input-bordered w-full',
                    'placeholder' => 'Enter your email'
                ],
                'label_attr' => ['class' => 'label'],
                'label' => 'Email'
            ])
            ->add('phone', TextType::class, [
                'attr' => [
                    'class' => 'input input-bordered w-full',
                    'placeholder' => 'Enter your phone number'
                ],
                'label_attr' => ['class' => 'label'],
                'label' => 'Phone Number'
            ])
            ->add('firstName', TextType::class, [
                'attr' => [
                    'class' => 'input input-bordered w-full',
                    'placeholder' => 'Enter your first name'
                ],
                'label_attr' => ['class' => 'label'],
                'label' => 'First Name'
            ])
            ->add('lastName', TextType::class, [
                'attr' => [
                    'class' => 'input input-bordered w-full',
                    'placeholder' => 'Enter your last name'
                ],
                'label_attr' => ['class' => 'label'],
                'label' => 'Last Name'
            ])
            ->add('hourlyRate', IntegerType::class, [
                'attr' => [
                    'class' => 'input input-bordered w-full',
                    'placeholder' => 'Enter your hourly rate'
                ],
                'label_attr' => ['class' => 'label'],
                'label' => 'Hourly Rate'
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'attr' => [
                        'class' => 'input input-bordered w-full',
                        'placeholder' => 'Enter your password',
                        'autocomplete' => 'new-password'
                    ],
                    'label_attr' => ['class' => 'label'],
                    'label' => 'Password',
                ],
                'second_options' => [
                    'attr' => [
                        'class' => 'input input-bordered w-full',
                        'placeholder' => 'Repeat your password',
                        'autocomplete' => 'new-password'
                    ],
                    'label_attr' => ['class' => 'label'],
                    'label' => 'Repeat Password',
                ],
                'invalid_message' => 'The password fields must match.',
                'mapped' => false,
                'attr' => [
                    'class' => 'input input-bordered w-full',
                    'placeholder' => 'Enter your password',
                    'autocomplete' => 'new-password'
                ],
                'label_attr' => ['class' => 'label'],
                'label' => 'Password',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'attr' => ['class' => 'checkbox checkbox-primary'],
                'label_attr' => ['class' => 'label cursor-pointer'],
                'label' => 'I agree to the terms and conditions'
            ])
            ->add('bio', TextareaType::class, [
                'attr' => [
                    'class' => 'textarea textarea-bordered w-full',
                    'placeholder' => 'Enter your bio'
                ],
                'label_attr' => ['class' => 'label'],
                'label' => 'Bio'
            ])
            ->add('specialization', EntityType::class, [
                'class' => Specialization::class,
                'choice_label' => 'name',
                'label' => 'Specialization',
                'attr' => [
                    'class' => 'select select-bordered w-full',
                ],
                'label_attr' => ['class' => 'label'],
                'placeholder' => 'Select specialization'
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
