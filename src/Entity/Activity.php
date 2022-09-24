<?php

namespace App\Entity;

use App\Repository\ActivityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

#[ORM\Entity(repositoryClass: ActivityRepository::class)]
#[ORM\Table(name: 'activity')]
class Activity {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id = null;

	#[ORM\Column(name: 'name', type: 'string', length: 255, unique: false, nullable: true)]
	private string $name;

	#[ORM\Column(name: 'description', type: 'string', length: 255, nullable: true)]
	private string $description;

	#[ORM\ManyToMany(targetEntity: SectorArea::class, inversedBy: 'activities')]
	#[ORM\JoinColumn(name: 'id_sectorArea', referencedColumnName: 'id')]
	private SectorArea $sectorArea;

	#[ORM\OneToMany(mappedBy: 'activity', targetEntity: Country::class)]
	private Collection $country;

	public function __construct() {
		$this->country = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function setName(string $name): self {
		$this->name = $name;

		return $this;
	}

	public function getName(): string {
		return $this->name;
	}

	public function setDescription(string $description): self {
		$this->description = $description;

		return $this;
	}

	public function getDescription(): string {
		return $this->description;
	}

	public function setSectorArea(SectorArea $sectorArea = null): self {
		$this->sectorArea = $sectorArea;

		return $this;
	}

	public function getSectorArea(): SectorArea {
		return $this->sectorArea;
	}

	public function addCountry(Country $country): self {
		$this->country->add($country);

		return $this;
	}

	public function removeCountry(Country $country): void {
		$this->country->removeElement($country);
	}

	public function getCountry(): ArrayCollection|Collection {
		return $this->country;
	}

	public function __toString() {
		return $this->name;
	}
}
