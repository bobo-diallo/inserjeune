<?php

namespace App\Entity;

use App\Repository\GeoLocationRepository;
use App\Repository\SchoolRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Collection;

#[ORM\Table(name: 'geo_location')]
#[ORM\Entity(repositoryClass: GeoLocationRepository::class)]
class GeoLocation {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id = null;

	#[ORM\ManyToOne(targetEntity: City::class)]
	#[ORM\JoinColumn(name: 'id_city', referencedColumnName: 'id')]
	private ?City $city = null;

	#[ORM\Column(name: 'other_city', type: 'string', length: 255, nullable: true)]
	private ?string $otherCity;

	#[ORM\Column(name: 'address_locality', type: 'string', nullable: true)]
	private ?string $locality;

	#[ORM\Column(name: 'address_road', type: 'string', nullable: true)]
	private ?string $road;

	#[ORM\Column(name: 'address_number', type: 'integer', nullable: true)]
	private ?int $number;

	#[ORM\Column(name: 'show_companies', type: 'boolean')]
	private bool $showCompanies;

	#[ORM\Column(name: 'show_schools', type: 'boolean')]
	private bool $showSchools;

	#[ORM\Column(name: 'show_search_person_degrees', type: 'boolean')]
	private bool $showSearchPersonDegrees;

    #[ORM\Column(name: 'show_other_person_degrees', type: 'boolean')]
    private bool $showOtherPersonDegrees;

	#[ORM\ManyToOne(targetEntity: Region::class)]
	#[ORM\JoinColumn(name: 'id_region', referencedColumnName: 'id')]
	private Region $region;

	#[ORM\ManyToOne(targetEntity: Country::class)]
	#[ORM\JoinColumn(name: 'id_country', referencedColumnName: 'id')]
	private Country $country;

	#[ORM\ManyToOne(targetEntity: Activity::class)]
	#[ORM\JoinColumn(nullable: true)]
	private ?Activity $activity = null;

	#[ORM\ManyToOne(targetEntity: SectorArea::class)]
	#[ORM\JoinColumn(name: 'id_sectorArea', referencedColumnName: 'id')]
	private SectorArea $sectorArea;

	public function getId(): ?int {
		return $this->id;
	}

	public function getLocality(): ?string {
		return $this->locality;
	}

	public function setLocality(?string $locality): self {
		$this->locality = $locality;
		return $this;
	}

	public function getRoad(): ?string {
		return $this->road;
	}

	public function setRoad(?string $road): self {
		$this->road = $road;
		return $this;
	}

	public function getNumber(): ?int {
		return $this->number;
	}

	public function setNumber(?int $number): self {
		$this->number = $number;
		return $this;
	}

	public function setCity(?City $city = null): self {
		$this->city = $city;

		return $this;
	}

	public function getCity(): ?City {
		return $this->city;
	}

	public function getOtherCity(): ?string {
		return $this->otherCity;
	}

	public function setOtherCity(?string $otherCity): self {
		$this->otherCity = $otherCity;
		return $this;
	}

	public function setRegion(?Region $region = null): self {
		$this->region = $region;

		return $this;
	}

	public function getRegion(): ?Region {
		return $this->region;
	}

	public function setCountry(Country $country = null): self {
		$this->country = $country;

		return $this;
	}

	public function getCountry(): Country {
		return $this->country;
	}

	public function getActivity(): ?Activity {
		return $this->activity;
	}

	public function setActivity($activity): self {
		$this->activity = $activity;
		return $this;
	}

	public function getSectorArea(): SectorArea {
		return $this->sectorArea;
	}

	public function setSectorArea(SectorArea $sectorArea): self {
		$this->sectorArea = $sectorArea;
		return $this;
	}

	public function isShowCompanies(): bool {
		return $this->showCompanies;
	}

	public function setShowCompanies(bool $showCompanies): self {
		$this->showCompanies = $showCompanies;
		return $this;
	}

	public function isShowSearchPersonDegrees(): bool {
		return $this->showSearchPersonDegrees;
	}

	public function isShowSchools(): bool {
		return $this->showSchools;
	}

	public function setShowSchools(bool $showSchools): self {
		$this->showSchools = $showSchools;
		return $this;
	}

	public function setShowSearchPersonDegrees(bool $showPersonDegrees): self {
		$this->showSearchPersonDegrees = $showPersonDegrees;
		return $this;
	}

    /**
     * @return bool
     */
    public function isShowOtherPersonDegrees(): bool
    {
        return $this->showOtherPersonDegrees;
    }

    /**
     * @param bool $showOtherPersonDegrees
     */
    public function setShowOtherPersonDegrees(bool $showOtherPersonDegrees): void
    {
        $this->showOtherPersonDegrees = $showOtherPersonDegrees;
    }

    /**
     * @return Prefecture|null
     */
    public function getPrefecture(): ?Prefecture {
        return $this->city?->getPrefecture();
    }

    /**
     * @param Prefecture|null $prefecture
     * @return GeoLocation
     */
    public function setPrefecture(?Prefecture $prefecture): GeoLocation
    {
        return $this;
    }

}
