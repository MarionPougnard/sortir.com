<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AccueilController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/', name: 'app_accueil')]
    public function index(): Response
    {
        $utilisateur  = $this->getUser();
        return $this->render('accueil/accueil.html.twig', [
            'utilisateur' => $utilisateur
        ]);
    }
}
