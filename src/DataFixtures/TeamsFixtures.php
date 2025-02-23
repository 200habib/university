<?php

namespace App\DataFixtures;

use App\Entity\Teams;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TeamsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $teams = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

        foreach ($teams as $teamName) {
            $team = new Teams();
            $team->setTeam($teamName);
            $manager->persist($team);
        }

        $manager->flush();
    }
}
