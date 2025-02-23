<?php

namespace App\Form;

use App\Entity\Profile;
use App\Enum\SexeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le nom',
                ],
            ])
            ->add('postname', TextType::class, [
                'label' => 'Post-nom',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le post-nom ou deuxième nom',
                ],
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le prénom',
                ],
            ])
            ->add('genre', ChoiceType::class, [
                'label' => 'Sexe',
                'choices' => [
                    'Masculin' => SexeEnum::Male,
                    'Féminin' => SexeEnum::Female,
                    'Autre' => SexeEnum::Other,
                ],
                'expanded' => true,
                'multiple' => false,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('phone', TextType::class, [
                'label' => 'Téléphone',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le numéro de téléphone',
                ],
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez l\'adresse',
                ],
            ])
            ->add('date_of_birth', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'AAAA-MM-JJ',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'La date de naissance est obligatoire.',
                    ]),
                    new LessThanOrEqual([
                        'value' => (new \DateTime())->modify('-16 years'), // Date maximale pour avoir au moins 16 ans
                        'message' => 'L\'utilisateur doit avoir au moins 16 ans.',
                    ]),
                ],
            ])
            ->add('date_inscrit', DateType::class, [
                'label' => 'Date d\'inscription',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'AAAA-MM-JJ',
                    'max' => (new \DateTime())->format('Y-m-d'), // HTML5 validation côté client
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'La date d\'inscription est obligatoire.',
                    ]),
                    new LessThanOrEqual([
                        'value' => new \DateTime(), // Date actuelle
                        'message' => 'La date d\'inscription ne peut pas être supérieure à aujourd\'hui.',
                    ]),
                ],
            ])
            ->add('passport', PassportType::class, [
                'label' => 'Passeport',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Profile::class,
        ]);
    }
}
