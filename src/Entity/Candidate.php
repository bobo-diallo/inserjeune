<?php

namespace App\Entity;

use App\Repository\CandidateRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Table(name: 'candidate')]
#[ORM\Entity(repositoryClass: CandidateRepository::class)]
class Candidate {
	#[ORM\Id]
	#[ORM\Column(name: 'id', type: 'integer')]
	#[ORM\GeneratedValue(strategy: 'AUTO')]
	private ?int $id;

	private File $coverLetter;

	private File $cv;

	private string $emailDestination;

	private string $candidateName;

	private string $message;

	public function getId(): ?int {
		return $this->id;
	}

	public function setCoverLetter(string $coverLetter): self {
		$this->coverLetter = $coverLetter;

		return $this;
	}

	public function getCoverLetter(): File {
		return $this->coverLetter;
	}

	public function setCv(File $cv): self {
		$this->cv = $cv;

		return $this;
	}

	public function getCv(): File {
		return $this->cv;
	}

	public function setEmailDestination(string $emailDestination): self {
		$this->emailDestination = $emailDestination;

		return $this;
	}

	public function getEmailDestination(): string {
		return $this->emailDestination;
	}

	public function getCandidateName(): string {
		return $this->candidateName;
	}

	public function setCandidateName(string $candidateName): self {
		$this->candidateName = $candidateName;
		return $this;
	}

	public function getMessage(): string {
		return $this->message;
	}

	public function setMessage(string $message): self {
		$this->message = $message;
		return $this;
	}
}

