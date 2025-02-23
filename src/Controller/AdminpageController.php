<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Step;
use App\Entity\Appointment;
use App\Form\AppointmentType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminpageController extends AbstractController
{
    #[Route('/adminpage', name: 'app_adminpage')]
    // #[IsGranted('ROLE_ADMIN')]
    public function index(UserRepository $userRepository): Response
    {
        // Récupérer les utilisateurs dans l'ordre décroissant de la date de création
        $users = $userRepository->findBy([], ['createdAt' => 'DESC']);

        // Initialiser les compteurs pour chaque rôle
        $totalAdmin = 0;
        $totalStudent = 0; // Variable pour les étudiants
        $totalCoach = 0;

        // Compter les utilisateurs par rôle
        foreach ($users as $user) {
            if (in_array('ROLE_ADMIN', $user->getRoles())) {
                $totalAdmin++;
            }
            if (in_array('ROLE_STUDENT', $user->getRoles())) {
                $totalStudent++;
            }

            if (in_array('ROLE_COACH', $user->getRoles())) {
                $totalCoach++;
            }
        }

        return $this->render('adminpage/index.html.twig', [
            'users' => $users,
            'totalAdmin' => $totalAdmin,
            'totalStudent' => $totalStudent,
            'totalCoach' => $totalCoach,
        ]);
    }

    #[Route('/adminpage/profile/{id}', name: 'app_user_profile')]
    public function show(User $user, EntityManagerInterface $entityManager): Response
    {
        // Récupérer les étapes associées à l'utilisateur
        $steps = $entityManager->getRepository(Step::class)->findBy(['user' => $user]);

        // Rendre la vue du profil de l'utilisateur avec ses étapes
        return $this->render('adminpage/profile.html.twig', [
            'user' => $user,
            'steps' => $steps,
        ]);
    }

    #[Route('/adminpage/delete/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Vérifier le token CSRF pour sécuriser la suppression
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            // Supprimer l'utilisateur de la base de données
            $entityManager->remove($user);
            $entityManager->flush();

            // Ajouter un message flash pour confirmer la suppression
            $this->addFlash('success', 'Utilisateur supprimé avec succès');
        }

        // Rediriger vers la page d'administration
        return $this->redirectToRoute('app_adminpage');
    }

    #[Route('/adminpage/appointment/new/{id}', name: 'app_appointment_new')]
    public function createAppointment(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Créer un nouveau rendez-vous et l'associer à l'utilisateur
        $appointment = new Appointment();
        $appointment->setUser($user);

        // Créer et gérer le formulaire de rendez-vous
        $form = $this->createForm(AppointmentType::class, $appointment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrer le rendez-vous dans la base de données
            $entityManager->persist($appointment);
            $entityManager->flush();

            // Ajouter un message flash pour confirmer la création du rendez-vous
            $this->addFlash('success', 'Rendez-vous créé avec succès.');


            return $this->redirectToRoute('app_user_profile', ['id' => $user->getId()]);
        }
        return $this->render('appointment/add_rdv.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
