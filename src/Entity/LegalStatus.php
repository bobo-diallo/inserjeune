<?php

namespace App\Entity;

use App\Repository\LegalStatusRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'legal_status')]
#[ORM\UniqueConstraint(name: 'legal_status_name_unique', columns: ['name'])]
#[ORM\Entity(repositoryClass: LegalStatusRepository::class)]
class LegalStatus {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id = null;

	#[ORM\Column(name: 'name', type: 'string', length: 255)]
	private string $name;

	#[ORM\Column(name: 'description', type: 'string', length: 255)]
	private string $description;

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
}
