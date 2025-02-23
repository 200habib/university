<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Form\Appointment1Type;
use App\Repository\AppointmentRepository;
use App\Repository\UserRepository;
use App\Repository\TeamsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/appointment')]
    // #[IsGranted('ROLE_ADMIN')]
class AppointmentController extends AbstractController
{
    #[Route('/', name: 'app_appointment_index', methods: ['GET'])]
    public function index(AppointmentRepository $appointmentRepository): Response
    {
        // Récupérer l'utilisateur connecté
        $currentUser = $this->getUser();

        // Vérifier si l'utilisateur est bien connecté
        if (!$currentUser) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        // Si l'utilisateur est admin, on récupère tous les rendez-vous
        if ($this->isGranted('ROLE_ADMIN')) {
            $appointments = $appointmentRepository->findAll();
        } elseif ($this->isGranted('ROLE_COACH')) {
            // Récupérer les rendez-vous où le coach est l'utilisateur connecté
            $appointments = $appointmentRepository->createQueryBuilder('a')
                ->where('a.coach = :coach')
                ->setParameter('coach', $currentUser)
                ->orderBy('a.appointment_date', 'ASC')
                ->getQuery()
                ->getResult();
        } else {
            // Récupérer uniquement les rendez-vous de l'utilisateur connecté
            $appointments = $appointmentRepository->createQueryBuilder('a')
                ->where('a.user = :user')
                ->setParameter('user', $currentUser)
                ->orderBy('a.appointment_date', 'ASC')
                ->getQuery()
                ->getResult();
        }

        return $this->render('appointment/index.html.twig', [
            'appointments' => $appointments,
        ]);
    }



    #[Route('/new', name: 'app_appointment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $appointment = new Appointment();
        $form = $this->createForm(Appointment1Type::class, $appointment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($appointment);
            $entityManager->flush();

            return $this->redirectToRoute('app_appointment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('appointment/new.html.twig', [
            'appointment' => $appointment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_appointment_show', methods: ['GET'])]
    public function show(Appointment $appointment): Response
    {
        return $this->render('appointment/show.html.twig', [
            'appointment' => $appointment,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_appointment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Appointment $appointment, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Appointment1Type::class, $appointment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_appointment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('appointment/edit.html.twig', [
            'appointment' => $appointment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_appointment_delete', methods: ['POST'])]
    public function delete(Request $request, Appointment $appointment, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $appointment->getId(), $request->get('_token'))) {
            $entityManager->remove($appointment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_appointment_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/user/{id}/appointments', name: 'app_appointment_user', methods: ['GET'])]
    public function getUserAppointments($id, AppointmentRepository $appointmentRepository, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        $appointments = $appointmentRepository->createQueryBuilder('a')
            ->where('a.user = :user')
            ->setParameter('user', $user)
            ->orderBy('a.appointment_date', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('appointment/seeall.html.twig', [
            'appointments' => $appointments,
            'user' => $user,
        ]);
    }
}
