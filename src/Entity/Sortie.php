<?php

namespace App\Entity;

use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SortieRepository::class)]
class Sortie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom est obligatoire")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le nom ne doit pas dépasser 255 caractères"
    )]
    private ?string $nom = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateHeureDebut = null;

    #[ORM\Column]
    private ?int $duree = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateLimiteInscription = null;

    #[ORM\Column]
    private ?int $nbInscriptionsMax = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $infosSortie = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    private ?Site $Site = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    private ?Etat $Etat = null;

    #[ORM\ManyToOne(inversedBy: 'SortiesParticipants')]
    private ?Participant $ParticipantOrganisateur = null;

    #[ORM\ManyToMany(targetEntity: Participant::class, inversedBy: 'ParticipantsInscrits')]
    private Collection $ParticipantInscrit;

    #[ORM\ManyToOne]
    private ?Lieu $Lieu = null;

    public function __construct()
    {
        $this->ParticipantInscrit = new ArrayCollection();
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

    public function getDateHeureDebut(): ?\DateTime
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut(?\DateTime $dateHeureDebut): static
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

    public function getDateLimiteInscription(): ?\DateTime
    {
        return $this->dateLimiteInscription;
    }

    public function setDateLimiteInscription(?\DateTime $dateLimiteInscription): static
    {
        $this->dateLimiteInscription = $dateLimiteInscription;
        return $this;
    }

    public function getNbInscriptionsMax(): ?int
    {
        return $this->nbInscriptionsMax;
    }

    public function setNbInscriptionsMax(int $nbInscriptionsMax): static
    {
        $this->nbInscriptionsMax = $nbInscriptionsMax;

        return $this;
    }

    public function getInfosSortie(): ?string
    {
        return $this->infosSortie;
    }

    public function setInfosSortie(string $infosSortie): static
    {
        $this->infosSortie = $infosSortie;

        return $this;
    }

    public function getEtatRelation(): ?Etat
    {
        return $this->Etat;
    }

    public function setEtatRelation(?Etat $etat): static
    {
        $this->Etat = $etat;
        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->Site;
    }

    public function setSite(?Site $Site): static
    {
        $this->Site = $Site;

        return $this;
    }

    public function getParticipantOrganisateur(): ?Participant
    {
        return $this->ParticipantOrganisateur;
    }

    public function setParticipantOrganisateur(?Participant $ParticipantOrganisateur): static
    {
        $this->ParticipantOrganisateur = $ParticipantOrganisateur;

        return $this;
    }

    public function getLieu(): ?Lieu
    {
        return $this->Lieu;
    }

    public function setLieu(?Lieu $lieu): static
    {
        $this->Lieu = $lieu;
        return $this;
    }

    /**
     * @return Collection<int, Participant>
     */
    public function getParticipantInscrit(): Collection
    {
        return $this->ParticipantInscrit;
    }

    public function addParticipantInscrit(Participant $participantInscrit): static
    {
        if (!$this->ParticipantInscrit->contains($participantInscrit)) {
            $this->ParticipantInscrit->add($participantInscrit);
        }

        return $this;
    }

    public function removeParticipantInscrit(Participant $participantInscrit): static
    {
        $this->ParticipantInscrit->removeElement($participantInscrit);

        return $this;
    }
}
