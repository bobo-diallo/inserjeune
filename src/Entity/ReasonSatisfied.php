<?php

namespace App\Entity;

use App\Repository\ReasonSatisfiedRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'reason_satisfied')]
#[ORM\Entity(repositoryClass: ReasonSatisfiedRepository::class)]
class ReasonSatisfied {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id;

	#[ORM\Column(name: 'name', type: 'string', length: 255)]
	private string $name;

	public function getId(): ?int {
		return $this->id;
	}

	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	public function getName(): string {
		return $this->name;
	}

	public function __toString() {
		return $this->getName();
	}
}
