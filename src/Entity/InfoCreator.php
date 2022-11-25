<?php

namespace App\Entity;

use App\Repository\InfoCreatorRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'info_creator')]
#[ORM\Entity(repositoryClass: InfoCreatorRepository::class)]
class InfoCreator {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id = null;

	#[ORM\Column(name: 'name', type: 'string', length: 255, unique: true)]
	private ?string $name;

	#[ORM\Column(name: 'description', type: 'text', nullable: true)]
	private ?string $description;

	public function getId(): ?int {
		return $this->id;
	}

	public function setName(?string $name): static {
		$this->name = $name;

		return $this;
	}

	public function getName(): ?string {
		return $this->name;
	}

	public function setDescription(?string $description): static {
		$this->description = $description;

		return $this;
	}

	public function getDescription(): ?string {
		return $this->description;
	}
}
