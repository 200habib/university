<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\User;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;

class NewController extends AbstractController
{
    #[Route('/new', name: 'app_new')]
    // #[IsGranted('ROLE_ADMIN')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        UserPasswordHasherInterface $userPasswordHasherInterface
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si l'email existe déjà dans la base de données
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);

            if ($existingUser) {
                $this->addFlash('error', 'Cet email est déjà utilisé.');

                return $this->render('new/index.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            // Récupérer le mot de passe en clair
            $plainPassword = $form->get('password')->getData();

            // Hacher le mot de passe avant de l'enregistrer
            $hashedPassword = $userPasswordHasherInterface->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            // Attribuer les rôles
            $roles = $form->get('roles')->getData();
            $user->setRoles($roles);

            // Enregistrer l'utilisateur dans la base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // Envoi de l'email de bienvenue avec le mot de passe non haché
            $email = (new Email())
                ->from('mabialaelie4@gmail.com') // Adresse expéditeur
                ->to($user->getEmail()) // Adresse destinataire
                ->subject('Bienvenue dans notre plateforme')
                ->html(
                    $this->renderView('emails/welcome.html.twig', [
                        'email' => $user->getEmail(),
                        'password' => $plainPassword, // Mot de passe non haché
                    ])
                );

            $mailer->send($email);

            // Redirection après ajout réussi
            $this->addFlash('success', 'L\'utilisateur a été ajouté avec succès.');
            return $this->redirectToRoute('app_adminpage');
        }

        return $this->render('new/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
