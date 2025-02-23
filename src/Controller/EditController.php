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
use Symfony\Component\Form\FormError;

class EditController extends AbstractController
{
    #[Route('/edit/{id}', name: 'app_edit')]
    // #[IsGranted('ROLE_ADMIN')]
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        UserPasswordHasherInterface $userPasswordHasherInterface,
        int $id
    ): Response {
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Aucun utilisateur trouvé avec l\'ID ' . $id);
        }

        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si un autre utilisateur utilise le même email
            $existingEmailUser = $entityManager->getRepository(User::class)
                ->findOneBy(['email' => $user->getEmail()]);

            if ($existingEmailUser && $existingEmailUser->getId() !== $user->getId()) {
                $form->get('email')->addError(
                    new FormError('Cet email est déjà utilisé.')
                );
                return $this->render('edit/index.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            // Gérer les rôles
            $roles = $form->get('roles')->getData();
            $user->setRoles(is_array($roles) ? $roles : [$roles]);

            // Gérer le mot de passe uniquement si une nouvelle valeur est fournie
            $newPassword = $form->get('password')->getData();
            if (!empty($newPassword)) {
                $hashedPassword = $userPasswordHasherInterface->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);

                // Envoi d'un email si le mot de passe est modifié
                $email = (new Email())
                    ->from('mabialaelie4@gmail.com') // Expéditeur
                    ->to($user->getEmail()) // Destinataire
                    ->subject('Votre mot de passe a été réinitialisé')
                    ->html(
                        $this->renderView('emails/password_reset_notification.html.twig', [
                            'email' => $user->getEmail(),
                            'password' => $newPassword, // Mot de passe non haché
                        ])
                    );
                $mailer->send($email);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'L\'utilisateur a été modifié avec succès.');

            return $this->redirectToRoute('app_user_profile', ['id' => $user->getId()]);
        }

        return $this->render('edit/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
