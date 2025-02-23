<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CoachepageController extends AbstractController
{
    #[Route('/coachepage', name: 'app_coachepage')]
    #[IsGranted('ROLE_COACH')]
    public function index(UserRepository $userRepository): Response
    {
        // Obtenir l'utilisateur connecté
        $currentUser = $this->getUser();

        if (!$currentUser instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        // Vérifiez que l'utilisateur a bien une équipe
        $team = $currentUser->getTeams();

        if (!$team) {
            throw $this->createAccessDeniedException('Vous n\'avez pas d\'équipe assignée.');
        }

        // Trouver les utilisateurs appartenant à la même équipe
        $users = $userRepository->findBy(['teams' => $team]);

        // Initialiser le compteur d'utilisateurs
        $totalUser = 0;

        // Compter les utilisateurs par rôle
        foreach ($users as $user) {
            if (in_array('ROLE_USER', $user->getRoles())) {
                $totalUser++;
            }
        }

        return $this->render('coachepage/index.html.twig', [
            'currentUser' => $currentUser,
            'users' => $users,
            'totalUser' => $totalUser,
        ]);
    }

    #[Route('/coachepage/profile/{id}', name: 'app_coach_profile')]
    public function show(User $user): Response
    {
        return $this->render('coachepage/profile.html.twig', [
            'user' => $user,
        ]);
    }
}
