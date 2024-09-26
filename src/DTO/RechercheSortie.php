<?php

namespace App\DTO;

use App\Entity\Campus;

class RechercheSortie
{
    public ?string $search;
    public ?Campus $campus;
    public ?\DateTimeInterface $dateDebut = null;
    public ?\DateTimeInterface $dateFin = null;
    public ?bool $estOrganisateur = true;
    public ?bool $estInscrit = true;
    public ?bool $estPasInscrit = true;
    public ?bool $estTerminees = false;
}