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

    #[ORM\Column(name: 'valid', type: 'boolean')]
    private bool $valid;

    #[ORM\Column(name: 'iso_code', type: 'string', length: 3, unique: false, nullable: true)]
    private ?string $isoCode = null;

    #[ORM\Column(name: 'phone_code', type: 'integer')]
    private int $phoneCode;

    #[ORM\Column(name: 'phone_digit', type: 'integer')]
    private int $phoneDigit;

	#[ORM\ManyToOne(targetEntity: Country::class, inversedBy: 'regions')]
	#[ORM\JoinColumn(name: 'id_country', referencedColumnName: 'id')]
	#[Assert\NotBlank]
	private Country $country;

	#[ORM\OneToMany(mappedBy: 'region', targetEntity: City::class, cascade: ['persist', 'remove'])]
	private Collection $cities;

	#[ORM\OneToMany(mappedBy: 'region', targetEntity: School::class, cascade: ['persist'])]
	private Collection $schools;

    #[ORM\ManyToOne(targetEntity: Currency::class, inversedBy: 'regions')]
    #[ORM\JoinColumn(name: 'id_currency', referencedColumnName: 'id')]
    private Currency $currency;

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

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * @param bool $valid
     * @return Region
     */
    public function setValid(bool $valid): Region
    {
        $this->valid = $valid;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIsoCode(): ?string
    {
        return $this->isoCode;
    }

    /**
     * @param string|null $isoCode
     * @return Region
     */
    public function setIsoCode(?string $isoCode): Region
    {
        $this->isoCode = $isoCode;
        return $this;
    }

    /**
     * @return int
     */
    public function getPhoneCode(): int
    {
        return $this->phoneCode;
    }

    /**
     * @param int $phoneCode
     * @return Region
     */
    public function setPhoneCode(int $phoneCode): Region
    {
        $this->phoneCode = $phoneCode;
        return $this;
    }

    /**
     * @return int
     */
    public function getPhoneDigit(): int
    {
        return $this->phoneDigit;
    }

    /**
     * @param int $phoneDigit
     * @return Region
     */
    public function setPhoneDigit(int $phoneDigit): Region
    {
        $this->phoneDigit = $phoneDigit;
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

    /**
     * @return Currency
     */
    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    /**
     * @param Currency $currency
     * @return Region
     */
    public function setCurrency(Currency $currency): Region
    {
        $this->currency = $currency;
        return $this;
    }
}
