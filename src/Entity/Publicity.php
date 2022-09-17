<?php

namespace App\Entity;

use App\Repository\PublicityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'publicity')]
#[ORM\Entity(repositoryClass: PublicityRepository::class)]
class Publicity {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id;

	#[ORM\Column(name: 'title', type: 'string', length: 255)]
	private string $title;

	#[ORM\Column(name: 'createdDate', type: 'datetime')]
	private \DateTime $createdDate;

	#[ORM\Column(name: 'closedDate', type: 'datetime')]
	private \DateTime $closedDate;

	#[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'publicities')]
	#[ORM\JoinColumn(name: 'id_company', referencedColumnName: 'id')]
	private Company $company;

	#[ORM\ManyToOne(targetEntity: Image::class, inversedBy: 'publicities')]
	#[ORM\JoinColumn(name: 'id_image', referencedColumnName: 'id')]
	private Image $image;

	public function getId(): ?int {
		return $this->id;
	}

	public function setTitle(string $title): static {
		$this->title = $title;

		return $this;
	}

	public function getTitle(): string {
		return $this->title;
	}

	public function setCreatedDate(\DateTime $createdDate): static {
		$this->createdDate = $createdDate;

		return $this;
	}

	public function getCreatedDate(): \DateTime {
		return $this->createdDate;
	}

	public function setClosedDate(\DateTime $closedDate): static {
		$this->closedDate = $closedDate;

		return $this;
	}

	public function getClosedDate(): \DateTime {
		return $this->closedDate;
	}

	public function setCompany(Company $company): static {
		$this->company = $company;

		return $this;
	}

	public function getCompany(): Company {
		return $this->company;
	}

	public function getImage(): Image {
		return $this->image;
	}

	public function setImage(Image $image): void {
		$this->image = $image;
	}

}
