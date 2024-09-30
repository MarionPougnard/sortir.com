<?php

namespace App\Entity;

use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\DocBlock\Tags\Property;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SortieRepository::class)]
class Sortie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'le nom est requis')]
    #[Assert\Length(min: 2, max: 50)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: "La date et l'heure sont requises")]
    #[Assert\GreaterThan("today", message: "La sortie est un évènement à venir")]
    private ?\DateTimeInterface $dateHeureDebut = null;

    #[ORM\Column(length: 7)]
    #[Assert\NotBlank(message: "La durée est requise")]
    #[Assert\Positive(message: 'La durée doit être positive')]
    private ?int $duree = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: "La date limite d'inscription est requise")]
    #[Assert\GreaterThan("today", message: "La sortie est un évènement à venir")]
    #[Assert\LessThan(propertyPath: 'dateHeureDebut', message: "La date limite d'inscription doit être antérieure à la date de la sortie")]
    private ?\DateTimeInterface $dateLimiteInscription = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Le nombre maximum d'inscrits est obligatoire")]
    #[Assert\Type(type: "integer", message: "Un nombre entier est attendu")]
    #[Assert\Positive(message: 'Un nombre positif est attendu')]
    private ?int $nbInscriptionMax = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $infosSortie = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etat $etat = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;

    #[ORM\ManyToOne(inversedBy: 'sortiesOrganisees')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $organisateur = null;

    /**
     * @var Collection<int, Utilisateur>
     */
    #[ORM\ManyToMany(targetEntity: Utilisateur::class, mappedBy: 'sortiesParticipees')]
    private Collection $participants;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Lieu $lieu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $motifAnnulation = null;


    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDateHeureDebut(): ?\DateTimeInterface
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut(\DateTimeInterface $dateHeureDebut): static
    {
        $this->dateHeureDebut = $dateHeureDebut;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDateLimiteInscription(): ?\DateTimeInterface
    {
        return $this->dateLimiteInscription;
    }

    public function setDateLimiteInscription(\DateTimeInterface $dateLimiteInscription): static
    {
        $this->dateLimiteInscription = $dateLimiteInscription;

        return $this;
    }

    public function getNbInscriptionMax(): ?int
    {
        return $this->nbInscriptionMax;
    }

    public function setNbInscriptionMax(int $nbInscriptionMax): static
    {
        $this->nbInscriptionMax = $nbInscriptionMax;

        return $this;
    }

    public function getInfosSortie(): ?string
    {
        return $this->infosSortie;
    }

    public function setInfosSortie(?string $infosSortie): static
    {
        $this->infosSortie = $infosSortie;

        return $this;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): static
    {
        $this->campus = $campus;

        return $this;
    }

    public function getOrganisateur(): ?Utilisateur
    {
        return $this->organisateur;
    }

    public function setOrganisateur(?Utilisateur $organisateur): static
    {
        $this->organisateur = $organisateur;


        return $this;
    }

    /**
     * @return Collection<int, Utilisateur>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Utilisateur $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
            $participant->addSortiesParticipee($this);
        }

        return $this;
    }

    public function removeParticipant(Utilisateur $participant): static
    {
        if ($this->participants->removeElement($participant)) {
            $participant->removeSortiesParticipee($this);
        }

        return $this;
    }

    public function getLieu(): ?Lieu
    {
        return $this->lieu;
    }

    public function setLieu(?Lieu $lieu): static
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function getMotifAnnulation(): ?string
    {
        return $this->motifAnnulation;
    }

    public function setMotifAnnulation(?string $motifAnnulation): static
    {
        $this->motifAnnulation = $motifAnnulation;

        return $this;
    }

    public function verifierEtat(EtatRepository $etatRepository): void {
        $now = new \DateTime();
        $dateFin = (clone $this->dateHeureDebut)->modify("+{$this->duree} minutes");
        $dateHistorisation = (clone $dateFin)->modify('+1 month');

        if ($this->etat && $this->etat->getLibelle() == 'Ouverte') {
            $nbParticipants = count($this->participants);

            $isMaxParticipantsAtteint = $nbParticipants >= $this->nbInscriptionMax;
            $isDateLimiteDepassee = $now >= $this->dateLimiteInscription;

            if ($isMaxParticipantsAtteint || $isDateLimiteDepassee) {
                $etatCloture = $etatRepository->findOneBy(['libelle' => 'Clôturée']);

                if ($etatCloture) {
                    $this->setEtat($etatCloture);
                }
            }
        }

        if ($this->etat && $this->etat->getLibelle() == 'Clôturée' && $now >= $this->dateHeureDebut) {
            $etatEnCours = $etatRepository->findOneBy(['libelle' => 'En cours']);
            $this->setEtat($etatEnCours);
        }

        if ($this->etat && $this->etat->getLibelle() == 'En cours' && $now >= $dateFin) {
            $etatTerminee = $etatRepository->findOneBy(['libelle' => 'Terminée']);
            $this->setEtat($etatTerminee);
        }

        if ($this->etat && $this->etat->getLibelle() == 'Terminée' && $now > $dateHistorisation) {
            $etatHistorisee = $etatRepository->findOneBy(['libelle' => 'Historisée']);
            $this->setEtat($etatHistorisee);
        }
    }

}

