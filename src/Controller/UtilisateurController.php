<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UtilisateurController extends AbstractController
{
    // Route page profil utilisateur
    #[Route('/utilisateur', name: 'utilisateur')]
    public function index(): Response
    {
        $utilisateur  = $this->getUser();
        return $this->render('utilisateur/_profil.html.twig', [
            'utilisateur' => $utilisateur
               ]);
    }

    // Route page modification profil utilisateur
    #[Route('/utilisateur/modification', name: 'utilisateur_modification')]
    public function modificationProfil(): Response
    {
        return $this->render('utilisateur/_modification.html.twig', [

        ]);
    }
}
