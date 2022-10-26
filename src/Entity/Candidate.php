<?php

namespace App\Entity;

use App\Repository\CandidateRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'candidate')]
#[ORM\Entity(repositoryClass: CandidateRepository::class)]
class Candidate {
	#[ORM\Id]
	#[ORM\Column(name: 'id', type: 'integer')]
	#[ORM\GeneratedValue(strategy: 'AUTO')]
	private ?int $id = null;

	private ?string $cvFilename;

	private ?string $coverLetterFilename;

	private ?string $emailDestination;

	private ?string $candidateName;

	private ?string $message;

	public function getId(): ?int {
		return $this->id;
	}

	public function setEmailDestination(string $emailDestination): self {
		$this->emailDestination = $emailDestination;

		return $this;
	}

	public function getEmailDestination(): string {
		return $this->emailDestination;
	}


	public function getCvFilename(): ?string {
		return $this->cvFilename;
	}

	public function setCvFilename(?string $cvFilename): self {
		$this->cvFilename = $cvFilename;

		return $this;
	}

	public function getCoverLetterFilename(): ?string {
		return $this->coverLetterFilename;
	}

	public function setCoverLetterFilename(?string $coverLetterFilename): self {
		$this->coverLetterFilename = $coverLetterFilename;
		return $this;
	}

	public function getCandidateName(): ?string {
		return $this->candidateName;
	}

	public function setCandidateName(?string $candidateName): self {
		$this->candidateName = $candidateName;

		return $this;
	}

	public function getMessage(): ?string {
		return $this->message;
	}

	public function setMessage(?string $message): self {
		$this->message = $message;

		return $this;
	}

}

