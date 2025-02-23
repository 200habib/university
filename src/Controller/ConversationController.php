<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Messages;
use App\Entity\User;
use App\Form\MessageType;
use App\Repository\ConversationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ConversationController extends AbstractController
{
    #[Route('/conversation/new/{id}', name: 'app_conversation_create')]
    #[IsGranted('ROLE_USER')]
    public function createConversation(
        User $recipient,
        EntityManagerInterface $entityManager,
        ConversationRepository $conversationRepository
    ): Response {
        /** @var User $sender */
        $sender = $this->getUser();

        // Vérification pour s'assurer que l'utilisateur ne peut pas se contacter lui-même
        if ($sender === $recipient) {
            $this->addFlash('error', 'Vous ne pouvez pas démarrer une conversation avec vous-même.');
            return $this->redirectToRoute('app_conversation_list');
        }

        $existingConversation = $conversationRepository->findConversationBetweenUsers($recipient, $sender);

        if ($existingConversation) {
            return $this->redirectToRoute('app_conversation_show', ['id' => $existingConversation->getId()]);
        }

        $conversation = new Conversation();
        $conversation->addParticipant($sender);
        $conversation->addParticipant($recipient);

        $entityManager->persist($conversation);
        $entityManager->flush();

        return $this->redirectToRoute('app_conversation_show', ['id' => $conversation->getId()]);
    }

    #[Route('/conversation/{id}', name: 'app_conversation_show', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function showConversation(
        Request $request,
        int $id,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();

        // Récupérer la conversation en utilisant l'ID fourni
        $conversation = $entityManager->getRepository(Conversation::class)->find($id);
        if (!$conversation) {
            throw $this->createNotFoundException("La conversation demandée n'existe pas.");
        }

        $messages = $conversation->getMessages();
        foreach ($messages as $message) {
            if ($message->getAuthor() !== $this->getUser()) {
                $message->setRead(true);
            }
        }
        $entityManager->flush();

        // Vérifier que la conversation contient exactement deux participants
        $participants = $conversation->getParticipants();
        if (count($participants) !== 2 || !$participants->contains($user)) {
            throw new AccessDeniedException("Vous n'êtes pas autorisé à voir cette conversation.");
        }

        $message = new Messages();
        $form = $this->createForm(MessageType::class, $message);

        // Gestion de la soumission du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $message
                ->setAuthor($user)
                ->setCreatedAt(new \DateTimeImmutable())
                ->setConversation($conversation);

            $entityManager->persist($message);
            $entityManager->flush();

            return $this->redirectToRoute('app_conversation_show', ['id' => $conversation->getId()]);
        }

        return $this->render('Conversation/show.html.twig', [
            'conversation' => $conversation,
            'messages' => $conversation->getMessages(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/conversation/list', name: 'app_conversation_list')]
    #[IsGranted('ROLE_USER')]
    public function listConversations(EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur connecté
        $currentUser = $this->getUser();

        // Construire la requête de base pour récupérer les utilisateurs
        $qb = $entityManager->getRepository(User::class)->createQueryBuilder('u');

        // Vérifier le rôle de l'utilisateur connecté
        if ($this->isGranted('ROLE_STUDENT')) {
            // Si l'utilisateur est un étudiant, on filtre pour montrer uniquement les coachs
            $qb->where('u.roles LIKE :role_coach')
                ->setParameter('role_coach', '%"ROLE_COACH"%');
        }

        // Exclure l'utilisateur connecté de la liste des utilisateurs
        $qb->andWhere('u != :currentUser')
            ->setParameter('currentUser', $currentUser);

        $users = $qb->getQuery()->getResult();

        return $this->render('Conversation/list.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/conversation/existing', name: 'app_existing_conversations')]
    #[IsGranted('ROLE_USER')]
    public function listExistingConversations(
        EntityManagerInterface $entityManager,
        ConversationRepository $conversationRepository
    ): Response {
        // Récupérer l'utilisateur connecté
        $currentUser = $this->getUser();
        // dump($currentUser);
        // die();

        // Récupérer les conversations auxquelles l'utilisateur participe
        $conversations = $conversationRepository->createQueryBuilder('c')
            ->join('c.participants', 'p')
            ->where('p = :currentUser')
            ->setParameter('currentUser', $currentUser)
            ->getQuery()
            ->getResult();

        return $this->render('Conversation/existing.html.twig', [
            'conversations' => $conversations,
        ]);
    }
}
