<?php

namespace App\Controller;

use App\DTO\RechercheSortie;
use App\Form\RechercheSortieFormType;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AccueilController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/', name: 'app_accueil')]
    public function index(
        Request $request,
        SortieRepository $sortieRepository,
        EtatRepository $etatRepository,
        EntityManagerInterface $entityManager,
    )
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $filtreRecherche = new RechercheSortie();
        $formRecherche = $this->createForm(RechercheSortieFormType::class, $filtreRecherche);
        $formRecherche->handleRequest($request);
        $sorties = $sortieRepository->chercheSortiesNonHistorisees();

        if ($formRecherche->isSubmitted() && $formRecherche->isValid()) {
            $filtreRecherche = $formRecherche->getData();
            $sorties = $sortieRepository->chercheSortieAvecFiltre($filtreRecherche, $user);
        }

        $entityManager->flush();

        return $this->render('accueil/accueil.html.twig', [
            'title' => 'Liste des sorties',
            'sorties' => $sorties,
            'utilisateur' => $user,
        ]);
    }
}
