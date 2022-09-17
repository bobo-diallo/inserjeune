<?php

namespace App\Entity;

use App\Repository\ImageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'image')]
#[ORM\Entity(repositoryClass: ImageRepository::class)]
#[ORM\UniqueConstraint(name: 'image_url_unique', columns: ['url'])]
class Image {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id;

	#[ORM\Column(name: 'name', type: 'string', length: 255)]
	private string $name;

	#[ORM\Column(name: 'url', type: 'string', length: 400)]
	private string $url;

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

	public function setUrl(string $url): self {
		$this->url = $url;

		return $this;
	}

	public function getUrl(): string {
		return $this->url;
	}
}
