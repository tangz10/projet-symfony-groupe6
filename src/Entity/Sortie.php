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
    #[ORM\JoinColumn(name: 'site_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Site $site = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(name: 'etat_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Etat $etat = null;

    #[ORM\ManyToOne(inversedBy: 'sortiesParticipants')]
    #[ORM\JoinColumn(name: 'participant_organisateur_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Participant $participantOrganisateur = null;

    #[ORM\ManyToMany(targetEntity: Participant::class, inversedBy: 'participantsInscrits')]
    #[ORM\JoinTable(name: 'sortie_participant')]
    #[ORM\JoinColumn(name: 'sortie_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'participant_id', referencedColumnName: 'id')]
    private Collection $participantInscrit;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'lieu_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Lieu $lieu = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $motifAnnulation = null;

    /**
     * @var Collection<int, Note>
     */
    #[ORM\OneToMany(targetEntity: Note::class, mappedBy: 'sortie')]
    private Collection $participant;

    /**
     * @var Collection<int, Note>
     */
    #[ORM\OneToMany(targetEntity: Note::class, mappedBy: 'sortie')]
    private Collection $note;

    public function __construct()
    {
        $this->participantInscrit = new ArrayCollection();
        $this->participant = new ArrayCollection();
        $this->note = new ArrayCollection();
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

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): static
    {
        $this->etat = $etat;
        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): static
    {
        $this->site = $site;

        return $this;
    }

    public function getParticipantOrganisateur(): ?Participant
    {
        return $this->participantOrganisateur;
    }

    public function setParticipantOrganisateur(?Participant $participantOrganisateur): static
    {
        $this->participantOrganisateur = $participantOrganisateur;

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

    /**
     * @return Collection<int, Participant>
     */
    public function getParticipantInscrit(): Collection
    {
        return $this->participantInscrit;
    }

    public function addParticipantInscrit(Participant $participantInscrit): static
    {
        if (!$this->participantInscrit->contains($participantInscrit)) {
            $this->participantInscrit->add($participantInscrit);
        }

        return $this;
    }

    public function removeParticipantInscrit(Participant $participantInscrit): static
    {
        $this->participantInscrit->removeElement($participantInscrit);

        return $this;
    }

    public function getMotifAnnulation(): ?string
    {
        return $this->motifAnnulation;
    }

    public function setMotifAnnulation(?string $motifAnnulation): self
    {
        $this->motifAnnulation = $motifAnnulation;
        return $this;
    }

    /**
     * @return Collection<int, Note>
     */
    public function getParticipant(): Collection
    {
        return $this->participant;
    }

    public function addParticipant(Note $participant): static
    {
        if (!$this->participant->contains($participant)) {
            $this->participant->add($participant);
            $participant->setSortie($this);
        }

        return $this;
    }

    public function removeParticipant(Note $participant): static
    {
        if ($this->participant->removeElement($participant)) {
            // set the owning side to null (unless already changed)
            if ($participant->getSortie() === $this) {
                $participant->setSortie(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Note>
     */
    public function getNote(): Collection
    {
        return $this->note;
    }

    public function addNote(Note $note): static
    {
        if (!$this->note->contains($note)) {
            $this->note->add($note);
            $note->setSortie($this);
        }

        return $this;
    }

    public function removeNote(Note $note): static
    {
        if ($this->note->removeElement($note)) {
            // set the owning side to null (unless already changed)
            if ($note->getSortie() === $this) {
                $note->setSortie(null);
            }
        }

        return $this;
    }
}
