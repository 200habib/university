<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        // Création d'un nouvel objet Contact
        $contact = new Contact();

        // Création du formulaire de contact
        $form = $this->createForm(ContactType::class, $contact);

        // Traitement de la requête HTTP pour récupérer les données soumises
        $form->handleRequest($request);

        // Vérification si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarde du contact dans la base de données
            $entityManager->persist($contact);
            $entityManager->flush();

            // Création de l'email de notification
            $email = (new Email())
                ->from('mabialaelie4@gmail.com') // L'adresse email de l'expéditeur
                ->to('endiepro4@gmail.com') // L'adresse email du destinataire
                ->subject('Nouveau utilisateur ajouté')
                ->html($this->renderView('emails/notification_contact.html.twig', [
                    'contact' => $contact
                ])); // Envoi des données du contact au template

            // Envoi de l'email via le service de mailer
            $mailer->send($email);

            // Ajout d'un message flash de succès après soumission
            $this->addFlash('success', 'Votre message a bien été envoyé. ');

            // Redirection vers la même page pour éviter la soumission multiple du formulaire
            return $this->redirectToRoute('app_contact');
        }

        // Rendu du formulaire de contact dans la vue
        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/conditions', name: 'terms_conditions')]
    public function termsConditions(): Response
    {
        return $this->render('legal/terms_conditions.html.twig');
    }

    #[Route('/politique-de-confidentialite', name: 'privacy_policy')]
    public function privacyPolicy(): Response
    {
        return $this->render('legal/privacy_policy.html.twig');
    }

    #[Route('/mentions-legales', name: 'legal_mentions')]
    public function legalMentions(): Response
    {
        return $this->render('legal/legal_mentions.html.twig');
    }
}
