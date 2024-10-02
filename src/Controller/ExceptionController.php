<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExceptionController extends AbstractController
{

    #[Route('/access-refuse', name: 'acces_refuse')]
    public function accessRefuse(): Response
    {
        return $this->render('security/acces_refuse.html.twig');
    }
}
