<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Teams;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer une adresse email.',
                    ]),
                ],
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Choisissez un rôle',
                'choices' => [
                    'Administrateur' => 'ROLE_ADMIN',
                    'Coach' => 'ROLE_COACH',
                    'Etudiant' => 'ROLE_STUDENT',
                ],
                'multiple' => true, // Permet de sélectionner plusieurs rôles
                'expanded' => true, // Rendre les choix comme des cases à cocher
                'constraints' => [
                    new Callback(function ($roles, ExecutionContextInterface $context) {
                        if (count($roles) !== 1) {
                            $context->buildViolation('L\'utilisateur doit avoir un seul rôle : Administrateur, Coach ou Étudiant.')
                                ->addViolation();
                        }
                    }),
                ],
            ])
            ->add('teams', EntityType::class, [
                'class' => Teams::class,
                'choice_label' => 'team', // Vérifiez que 'team' est une propriété correcte dans l'entité Teams
                'label' => 'Team',
                'placeholder' => '-- Sélectionnez une équipe --',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner une équipe.',
                    ]),
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'required' => false, // Rendre optionnel
                'empty_data' => '', // Pour éviter que null soit transmis
            ])
            ->add('profile', ProfileType::class, [
                'label' => 'Profile',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
