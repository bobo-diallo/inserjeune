<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait Person
 * @package App\Entity
 *
 */
trait Person {
	#[ORM\Id]
	#[ORM\Column(name: 'id', type: 'integer')]
	#[ORM\GeneratedValue(strategy: 'AUTO')]
	protected ?int $id = null;

	#[ORM\Column(name: 'firstname', type: 'string', length: 255)]
	private string $firstname;

	#[ORM\Column(name: 'lastname', type: 'string', length: 255)]
	private string $lastname;

	#[ORM\Column(name: 'birth_date', type: 'datetime')]
	private ?\DateTime $birthDate;

	#[ORM\Column(name: 'address_number', type: 'integer', nullable: true)]
	private ?int $addressNumber;

	#[ORM\Column(name: 'address_locality', type: 'string', nullable: true)]
	private ?string $addressLocality;

	#[ORM\Column(name: 'address_road', type: 'string', nullable: true)]
	private ?tring $addressRoad;

	#[ORM\ManyToOne(targetEntity: City::class)]
	#[ORM\JoinColumn(name: 'id_city', referencedColumnName: 'id')]
	private ?City $addressCity = null;

	#[ORM\Column(name: 'other_city', type: 'string', nullable: true)]
	private ?string $otherCity;

	#[ORM\ManyToOne(targetEntity: Region::class)]
	#[ORM\JoinColumn(name: 'id_region', referencedColumnName: 'id')]
	private Region $region;

	#[ORM\ManyToOne(targetEntity: Country::class)]
	#[ORM\JoinColumn(name: 'id_country', referencedColumnName: 'id')]
	private Country $country;

	#[ORM\Column(name: 'latitude', type: 'string', nullable: true)]
	private string $latitude;

	#[ORM\Column(name: 'longitude', type: 'string', nullable: true)]
	private string $longitude;

	#[ORM\Column(name: 'maps_address', type: 'string', nullable: true)]
	private string $mapsAddress;

	#[ORM\Column(name: 'phone_mobile1', type: 'string')]
	private ?string $phoneMobile1;

	#[ORM\Column(name: 'phone_mobile2', type: 'string', nullable: true)]
	private ?string $phoneMobile2;

	#[ORM\Column(name: 'phone_mobile3', type: 'string', nullable: true)]
	private ?string $phoneMobile3;

	#[ORM\Column(name: 'phone_home', type: 'string', nullable: true)]
	private ?string $phoneHome;

	#[ORM\Column(name: 'phone_office', type: 'string', nullable: true)]
	private string $phoneOffice;

	#[ORM\Column(name: 'email', type: 'string', nullable: true)]
	private ?string $email;

	#[ORM\Column(name: 'sex', type: 'string', length: 10)]
	private string $sex;

	#[ORM\ManyToOne(targetEntity: Image::class)]
	#[ORM\JoinColumn(name: 'id_image', referencedColumnName: 'id', nullable: true)]
	private Image $image;

