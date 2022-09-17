<?php

namespace App\Entity;

use App\Repository\OmissionReasonRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'omission_reason')]
#[ORM\Entity(repositoryClass: OmissionReasonRepository::class)]
class OmissionReason {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id;

	#[ORM\Column(name: 'name', type: 'string', length: 255)]
	private string $name;

	#[ORM\Column(name: 'level', type: 'integer', nullable: true)]
	private int $level;

	#[ORM\Column(name: 'complete_training', type: 'boolean')]
	private bool $completeTraining;

	public function getId(): ?int {
		return $this->id;
	}

	public function setName($name): self {
		$this->name = $name;

		return $this;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getLevel(): int {
		return $this->level;
	}

	public function setLevel($level): self {
		$this->level = $level;
		return $this;
	}

	public function isCompleteTraining(): bool {
		return $this->completeTraining;
	}

	public function setCompleteTraining(bool $completeTraining): self {
		$this->completeTraining = $completeTraining;
		return $this;
	}

	public function __toString() {
		return $this->getName();
	}
}

