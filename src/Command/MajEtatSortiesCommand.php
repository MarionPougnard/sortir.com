<?php

namespace App\Command;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:maj-etat-sortie',
    description: 'Add a short description for your command',
)]
class MajEtatSortiesCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private SortieRepository $sortieRepository;
    private EtatRepository $etatRepository;

    public function __construct(EntityManagerInterface $entityManager, SortieRepository $sortieRepository, EtatRepository $etatRepository)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->sortieRepository = $sortieRepository;
        $this->etatRepository = $etatRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $sorties = $this->sortieRepository->chercheSortiesNonHistorisees();


        foreach ($sorties as $sortie) {
            $sortie->verifierEtat($this->etatRepository);
        }

        $this->entityManager->flush();


        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}