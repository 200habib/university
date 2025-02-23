<?php

namespace App\Form;

use App\Entity\Documents;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Validator\Constraints\File;


class DocumentsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file_path', VichFileType::class, [
                'label' => 'Document',
                'required' => true,
                'allow_delete' => false,
                'download_uri' => true, // Afficher le lien de téléchargement si le fichier existe déjà
            'constraints' => [
                new File([
                    'maxSize' => '1000M', // Ajustez la taille ici, par exemple 10 MiB
                    'mimeTypes' => [
                        'application/pdf',
                        'image/jpeg',
                        'image/png',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid file (PDF, JPEG, or PNG).',
                    'maxSizeMessage' => 'Le fichier est trop volumineux. La taille maximale autorisée est de 50 MiB.', 
                ]),
            ],
            ])
            ->add('coach', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'label' => false,
                'choices' => $options['users'],
                'placeholder' => 'Choisissez un destinataire',
                'required' => false, // Permet de ne pas sélectionner de coach si ce n'est pas obligatoire
            ]);
            // ->add('save', SubmitType::class, [
            //     'label' => 'Mettre à jour',
            //     'attr' => ['class' => 'bg-[#14213D] text-white py-2 px-4 rounded hover:bg-[#b49047] transition duration-300'],
            // ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Documents::class,
            'users' => [], // Ajout de la variable 'users' pour être passée depuis le contrôleur
        ]);
    }
}
