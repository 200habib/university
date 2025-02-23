<?php

namespace App\DataFixtures;

use App\Entity\Contact;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ContactFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 5; $i++) {
            $contact = new Contact();
            $contact->setName($faker->name());
            $contact->setEmail($faker->email());
            $contact->setSubject('Demande n°' . ($i + 1));
            $contact->setMessage($faker->text());
            $contact->setCreatedAt(new \DateTimeImmutable());

            // Définir termsAccepted à true pour éviter les erreurs de contrainte de base de données
            $contact->setTermsAccepted(true);

            $manager->persist($contact);
        }

        $manager->flush();
    }
}
