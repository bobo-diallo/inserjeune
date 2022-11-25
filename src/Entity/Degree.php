<?php

namespace App\Entity;

use App\Repository\DegreeRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

#[ORM\Table(name: 'degree')]
#[ORM\Entity(repositoryClass: DegreeRepository::class)]
#[ORM\UniqueConstraint(name: 'degree_name_unique', columns: ['name'])]
class Degree {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id = null;

	#[ORM\Column(name: 'name', type: 'string', length: 255)]
	private string $name;

	#[ORM\Column(name: 'description', type: 'text', nullable: true)]
	private ?string $description;

	#[ORM\Column(name: 'level', type: 'integer')]
	private int $level;

	#[ORM\ManyToMany(targetEntity: School::class, mappedBy: 'degree')]
	private Collection $schools;

	#[ORM\ManyToOne(targetEntity: Activity::class)]
	#[ORM\JoinColumn(nullable: true)]
	private ?Activity $activity = null;

	public function __construct() {
		$this->schools = new ArrayCollection();
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

	public function getDescription(): ?string {
		return $this->description;
	}

	public function setDescription(?string $description): self {
		$this->description = $description;
		return $this;
	}

	public function getLevel(): int {
		return $this->level;
	}

	public function setLevel($level): self {
		$this->level = $level;
		return $this;
	}

	public function getActivity(): ?Activity {
		return $this->activity;
	}

	public function setActivity(?Activity $activity): self {
		$this->activity = $activity;
		return $this;
	}

	public function __toString() {
		return $this->name;
	}

	public function addSchool(School $school): self {
		$this->schools->add($school);

		return $this;
	}

	public function removeSchool(School $school): void {
		$this->schools->removeElement($school);
	}

	public function getSchools(): Collection {
		return $this->schools;
	}
}
