<?php

namespace App\Entity;

use App\Repository\CountryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

#[ORM\Table(name: 'country')]
#[ORM\UniqueConstraint(name: 'country_name_unique', columns: ['name'])]
#[ORM\UniqueConstraint(name: 'country_iso_unique', columns: ['iso_code'])]
#[ORM\Entity(repositoryClass: CountryRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Country {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id;

	#[ORM\Column(name: 'valid', type: 'boolean')]
	private bool $valid;

	#[ORM\Column(name: 'name', type: 'string', length: 255)]
	#[Assert\NotBlank]
	private string $name;

	#[ORM\Column(name: 'iso_code', type: 'string', length: 3)]
	#[Assert\NotBlank]
	#[Assert\Length(min: '2', max: '3')]
	private string $isoCode;

	#[ORM\Column(name: 'phone_code', type: 'integer')]
	private int $phoneCode;

	#[ORM\Column(name: 'phone_digit', type: 'integer')]
	private int $phoneDigit;

	#[ORM\OneToMany(targetEntity: Region::class, mappedBy: 'country', cascade: ['remove', 'persist'])]
	private Collection $regions;

	#[ORM\ManyToOne(targetEntity: Currency::class, inversedBy: 'countries')]
	#[ORM\JoinColumn(name: 'id_currency', referencedColumnName: 'id')]
	private Currency $currency;

	public function __construct() {
		$this->regions = new ArrayCollection();
		$this->valid = false;
	}

	#[ORM\PrePersist]
	#[ORM\PreFlush]
	public function prePersist() {
		if ($this->regions->count()) {
			/** @var Region $region */
			foreach ($this->regions as $region) {
				$region->setCountry($this);
			}
		}
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getName(): string {
		return $this->name;
	}

	public function isValid(): bool {
		return $this->valid;
	}

	public function setValid(bool $valid): self {
		$this->valid = $valid;
		return $this;
	}

	public function setName(string $name): self {
		$this->name = $name;

		return $this;
	}

	public function getIsoCode(): string {
		return $this->isoCode;
	}

	public function setIsoCode(string $isoCode): self {
		$this->isoCode = strtoupper($isoCode);

		return $this;
	}

	public function getPhoneCode(): int {
		return $this->phoneCode;
	}

	public function setPhoneCode(string $phoneCode): self {
		$this->phoneCode = $phoneCode;
		return $this;
	}

	public function getPhoneDigit(): int {
		return $this->phoneDigit;
	}

	public function setPhoneDigit(int $phoneDigit): self {
		$this->phoneDigit = $phoneDigit;
		return $this;
	}

	public function getCurrency(): Currency {
		return $this->currency;
	}

	public function setCurrency(Currency $currency): self {
		$this->currency = $currency;
		return $this;
	}

	public function addRegion(Region $region): self {
		$this->regions->add($region);

		return $this;
	}

	public function removeRegion(Region $region): void {
		$this->regions->removeElement($region);
	}

	public function getRegions(): ArrayCollection {
		return $this->regions;
	}

	public function __toString(): string {
		return $this->getName();
	}
}
