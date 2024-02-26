<?php

namespace App\Entity;

use App\Repository\TrajetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TrajetRepository::class)]
class Trajet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $kms = null;

    #[ORM\Column(nullable: true)]
    private ?int $placesDisponible = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ville $departVille = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ville $arriverVille = null;

    #[ORM\ManyToMany(targetEntity: Personne::class, inversedBy: 'trajets', fetch: "EAGER")]
    private Collection $passager;

    #[ORM\ManyToOne(inversedBy: 'trajets', fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: false)]
    private ?Personne $conducteur = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateDepart = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $heureDepart = null;

    public function __construct()
    {
        $this->passager = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKms(): ?int
    {
        return $this->kms;
    }

    public function setKms(?int $kms): static
    {
        $this->kms = $kms;

        return $this;
    }

    public function getPlacesDisponible(): ?int
    {
        return $this->placesDisponible;
    }

    public function setPlacesDisponible(?int $placesDisponible): static
    {
        $this->placesDisponible = $placesDisponible;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getDepartVille(): ?Ville
    {
        return $this->departVille;
    }

    public function setDepartVille(?Ville $departVille): static
    {
        $this->departVille = $departVille;

        return $this;
    }

    public function getArriverVille(): ?Ville
    {
        return $this->arriverVille;
    }

    public function setArriverVille(?Ville $arriverVille): static
    {
        $this->arriverVille = $arriverVille;

        return $this;
    }

    /**
     * @return Collection<int, Personne>
     */
    public function getPassager(): Collection
    {
        return $this->passager;
    }

    public function addPassager(Personne $passager): static
    {
        if (!$this->passager->contains($passager)) {
            $this->passager->add($passager);
        }

        return $this;
    }

    public function removePassager(Personne $passager): static
    {
        $this->passager->removeElement($passager);

        return $this;
    }

    public function getConducteur(): ?Personne
    {
        return $this->conducteur;
    }

    public function setConducteur(?Personne $conducteur): static
    {
        $this->conducteur = $conducteur;

        return $this;
    }

    public function getDateDepart(): ?\DateTimeInterface
    {
        return $this->dateDepart;
    }

    public function setDateDepart(?\DateTimeInterface $dateDepart): static
    {
        $this->dateDepart = $dateDepart;

        return $this;
    }

    public function getHeureDepart(): ?\DateTimeInterface
    {
        return $this->heureDepart;
    }

    public function setHeureDepart(?\DateTimeInterface $heureDepart): static
    {
        $this->heureDepart = $heureDepart;

        return $this;
    }
}
