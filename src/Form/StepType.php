<?php

// App\Form\StepType.php

namespace App\Form;

use App\Entity\Step;
use App\Entity\Documents;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $step = $options['data'] ?? null;
        $userEmail = null;

        if ($step && $step->getDocuments()) {
            $userEmail = $step->getDocuments()->getUserEmail();
        }

        $builder
            ->add('title')
            ->add('description')
            ->add('documents', EntityType::class, [
                'class' => Documents::class,
                'choice_label' => function (Documents $document) {
                    // Vérifiez si l'utilisateur existe avant d'appeler getEmail()
                    $user = $document->getUser();
                    $userEmail = $user ? $user->getEmail() : 'Email non disponible';
                    return $document->getFileName() . ' = ' . $userEmail;
                },
                'placeholder' => 'Choisissez un document',
                'required' => true,
                'attr' => [
                    'class' => 'shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline'
                ]
                ]);
            // ->add('user', EntityType::class, [
            //     'class' => User::class,
            //     'choice_label' => 'email',
            //     'placeholder' => 'Choisissez un utilisateur',
            //     'required' => true,
            //     'data' => $step ? ($step->getDocuments() ? $step->getDocuments()->getUser() : null) : null,
            //     'attr' => [
            //         'class' => 'shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline'
            //     ]
            // ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Step::class,
        ]);
    }
}
