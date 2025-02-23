<?php

namespace App\Controller;

use App\Entity\Documents;
use App\Form\DocumentsType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/documents')]
class DocumentsController extends AbstractController
{
    #[Route('/', name: 'app_documents_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Tri les documents par `download_date` décroissante (les plus récents en haut)
        if ($this->isGranted('ROLE_STUDENT')) {
            $documents = $entityManager->getRepository(Documents::class)->createQueryBuilder('d')
                ->where('d.user = :user OR d.selectedUser = :user')
                ->setParameter('user', $user)
                ->orderBy('d.download_date', 'DESC') // Tri par `download_date`
                ->getQuery()
                ->getResult();
        } else {
            // Récupère tous les documents et les tri par `download_date` décroissante
            $documents = $entityManager->getRepository(Documents::class)->findBy([], ['download_date' => 'DESC']);
        }

        return $this->render('documents/index.html.twig', [
            'documents' => $documents,
        ]);
    }

    #[Route('/new', name: 'app_documents_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $document = new Documents();
        $user = $this->getUser();

        // Si l'utilisateur est admin ou coach, récupérer tous les utilisateurs
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_COACH')) {
            $users = $userRepository->findAll();
        } else {
            // Si étudiant, récupérer uniquement les utilisateurs ayant le rôle ROLE_ADMIN ou ROLE_COACH
            $users = $userRepository->createQueryBuilder('u')
                ->where('u.roles LIKE :adminRole OR u.roles LIKE :coachRole')
                ->setParameter('adminRole', '%"ROLE_ADMIN"%')
                ->setParameter('coachRole', '%"ROLE_COACH"%')
                ->getQuery()
                ->getResult();
        }

        // Créer le formulaire avec les utilisateurs récupérés
        $form = $this->createForm(DocumentsType::class, $document, ['users' => $users]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $document->setUser($user);
            $document->setCoach($form->get('coach')->getData());
            $document->setDownloadDate(new \DateTime()); // Définit la date actuelle pour `download_date`

            $entityManager->persist($document);
            $entityManager->flush();

            return $this->redirectToRoute('app_documents_index');
        }

        return $this->render('documents/new.html.twig', [
            'document' => $document,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_documents_show', methods: ['GET'])]
    public function show(Documents $document): Response
    {
        if ($this->isGranted('ROLE_STUDENT') && $document->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('app_documents_index');
        }

        return $this->render('documents/show.html.twig', [
            'document' => $document,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_documents_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Documents $document, EntityManagerInterface $entityManager): Response
    {
        if ($this->isGranted('ROLE_STUDENT')) {
            return $this->redirectToRoute('app_documents_index');
        }

        $form = $this->createForm(DocumentsType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_documents_index');
        }

        return $this->render('documents/edit.html.twig', [
            'document' => $document,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_documents_delete', methods: ['POST'])]
    public function delete(Request $request, Documents $document = null, EntityManagerInterface $entityManager): Response
    {
        // Vérifie si le document existe
        if (!$document) {
            $this->addFlash('error', 'Document introuvable.');
            return $this->redirectToRoute('app_documents_index');
        }

        // Vérifie que l'utilisateur n'est pas un étudiant
        if ($this->isGranted('ROLE_STUDENT')) {
            return $this->redirectToRoute('app_documents_index');
        }

        // Vérifie le token CSRF
        if ($this->isCsrfTokenValid('delete' . $document->getId(), $request->request->get('_token'))) {
            $entityManager->remove($document);
            $entityManager->flush();

            $this->addFlash('success', 'Document supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Échec de la validation du token CSRF.');
        }

        return $this->redirectToRoute('app_documents_index');
    }
}
