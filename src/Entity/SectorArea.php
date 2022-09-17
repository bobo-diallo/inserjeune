<?php

namespace App\Entity;

use App\Repository\SectorAreaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'sector_area')]
#[ORM\Entity(repositoryClass: SectorAreaRepository::class)]
class SectorArea {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id;

	#[ORM\Column(name: 'name', type: 'string', length: 255)]
	private string $name;

	#[ORM\Column(name: 'description', type: 'string', length: 255)]
	private string $description;

	#[ORM\OneToMany(targetEntity: Activity::class, mappedBy: 'sectorArea')]
	private Collection $activities;

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

	public function __toString() {
		return $this->name;
	}

	public function __construct() {
		$this->activities = new ArrayCollection();
	}

	public function addActivity(Activity $activity): self {
		$this->activities->add($activity);

		return $this;
	}

	public function removeActivity(Activity $activity): void {
		$this->activities->removeElement($activity);
	}

	public function getActivities(): ArrayCollection|Collection {
		return $this->activities;
	}
}
