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
       // $user = $this->getUser();
//        if (!$user) {
//            return $this->redirectToRoute('app_login');
//        }
        return $this->render('accueil/accueil.html.twig', [
        ]);
    }
}
