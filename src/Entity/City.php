<?php

namespace App\Entity;

use App\Repository\CityRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'city')]
#[ORM\UniqueConstraint(name: 'city_region_unique', columns: ['name', 'id_region'])]
#[ORM\Entity(repositoryClass: CityRepository::class)]
class City {
	#[ORM\Id]
	#[ORM\Column(name: 'id', type: 'integer')]
	#[ORM\GeneratedValue(strategy: 'AUTO')]
	private ?int $id = null;

	#[ORM\Column(name: 'name', type: 'string', length: 255)]
	#[Assert\NotBlank]
	#[Assert\Length(min: '4')]
	private string $name;

	#[ORM\Column(name: 'post_code', type: 'integer', nullable: true)]
	private ?int $postCode;

	#[ORM\Column(name: 'is_capital', type: 'boolean')]
	private bool $isCapital;

	#[ORM\ManyToOne(targetEntity: Region::class, inversedBy: 'cities')]
	#[ORM\JoinColumn(name: 'id_region', referencedColumnName: 'id')]
	#[Assert\NotNull]
	private ?Region $region = null;

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

	public function getPostCode(): ?int {
		return $this->postCode;
	}

	public function setPostCode(?int $postCode): self {
		$this->postCode = $postCode;

		return $this;
	}

	public function setIsCapital(bool $isCapital): self {
		$this->isCapital = $isCapital;

		return $this;
	}

	public function getIsCapital(): bool {
		return $this->isCapital;
	}

	public function setRegion(Region $region = null): self {
		$this->region = $region;

		return $this;
	}

	public function getRegion(): ?Region {
		return $this->region;
	}

	public function getCountry(): Country {
		return $this->region->getCountry();
	}

	public function __toString(): string {
		return sprintf('%s - %s - %s',
			ucfirst($this->region->getCountry()->getName()),
			ucfirst($this->region->getName()),
			ucfirst($this->name)
		);
	}
}
