<?php

namespace App\Form;

use App\Entity\Passport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Doctrine\ORM\EntityManagerInterface;

class PassportType extends AbstractType
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $entityManager = $this->entityManager; // Utilisation de l'EntityManager injecté

        $builder
            ->add('number_passport', TextType::class, [
                'label' => 'Numéro de passeport',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le numéro de passeport est obligatoire.',
                    ]),
                    new Callback(function ($value, ExecutionContextInterface $context) use ($entityManager) {
                        // Récupérer le formulaire et l'entité Passport associée
                        $passport = $context->getRoot()->getData();

                        // Rechercher un passeport existant avec le même numéro
                        $existingPassport = $entityManager
                            ->getRepository(Passport::class)
                            ->findOneBy(['number_passport' => $value]);

                        // Vérifier si un autre passeport utilise ce numéro
                        if ($existingPassport && $existingPassport->getId() !== $passport->getId()) {
                            $context->buildViolation('Ce numéro de passeport est déjà utilisé.')
                                ->addViolation();
                        }
                    }),
                ],
            ])
            ->add('date_expiration', DateType::class, [
                'label' => 'Date d\'expiration',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'constraints' => [
                    new GreaterThan([
                        'value' => 'today',
                        'message' => 'La date d\'expiration doit être supérieure à la date actuelle.',
                    ]),
                ],
            ])
            ->add('nationalite', TextType::class, [
                'label' => 'Nationalité',
            ])
            ->add('profession', TextType::class, [
                'label' => 'Profession',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Passport::class,
        ]);
    }
}
