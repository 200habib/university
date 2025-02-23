<?php

// src/Command/CreateAdminCommand.php

namespace App\Command;

use App\Entity\User;
use App\Entity\Profile;
use App\Entity\Passport;
// Importer l'entité Passport
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Console\Helper\QuestionHelper;

#[AsCommand(name: 'app:create-admin')]
class CreateAdminCommand extends Command
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher, private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Creates a new admin user.')
            ->setHelp('This command allows you to create a user with ROLE_ADMIN...');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = new QuestionHelper();
// Demander l'email
        $questionEmail = new Question('Please enter the email: ');
        $email = $helper->ask($input, $output, $questionEmail);
// Demander le mot de passe avec vérification
        $password = null;
        while (!$password) {
            $questionPassword = new Question('Please enter the password: ');
            $questionPassword->setHidden(true);
            $questionPassword->setHiddenFallback(false);
            $password = $helper->ask($input, $output, $questionPassword);
            $questionPasswordRepeat = new Question('Please repeat the password: ');
            $questionPasswordRepeat->setHidden(true);
            $questionPasswordRepeat->setHiddenFallback(false);
            $passwordRepeat = $helper->ask($input, $output, $questionPasswordRepeat);
            if ($passwordRepeat !== $password) {
                $io->error('Passwords do not match!');
                $password = null;
            }
        }

        // Confirmation
        $confirm = new ConfirmationQuestion('Do you want to create this admin? [Y/n]', true);
        if (!$helper->ask($input, $output, $confirm)) {
            $io->warning('User creation aborted!');
            return Command::SUCCESS;
        }

        // Créer l'utilisateur
        $user = new User();
        $user
            ->setEmail($email)
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($this->passwordHasher->hashPassword($user, $password));
// Créer un passeport
        $passport = new Passport();
        $passport->setNumberPassport('DefaultNumber');
// Remplacez par un numéro approprié
        $passport->setDateExpiration(new \DateTime('2030-01-01'));
// Exemple de date d'expiration
        $passport->setNationalite('Default Nationality');
// Remplacez par une nationalité appropriée
        $passport->setProfession('Default Profession');
// Remplacez par une profession appropriée

        // Créer un profil avec les champs requis et associer le passeport
        $profile = new Profile();
        $profile->setName('Default Name');
// Remplacez par une valeur appropriée
        $profile->setFirstname('Default Firstname');
// Remplacez par une valeur appropriée
        $profile->setPhone('0000000000');
// Exemple de numéro de téléphone par défaut
        $profile->setAddress('Default Address');
// Exemple d'adresse par défaut
        $profile->setDateOfBirth(new \DateTime('2000-01-01'));
// Exemple de date de naissance par défaut
        $profile->setPassport($passport);
// Associez le passeport au profil

        // Associer le profil à l'utilisateur
        $user->setProfile($profile);
// Persister le passeport, le profil, et l'utilisateur
        $this->entityManager->persist($passport);
        $this->entityManager->persist($profile);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $io->success('Admin user created successfully!');
        return Command::SUCCESS;
    }
}
