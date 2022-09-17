<?php

namespace App\Entity;

use App\Repository\CurrencyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'currency')]
#[ORM\Entity(repositoryClass: CurrencyRepository::class)]
class Currency {
	#[ORM\Id]
	#[ORM\GeneratedValue('AUTO')]
	#[ORM\Column(type: 'integer')]
	private ?int $id;

	#[ORM\Column(name: 'name', type: 'string', length: 255)]
	private string $name;

	#[ORM\Column(name: 'iso_name', type: 'string', length: 255)]
	private string $isoName;

	#[ORM\Column(name: 'iso_num', type: 'string', length: 255)]
	private string $isoNum;

	#[ORM\Column(name: 'iso_symbol', type: 'string', length: 255)]
	private string $isoSymbol;

	#[ORM\OneToMany(targetEntity: Country::class, mappedBy: 'currency', cascade: ['remove', 'persist'])]
	private Collection $countries;

	public function __construct() {
		$this->countries = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getIsoName(): string {
		return $this->isoName;
	}

	public function setIsoName($isoName): static {
		$this->isoName = $isoName;
		return $this;
	}

	public function getIsoNum(): string {
		return $this->isoNum;
	}

	public function setIsoNum(string $isoNum): static {
		$this->isoNum = $isoNum;
		return $this;
	}

	public function getIsoSymbol(): string {
		return $this->isoSymbol;
	}

	public function setIsoSymbol($isoSymbol): static {
		$this->isoSymbol = $isoSymbol;
		return $this;
	}

	public function addCountry(Country $country): static {
		$this->countries->add($country);

		return $this;
	}

	public function removeCountry(Country $country): void {
		$this->countries->removeElement($country);
	}

	public function getCountries(): ArrayCollection {
		return $this->countries;
	}

	public function __toString() {
		return sprintf('%s', strtoupper($this->name));
	}
}
