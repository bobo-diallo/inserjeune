<?php

namespace App\Entity;

use App\Repository\JobNotFoundReasonRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'JobNotFoundReasonRepository')]
#[ORM\Entity(repositoryClass: JobNotFoundReasonRepository::class)]
class JobNotFoundReason {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id = null;

	#[ORM\Column(name: 'name', type: 'string', length: 255)]
	private string $name;

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

	public function __toString() {
		return $this->getName();
	}
}
