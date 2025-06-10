<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class SettingsForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class)
            ->add('firstName')
            ->add('lastName')
            ->add('phone', TextType::class, [
                'attr' => [
                    'placeholder' => '+48 123 456 789'
                ]
            ])
            ->add('avatarFile', FileType::class, [
                'label' => 'Profile Picture',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image file (JPEG or PNG)',
                    ])
                ],
            ]);

            if (in_array('ROLE_THERAPIST', $builder->getData()->getRoles(), true)) {
                $builder
                    ->add('hourlyRate', IntegerType::class)
                    ->add('bio', TextareaType::class, [
                        'attr' => [
                            'placeholder' => 'Enter your bio'
                        ]
                    ])
                    ->add('location', TextType::class, [
                        'required' => false,
                        'attr' => [
                            'placeholder' => 'City or address',
                        ],
                        'label' => 'Lokalizacja',
                    ])
                    ->add('latitude', TextType::class, [
                        'required' => false,
                        'attr' => [
                            'readonly' => true,
                            'class' => 'd-none',
                        ],
                        'label' => false,
                    ])
                    ->add('longitude', TextType::class, [
                        'required' => false,
                        'attr' => [
                            'readonly' => true,
                            'class' => 'd-none',
                        ],
                        'label' => false,
                    ]);
            }
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
