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
	private ?int $id = null;

	#[ORM\Column(name: 'name', type: 'string', length: 255)]
	private string $name;

	#[ORM\Column(name: 'description', type: 'text', nullable: true)]
	private ?string $description;


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

	public function setDescription(?string $description): self {
		$this->description = $description;

		return $this;
	}

	public function getDescription(): ?string {
		return $this->description;
	}

	public function __toString(): string {
		if ($this->description) {
			// return sprintf('%s',
			// 	ucfirst($this->name)
			// );
            return $this->name;
		}
		return sprintf('%s', ucfirst($this->name));
	}
}
