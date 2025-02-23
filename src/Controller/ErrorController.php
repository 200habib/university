<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ErrorController extends AbstractController
{
    #[Route('/404', name: 'app_404')]
    public function showNotFound(): Response
    {
        // Rend le template 404.html.twig avec un code de réponse 404
        return $this->render('404.html.twig', [], new Response('', 404));
    }
}
