<?php

namespace App\Entity;

use App\Repository\RegionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'region')]
#[ORM\UniqueConstraint(name: 'region_name_unique', columns: ['name', 'id_country'])]
#[ORM\Entity(repositoryClass: RegionRepository::class)]
class Region {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id = null;

	#[ORM\Column(name: 'name', type: 'string', length: 255)]
	#[Assert\NotBlank]
	#[Assert\Length(min: '3')]
	private string $name;

	#[ORM\ManyToOne(targetEntity: Country::class, inversedBy: 'regions')]
	#[ORM\JoinColumn(name: 'id_country', referencedColumnName: 'id')]
	#[Assert\NotBlank]
	private Country $country;

	#[ORM\OneToMany(mappedBy: 'region', targetEntity: City::class, cascade: ['persist', 'remove'])]
	private Collection $cities;

	#[ORM\OneToMany(mappedBy: 'region', targetEntity: School::class, cascade: ['persist'])]
	private Collection $schools;

	public function __construct() {
		$this->cities = new ArrayCollection();
		$this->schools = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getName(): string {
		return $this->name;
	}

	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	public function getCountry(): Country {
		return $this->country;
	}

	public function setCountry(Country $country = null): static {
		$this->country = $country;

		return $this;
	}

	public function addCity(City $city): static {
		$this->cities->add($city);

		return $this;
	}

	public function removeCity(City $city): void {
		$this->cities->removeElement($city);
	}

	public function getCities(): Collection {
		return $this->cities;
	}

	public function __toString() {
		return $this->name;
	}

	public function addSchool(School $school): static {
		$this->schools->add($school);

		return $this;
	}

	public function removeSchool(School $school): void {
		$this->schools->removeElement($school);
	}

	public function getSchools(): Collection {
		return $this->schools;
	}

	public static function fromFixture(string $name): static {
		return (new static())
			->setName($name);
	}
}
