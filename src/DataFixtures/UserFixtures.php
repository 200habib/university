<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Teams;
use App\Entity\Documents;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\File;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $teamsRepo = $manager->getRepository(Teams::class);
        $teams = $teamsRepo->findAll();

        if (empty($teams)) {
            throw new \RuntimeException('No teams found. Please load teams fixtures first.');
        }

        // Garder une trace des e-mails générés pour assurer l'unicité
        $usedEmails = [];

        for ($i = 1; $i <= 10; $i++) {
            $user = new User();

            // Associer un profil à l'utilisateur
            $profile = $this->getReference('profile_' . $i);
            if (!$profile) {
                throw new \RuntimeException("Référence de profil introuvable pour l'utilisateur avec l'index " . $i);
            }

            $user->setProfile($profile);

            // Crée un email basé sur le nom et prénom du profil
            $firstName = strtolower(str_replace(' ', '', $profile->getFirstname()));
            $lastName = strtolower(str_replace(' ', '', $profile->getName()));
            $email = $firstName . '.' . $lastName . '@gmail.com';

            // Vérifier l'unicité de l'email et ajuster si nécessaire
            $emailCount = 1;
            while (in_array($email, $usedEmails)) {
                $email = $firstName . '.' . $lastName . $emailCount . '@gmail.com';
                $emailCount++;
            }
            $usedEmails[] = $email; // Ajouter l'email à la liste des e-mails utilisés
            $user->setEmail($email);

            // Créez un mot de passe et affichez-le pour les administrateurs
            $plainPassword = 'password' . $i;
            $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            // Définir les rôles des utilisateurs
            if ($i <= 2) {
                // Les deux premiers utilisateurs sont des administrateurs
                $user->setRoles(['ROLE_ADMIN']);
                echo "Admin Email: " . $user->getEmail() . " | Mot de passe: " . $plainPassword . "\n";
            }else if ($i >2 && $i <= 4) {
                // Les deux premiers utilisateurs sont des administrateurs
                $user->setRoles(['ROLE_COACH']);
                echo "Coach Email: " . $user->getEmail() . " | Mot de passe: " . $plainPassword . "\n";
            } else {
                // Les autres utilisateurs sont des étudiants
                $user->setRoles(['ROLE_STUDENT']);
                echo "User Email: " . $user->getEmail() . " | Mot de passe: " . $plainPassword . "\n";
            }

            // Associer une équipe aléatoire
            $user->setTeams($teams[array_rand($teams)]);

            // Créez et associez des documents à l'utilisateur
            for ($j = 1; $j <= 3; $j++) {
                $document = new Documents();
                $document->setFileName('Document ' . $j . ' for user ' . $i);

                // Crée un fichier temporaire pour simuler un fichier téléchargé
                $tempFile = tempnam(sys_get_temp_dir(), 'upload_');
                file_put_contents($tempFile, 'This is the content of the file ' . $j);

                $file = new File($tempFile); // Crée un objet File
                $document->setFilePath($file); // Associe l'objet File au document
                $document->setUser($user); // Utilise setUser au lieu de addUser
                $manager->persist($document);
            }

            $manager->persist($user);
        }

        $manager->flush();

        // Nettoyage des fichiers temporaires
        foreach (glob(sys_get_temp_dir() . '/upload_*') as $file) {
            unlink($file);
        }
    }
}
