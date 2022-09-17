<?php

namespace App\Entity;

use App\Repository\ContractRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'contract')]
#[ORM\Entity(repositoryClass: ContractRepository::class)]
class Contract {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id;

	#[ORM\Column(name: 'name', type: 'string', length: 255)]
	private string $name;

	#[ORM\Column(name: 'description', type: 'string', length: 255, nullable: true)]
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

	public function setDescription($description): self {
		$this->description = $description;

		return $this;
	}

	public function getDescription(): string {
		return $this->description;
	}

	public function __toString(): string {
		if ($this->description) {
			return sprintf('%s - %s',
				ucfirst($this->name),
				ucfirst($this->description)
			);
		}
		return sprintf('%s', ucfirst($this->name));
	}
}
