<?php

namespace App\DTO;

use App\Entity\Campus;

class RechercheUtilisateur
{
    public ?string $search;
    public ?Campus $campus;
    public ?bool $estActif = true;

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): self
    {
        $this->campus = $campus;
        return $this;
    }
}