<?php

namespace App\Form;

use App\Entity\Appointment;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Appointment1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('coach', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'label' => 'Sélectionnez le coach pour le rendez-vous',
                'placeholder' => 'Sélectionnez un coach',
                'query_builder' => function (UserRepository $repo) {
                       return $repo->createQueryBuilder('u')
                        ->andWhere('u.roles LIKE :role')
                        ->setParameter('role', '%ROLE_COACH%');
                },
                'attr' => [
                    'class' => 'form-control',
                ],
            ])

            // Champ pour sélectionner l'étudiant
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',  // Afficher l'email de l'utilisateur
                'label' => 'Sélectionnez l\'étudiant pour le rendez-vous',
                'placeholder' => 'Sélectionnez un étudiant',
                'query_builder' => function (UserRepository $repo) {
                    // Filtrer uniquement les utilisateurs ayant le rôle ROLE_STUDENT
                    return $repo->createQueryBuilder('u')
                        ->andWhere('u.roles LIKE :role')
                        ->setParameter('role', '%ROLE_STUDENT%');
                },
                'attr' => [
                    'class' => 'form-control',
                ],
            ])

            // Champ pour la date du rendez-vous
            ->add('appointment_date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date du rendez-vous',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])

            // Champ pour l'heure du rendez-vous
            ->add('appointment_time', TimeType::class, [
                'widget' => 'single_text',
                'label' => 'Heure du rendez-vous',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])

            // Champ pour l'objet du rendez-vous
            ->add('object', TextareaType::class, [
                'label' => 'Objet du rendez-vous',
                'attr' => [
                    'rows' => 5,
                    'class' => 'form-control',
                    'placeholder' => 'Entrez l\'objet du rendez-vous',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Appointment::class,
        ]);
    }
}

            // ->add('user', EntityType::class, [
            //     'class' => User::class,
            //     'choice_label' => 'id',
            // ])
            // ->add('user', EntityType::class, [
            //     'class' => User::class,
            //     'choice_label' => function (User $user) {
            //     $name = $user->getProfile() ? $user->getProfile()->getName() : 'No Name';
            //     $postname = $user->getProfile() ? $user->getProfile()->getPostname() : 'No Postname';
            //     return $name . ' ' . $postname;
            // ])
            // },
