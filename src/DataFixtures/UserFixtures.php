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
    
        $usedEmails = [];
        $coaches = []; // Stocker les coachs
    
        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
    
            // Associer un profil
            $profile = $this->getReference('profile_' . $i);
            if (!$profile) {
                throw new \RuntimeException("Référence de profil introuvable pour l'utilisateur avec l'index " . $i);
            }
    
            $user->setProfile($profile);
    
            // Générer un email unique
            $firstName = strtolower(str_replace(' ', '', $profile->getFirstname()));
            $lastName = strtolower(str_replace(' ', '', $profile->getName()));
            $email = $firstName . '.' . $lastName . '@gmail.com';
    
            $emailCount = 1;
            while (in_array($email, $usedEmails)) {
                $email = $firstName . '.' . $lastName . $emailCount . '@gmail.com';
                $emailCount++;
            }
            $usedEmails[] = $email;
            $user->setEmail($email);
    
            // Mot de passe
            $plainPassword = 'password' . $i;
            $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
    
            // Définir les rôles et stocker les coachs
            if ($i <= 2) {
                $user->setRoles(['ROLE_ADMIN']);
                echo "Admin Email: " . $user->getEmail() . " | Mot de passe: " . $plainPassword . "\n";
            } elseif ($i > 2 && $i <= 4) {
                $user->setRoles(['ROLE_COACH']);
                $coaches[] = $user; // Stocke les coachs ici
                echo "Coach Email: " . $user->getEmail() . " | Mot de passe: " . $plainPassword . "\n";
            } else {
                $user->setRoles(['ROLE_STUDENT']);
                echo "User Email: " . $user->getEmail() . " | Mot de passe: " . $plainPassword . "\n";
            }
    
            // Associer une équipe aléatoire
            $user->setTeams($teams[array_rand($teams)]);
    
            $manager->persist($user);
        }
    
        $manager->flush(); //  On flush AVANT de créer les documents pour avoir les coachs en base
    
        // Récupérer tous les étudiants après flush
        $students = $manager->getRepository(User::class)->findByRole('ROLE_STUDENT');
    
        foreach ($students as $student) {
            for ($j = 1; $j <= 3; $j++) {
                $document = new Documents();
                $document->setFileName('Document ' . $j);
    
                // Créer un fichier temporaire
                $tempFile = tempnam(sys_get_temp_dir(), 'upload_');
                file_put_contents($tempFile, 'This is the content of the file ' . $j);
                $file = new File($tempFile);
                $document->setFilePath($file);
                $document->setUser($student);
    
                // Assigner un coach aléatoire si possible
                if (!empty($coaches)) {
                    $randomCoach = $coaches[array_rand($coaches)];
                    $document->setCoach($randomCoach);
                }
    
                $manager->persist($document);
            }
        }
    
        $manager->flush(); //  Flush final pour les documents
    
        // Nettoyage des fichiers temporaires
        foreach (glob(sys_get_temp_dir() . '/upload_*') as $file) {
            unlink($file);
        }
    }
    
}
