<?php

namespace App\Entity;

use App\Repository\JobAppliedRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JobAppliedRepository::class)]
class JobApplied
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'id_offer', type: 'integer', nullable: true)]
    private ?int $idOffer;

    #[ORM\Column(name: 'id_user', type: 'integer', nullable: true)]
    private ?int $idUser;

    #[ORM\Column(name: 'id_city', type: 'integer', nullable: true)]
    private ?int $idCity;

    #[ORM\Column(name: 'applied_date', type: 'datetime', nullable: true)]
    private ?\DateTime $appliedDate = null;

    #[ORM\Column(name: 'resumed_applied', type: 'string', length: 766, nullable: true)]
    private ?string $resumedApplied = null;

    #[ORM\Column(name: 'is_sended', type: 'boolean')]
    private bool $isSended = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return int|null
     */
    public function getIdCity(): ?int
    {
        return $this->idCity;
    }

    /**
     * @param int|null $idCity
     */
    public function setIdCity(?int $idCity): void
    {
        $this->idCity = $idCity;
    }

    /**
     * @return \DateTime|null
     */
    public function getAppliedDate(): ?\DateTime
    {
        return $this->appliedDate;
    }

    /**
     * @param \DateTime|null $appliedDate
     */
    public function setAppliedDate(?\DateTime $appliedDate): void
    {
        $this->appliedDate = $appliedDate;
    }

    /**
     * @return string|null
     */
    public function getResumedApplied(): ?string
    {
        return $this->resumedApplied;
    }

    /**
     * @param string|null $resumedApplied
     */
    public function setResumedApplied(?string $resumedApplied): void
    {
        $this->resumedApplied = $resumedApplied;
    }

    /**
     * @return bool
     */
    public function isSended(): bool
    {
        return $this->isSended;
    }

    /**
     * @param bool $isSended
     */
    public function setIsSended(bool $isSended): void
    {
        $this->isSended = $isSended;
    }

    /**
     * @return int|null
     */
    public function getIdOffer(): ?int
    {
        return $this->idOffer;
    }

    /**
     * @param int|null $idOffer
     */
    public function setIdOffer(?int $idOffer): void
    {
        $this->idOffer = $idOffer;
    }

    /**
     * @return int|null
     */
    public function getIdUser(): ?int
    {
        return $this->idUser;
    }

    /**
     * @param int|null $idUser
     */
    public function setIdUser(?int $idUser): void
    {
        $this->idUser = $idUser;
    }

}
