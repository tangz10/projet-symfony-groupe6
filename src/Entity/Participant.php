<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
class Participant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255)]
    private ?string $mail = null;

    #[ORM\Column]
    private ?bool $administrateur = null;

    #[ORM\Column]
    private ?bool $actif = null;

    #[ORM\ManyToOne(inversedBy: 'participants')]
    private ?Site $Site = null;

    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\OneToMany(targetEntity: Sortie::class, mappedBy: 'ParticipantOrganisateur')]
    private Collection $SortiesParticipants;

    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\ManyToMany(targetEntity: Sortie::class, mappedBy: 'ParticipantInscrit')]
    private Collection $ParticipantsInscrits;

    public function __construct()
    {
        $this->SortiesParticipants = new ArrayCollection();
        $this->ParticipantsInscrits = new ArrayCollection();
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): static
    {
        $this->mail = $mail;

        return $this;
    }

    public function isAdministrateur(): ?bool
    {
        return $this->administrateur;
    }

    public function setAdministrateur(bool $administrateur): static
    {
        $this->administrateur = $administrateur;

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;

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

    /**
     * @return Collection<int, Sortie>
     */
    public function getSortiesParticipants(): Collection
    {
        return $this->SortiesParticipants;
    }

    public function addSortiesParticipant(Sortie $sortiesParticipant): static
    {
        if (!$this->SortiesParticipants->contains($sortiesParticipant)) {
            $this->SortiesParticipants->add($sortiesParticipant);
            $sortiesParticipant->setParticipantOrganisateur($this);
        }

        return $this;
    }

    public function removeSortiesParticipant(Sortie $sortiesParticipant): static
    {
        if ($this->SortiesParticipants->removeElement($sortiesParticipant)) {
            // set the owning side to null (unless already changed)
            if ($sortiesParticipant->getParticipantOrganisateur() === $this) {
                $sortiesParticipant->setParticipantOrganisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getParticipantsInscrits(): Collection
    {
        return $this->ParticipantsInscrits;
    }

    public function addParticipantsInscrit(Sortie $participantsInscrit): static
    {
        if (!$this->ParticipantsInscrits->contains($participantsInscrit)) {
            $this->ParticipantsInscrits->add($participantsInscrit);
            $participantsInscrit->addParticipantInscrit($this);
        }

        return $this;
    }

    public function removeParticipantsInscrit(Sortie $participantsInscrit): static
    {
        if ($this->ParticipantsInscrits->removeElement($participantsInscrit)) {
            $participantsInscrit->removeParticipantInscrit($this);
        }

        return $this;
    }
}
