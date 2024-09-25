<?php

namespace App\Entity;

use App\Enum\EtatEnum;
use App\Repository\EtatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EtatRepository::class)]
class Etat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(type: 'string')]
    #[Assert\Choice(callback: [EtatEnum::class, 'values'])]
    private EtatEnum $libelle;

    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\OneToMany(targetEntity: Sortie::class, mappedBy: 'Etat')]
    private Collection $sorties;

    public function __construct()
    {
        $this->sorties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): EtatEnum
    {
        return $this->libelle;
    }

    public function setLibelle(EtatEnum $libelle): void
    {
        if (!EtatEnum::tryFrom($libelle)) {
            throw new \InvalidArgumentException("Le libellé n'est pas valide");
        }
        $this->libelle = $libelle;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSorties(): Collection
    {
        return $this->sorties;
    }

    public function addSorty(Sortie $sorty): static
    {
        if (!$this->sorties->contains($sorty)) {
            $this->sorties->add($sorty);
            $sorty->setEtat($this);
        }

        return $this;
    }

    public function removeSorty(Sortie $sorty): static
    {
        if ($this->sorties->removeElement($sorty)) {
            // set the owning side to null (unless already changed)
            if ($sorty->getEtat() === $this) {
                $sorty->setEtat(null);
            }
        }

        return $this;
    }
}