	/**
	 * @return ?int
	 */
	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * @param int $id
	 * @return ContactCompany|Apprentice|Person|PersonDegree
	 */
	public function setId(int $id): self {
		$this->id = $id;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getFirstname(): string {
		return $this->firstname;
	}

	/**
	 * @param string $firstname
	 * @return ContactCompany|Apprentice|Person|PersonDegree
	 */
	public function setFirstname(string $firstname): self {
		$this->firstname = $firstname;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getLastname(): string {
		return $this->lastname;
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return ($this->lastname . ' ' . $this->firstname);
	}

	/**
	 * @param string $lastname
	 * @return ContactCompany|Apprentice|Person|PersonDegree
	 */
	public function setLastname(string $lastname): self {
		$this->lastname = $lastname;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getBirthDate(): ?string {
		return ($this->birthDate) ? $this->birthDate->format('m/d/Y') : null;
	}

	/**
	 * @param string $birthDate
	 * @return ContactCompany|Apprentice|Person|PersonDegree
	 */
	public function setBirthDate(string $birthDate): self {
		$this->birthDate = \DateTime::createFromFormat('d/m/Y', $birthDate);
		return $this;
	}

	public function getAddressNumber(): ?int {
		return $this->addressNumber;
	}

	public function setAddressNumber(?int $addressNumber): self {
		$this->addressNumber = $addressNumber;
		return $this;
	}

	public function getAddressLocality(): ?string {
		return $this->addressLocality;
	}

	public function setAddressLocality(?string $addressLocality): self {
		$this->addressLocality = $addressLocality;
		return $this;
	}

	public function getAddressRoad(): ?string {
		return $this->addressRoad;
	}

	public function setAddressRoad(?string $addressRoad): self {
		$this->addressRoad = $addressRoad;
		return $this;
	}

	/**
	 * @return City|null
	 */
	public function getAddressCity(): ?City {
		return $this->addressCity;
	}

	public function setAddressCity(?City $addressCity): self {
		$this->addressCity = $addressCity;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getOtherCity(): ?string {
		return $this->otherCity;
	}

	public function setOtherCity(?string $otherCity): self {
		$this->otherCity = $otherCity;
		return $this;
	}

	public function getRegion(): Region {
		return $this->region;
	}

	public function setRegion(Region $region): self {
		$this->region = $region;
		return $this;
	}

	/**
	 * @return Country|null
	 */
	public function getCountry(): ?Country {
		return $this->country;
	}

	/**
	 * @param mixed $country
	 * @return ContactCompany|Apprentice|Person|PersonDegree
	 */
	public function setCountry(Country $country): self {
		$this->country = $country;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getLatitude(): string {
		return $this->latitude;
	}

	/**
	 * @param string $latitude
	 * @return ContactCompany|Apprentice|Person|PersonDegree
	 */
	public function setLatitude(string $latitude): self {
		$this->latitude = $latitude;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getLongitude(): string {
		return $this->longitude;
	}

	/**
	 * @param string $longitude
	 * @return ContactCompany|Apprentice|Person|PersonDegree
	 */
	public function setLongitude(string $longitude): self {
		$this->longitude = $longitude;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getMapsAddress() {
		return $this->mapsAddress;
	}

	/**
	 * @param string $mapsAddress
	 * @return ContactCompany|Apprentice|Person|PersonDegree
	 */
	public function setMapsAddress(string $mapsAddress): self {
		$this->mapsAddress = $mapsAddress;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getPhoneMobile1(): ?string {
		return $this->phoneMobile1;
	}

	/**
	 * @param string|null $phoneMobile1
	 * @return ContactCompany|Apprentice|Person|PersonDegree
	 */
	public function setPhoneMobile1(?string $phoneMobile1): self {
		$this->phoneMobile1 = $phoneMobile1;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getPhoneMobile2(): ?string {
		return $this->phoneMobile2;
	}

	public function setPhoneMobile2(?string $phoneMobile2): self {
		$this->phoneMobile2 = $phoneMobile2;
		return $this;
	}

	public function getPhoneMobile3(): ?string {
		return $this->phoneMobile3;
	}

	/**
	 * @param string|null $phoneMobile3
	 * @return ContactCompany|Apprentice|Person|PersonDegree
	 */
	public function setPhoneMobile3(?string $phoneMobile3): self {
		$this->phoneMobile3 = $phoneMobile3;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getPhoneHome(): ?string {
		return $this->phoneHome;
	}

	/**
	 * @param string|null $phoneHome
	 * @return ContactCompany|Apprentice|Person|PersonDegree
	 */
	public function setPhoneHome(?string $phoneHome): self {
		$this->phoneHome = $phoneHome;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPhoneOffice(): string {
		return $this->phoneOffice;
	}

	/**
	 * @param string $phoneOffice
	 * @return ContactCompany|Apprentice|Person|PersonDegree
	 */
	public function setPhoneOffice(string $phoneOffice): self {
		$this->phoneOffice = $phoneOffice;
		return $this;
	}

	public function getEmail(): ?string {
		return $this->email;
	}

	public function setEmail(?string $email): self {
		$this->email = $email;
		return $this;
	}

	public function getSex(): string {
		return $this->sex;
	}

	/**
	 * @param string $sex
	 * @return ContactCompany|Apprentice|Person|PersonDegree
	 */
	public function setSex(string $sex): self {
		$this->sex = $sex;
		return $this;
	}

	public function getImage(): Image {
		return $this->image;
	}

	/**
	 * @param Image $image
	 */
	public function setImage(Image $image) {
		$this->image = $image;
	}
}
