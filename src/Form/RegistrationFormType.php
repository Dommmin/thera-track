<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
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
            ->add('plainPassword', PasswordType::class, [
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
            ->add('userType', ChoiceType::class, [
                'choices' => [
                    'Patient' => 'ROLE_USER',
                    'Therapist' => 'ROLE_THERAPIST'
                ],
                'expanded' => true,
                'multiple' => false,
                'mapped' => false,
                'label_attr' => ['class' => 'label'],
                'label' => 'I want to register as',
                'attr' => ['class' => 'flex gap-4']
            ])
            ->add('hourlyRate', NumberType::class, [
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'input input-bordered w-full',
                    'placeholder' => 'Enter your hourly rate',
                    'min' => 0,
                    'step' => 0.01
                ],
                'label_attr' => ['class' => 'label'],
                'label' => 'Hourly Rate (€)'
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'attr' => ['class' => 'checkbox checkbox-primary'],
                'label_attr' => ['class' => 'label cursor-pointer'],
                'label' => 'I agree to the terms and conditions'
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
