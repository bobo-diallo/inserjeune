<?php

namespace App\Entity;

use App\Repository\SchoolRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'school')]
#[ORM\Entity(repositoryClass: SchoolRepository::class)]
class School {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id = null;

	#[ORM\Column(name: 'created_date', type: 'datetime', nullable: true)]
	private ?\DateTime $createdDate = null;

	#[ORM\Column(name: 'updated_date', type: 'datetime', nullable: true)]
	private ?\DateTime $updatedDate = null;

	#[ORM\Column(name: 'client_updated_date', type: 'datetime', nullable: true)]
	private ?\DateTime $clientUpdateDate = null;

	#[ORM\Column(name: 'name', type: 'string', length: 255)]
	private ?string $name;

	#[ORM\Column(name: 'type', type: 'string', length: 255)]
	private string $type;

	#[ORM\Column(name: 'agree_rgpd', type: 'boolean', nullable: true)]
	private bool $agreeRgpd = false;

	#[ORM\Column(name: 'description', type: 'string', length: 255, nullable: true)]
	private ?string $description;

	#[ORM\Column(name: 'address_number', type: 'integer', nullable: true)]
	private ?int $addressNumber;

	#[ORM\Column(name: 'address_locality', type: 'string', nullable: true)]
	private ?string $addressLocality;

	#[ORM\Column(name: 'address_road', type: 'string', nullable: true)]
	private ?string $addressRoad;

	#[ORM\Column(name: 'phone_standard', type: 'string')]
	private ?string $phoneStandard = null;

	#[ORM\Column(name: 'phone_other', type: 'string', nullable: true)]
	private ?string $phoneOther;

	#[ORM\Column(name: 'email', type: 'string')]
	private ?string $email;

	#[ORM\Column(name: 'latitude', type: 'string', nullable: true)]
	private ?string $latitude;

	#[ORM\Column(name: 'longitude', type: 'string', nullable: true)]
	private ?string $longitude;

	#[ORM\Column(name: 'maps_address', type: 'string', nullable: true)]
	private ?string $mapsAddress;

	#[ORM\Column(name: 'location_fixed', type: 'boolean')]
	private bool $locationFixed = false;

	#[ORM\Column(name: 'other_activity1', type: 'string', nullable: true)]
	private ?string $otherActivity1;

	#[ORM\Column(name: 'other_activity2', type: 'string', nullable: true)]
	private ?string $otherActivity2;

	#[ORM\Column(name: 'other_activity3', type: 'string', nullable: true)]
	private ?string $otherActivity3;

	#[ORM\Column(name: 'other_activity4', type: 'string', nullable: true)]
	private ?string $otherActivity4;

	#[ORM\Column(name: 'other_activity5', type: 'string', nullable: true)]
	private ?string $otherActivity5;

	#[ORM\Column(name: 'other_activity6', type: 'string', nullable: true)]
	private ?string $otherActivity6;

	#[ORM\OneToMany(mappedBy: 'school', targetEntity: PersonDegree::class)]
	private Collection $personDegrees;

	#[ORM\Column(name: 'registration', type: 'string', length: 255, nullable: true)]
	private ?string $registration;

	#[ORM\Column(name: 'other_degree', type: 'string', nullable: true)]
	private ?string $otherDegree;


	#[ORM\ManyToOne(targetEntity: City::class)]
	#[ORM\JoinColumn(name: 'id_city', referencedColumnName: 'id')]
	private ?City $city = null;

	#[ORM\ManyToOne(targetEntity: Region::class, inversedBy: 'schools')]
	#[ORM\JoinColumn(name: 'id_region', referencedColumnName: 'id')]
	private ?Region $region = null;

	#[ORM\ManyToOne(targetEntity: Country::class)]
	#[ORM\JoinColumn(name: 'id_country', referencedColumnName: 'id')]
	private ?Country $country = null;

	#[ORM\ManyToOne(targetEntity: Image::class, cascade: ['persist', 'remove'])]
	#[ORM\JoinColumn(name: 'id_image', referencedColumnName: 'id')]
	private Image $image;

	#[ORM\ManyToMany(targetEntity: Company::class)]
	#[ORM\JoinTable(name: 'schools_companies')]
	#[ORM\JoinColumn(name: 'school_id', referencedColumnName: 'id')]
	#[ORM\InverseJoinColumn(name: 'company_id', referencedColumnName: 'id')]
	private Collection $companies;

	#[ORM\ManyToMany(targetEntity: SocialNetwork::class, cascade: ['persist', 'remove'])]
	#[ORM\JoinTable(name: 'schools_socials')]
	#[ORM\JoinColumn(name: 'school_id', referencedColumnName: 'id')]
	#[ORM\InverseJoinColumn(name: 'social_id', referencedColumnName: 'id')]
	private Collection $socialNetworks;

	#[ORM\OneToOne(targetEntity: User::class)]
	#[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true)]
	private User $user;

	#[ORM\ManyToMany(targetEntity: Degree::class)]
	#[ORM\JoinTable(name: 'school_degrees1')]
	#[ORM\JoinColumn(name: 'school_id', referencedColumnName: 'id')]
	#[ORM\InverseJoinColumn(name: 'degree_id', referencedColumnName: 'id')]
	private Collection $degrees;

	#[ORM\ManyToOne(targetEntity: SectorArea::class)]
	#[ORM\JoinColumn(name: 'id_sector_area1', referencedColumnName: 'id')]
	private ?SectorArea $sectorArea1 = null;

	#[ORM\ManyToOne(targetEntity: SectorArea::class)]
	#[ORM\JoinColumn(name: 'id_sector_area2', referencedColumnName: 'id', nullable: true)]
	private ?SectorArea $sectorArea2 = null;

	#[ORM\ManyToOne(targetEntity: SectorArea::class)]
	#[ORM\JoinColumn(name: 'id_sector_area3', referencedColumnName: 'id', nullable: true)]
	private ?SectorArea $sectorArea3 = null;

	#[ORM\ManyToOne(targetEntity: SectorArea::class)]
	#[ORM\JoinColumn(name: 'id_sector_area4', referencedColumnName: 'id', nullable: true)]
	private ?SectorArea $sectorArea4 = null;

	#[ORM\ManyToOne(targetEntity: SectorArea::class)]
	#[ORM\JoinColumn(name: 'id_sector_area5', referencedColumnName: 'id', nullable: true)]
	private ?SectorArea $sectorArea5 = null;

	#[ORM\ManyToOne(targetEntity: SectorArea::class)]
	#[ORM\JoinColumn(name: 'id_sector_area6', referencedColumnName: 'id', nullable: true)]
	private ?SectorArea $sectorArea6 = null;

	#[ORM\ManyToMany(targetEntity: Activity::class)]
	#[ORM\JoinTable(name: 'school_activities1')]
	#[ORM\JoinColumn(name: 'school_id', referencedColumnName: 'id')]
	#[ORM\InverseJoinColumn(name: 'activity_id', referencedColumnName: 'id')]
	private Collection $activities1;

	#[ORM\ManyToMany(targetEntity: Activity::class)]
	#[ORM\JoinTable(name: 'school_activities2')]
	#[ORM\JoinColumn(name: 'school_id', referencedColumnName: 'id')]
	#[ORM\InverseJoinColumn(name: 'activity_id', referencedColumnName: 'id')]
	private Collection $activities2;

	#[ORM\ManyToMany(targetEntity: Activity::class)]
	#[ORM\JoinTable(name: 'school_activities3')]
	#[ORM\JoinColumn(name: 'school_id', referencedColumnName: 'id')]
	#[ORM\InverseJoinColumn(name: 'activity_id', referencedColumnName: 'id')]
	private Collection $activities3;

	#[ORM\ManyToMany(targetEntity: Activity::class)]
	#[ORM\JoinTable(name: 'school_activities4')]
	#[ORM\JoinColumn(name: 'school_id', referencedColumnName: 'id')]
	#[ORM\InverseJoinColumn(name: 'activity_id', referencedColumnName: 'id')]
	private Collection $activities4;

	#[ORM\ManyToMany(targetEntity: Activity::class)]
	#[ORM\JoinTable(name: 'school_activities5')]
	#[ORM\JoinColumn(name: 'school_id', referencedColumnName: 'id')]
	#[ORM\InverseJoinColumn(name: 'activity_id', referencedColumnName: 'id')]
	private Collection $activities5;

	#[ORM\ManyToMany(targetEntity: Activity::class)]
	#[ORM\JoinTable(name: 'school_activities6')]
	#[ORM\JoinColumn(name: 'school_id', referencedColumnName: 'id')]
	#[ORM\InverseJoinColumn(name: 'activity_id', referencedColumnName: 'id')]
	private Collection $activities6;

	public function __construct() {
		$this->socialNetworks = new ArrayCollection();
		$this->activities1 = new ArrayCollection();
		$this->activities2 = new ArrayCollection();
		$this->activities3 = new ArrayCollection();
		$this->activities4 = new ArrayCollection();
		$this->activities5 = new ArrayCollection();
		$this->activities6 = new ArrayCollection();
		$this->degrees = new ArrayCollection();
		$this->personDegrees = new ArrayCollection();
		$this->companies = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getCreatedDate(): ?\DateTime {
		return $this->createdDate;
	}

	public function setCreatedDate(?\DateTime $createdDate): self {
		$this->createdDate = $createdDate;
		return $this;
	}

	public function getUpdatedDate(): ?\DateTime {
		return $this->updatedDate;
	}

	public function setUpdatedDate(?\DateTime $updatedDate): self {
		$this->updatedDate = $updatedDate;
		return $this;
	}

	public function getClientUpdateDate(): ?\DateTime {
		return $this->clientUpdateDate;
	}

	public function setClientUpdateDate(?\DateTime $clientUpdateDate): self {
		$this->clientUpdateDate = $clientUpdateDate;
		return $this;
	}

	public function getName(): ?string {
		return $this->name;
	}

	public function setName(?string $name): self {
		$this->name = $name;

		return $this;
	}

	public function getType(): string {
		return $this->type;
	}

	public function setType(string $type): self {
		$this->type = $type;
		return $this;
	}

	public function isAgreeRgpd(): bool {
		return $this->agreeRgpd;
	}

	public function setAgreeRgpd(bool $agreeRgpd): self {
		$this->agreeRgpd = $agreeRgpd;
		return $this;
	}

	public function getDescription(): ?string {
		return $this->description;
	}

	public function setDescription(?string $description): void {
		$this->description = $description;
	}

	public function getCity(): ?City {
		return $this->city;
	}

	public function setCity(?City $city = null): self {
		$this->city = $city;
		return $this;
	}

	public function getRegion(): ?Region {
		return $this->region;
	}

	public function setRegion(?Region $region): self {
		$this->region = $region;
		return $this;
	}

	public function getCountry(): ?Country {
		return $this->country;
	}

	public function setCountry(?Country $country): self {
		$this->country = $country;
		return $this;
	}

	public function getAddressNumber(): ?int {
		return $this->addressNumber;
	}

	public function setAddressNumber(?int $addressNumber): void {
		$this->addressNumber = $addressNumber;
	}

	public function getAddressLocality(): ?string {
		return $this->addressLocality;
	}

	public function setAddressLocality(?string $addressLocality): void {
		$this->addressLocality = $addressLocality;
	}

	public function getAddressRoad(): ?string {
		return $this->addressRoad;
	}

	public function setAddressRoad(?string $addressRoad): void {
		$this->addressRoad = $addressRoad;
	}

	public function getPhoneStandard(): ?string {
		return $this->phoneStandard;
	}

	public function setPhoneStandard(?string $phoneStandard): void {
		$this->phoneStandard = $phoneStandard;
	}

	public function getPhoneOther(): ?string {
		return $this->phoneOther;
	}

	public function setPhoneOther(?string $phoneOther): self {
		$this->phoneOther = $phoneOther;
		return $this;
	}

	public function getEmail(): ?string {
		return $this->email;
	}

	public function setEmail(?string $email): void {
		$this->email = $email;
	}

	public function getImage(): Image {
		return $this->image;
	}

	public function setImage(Image $image): void {
		$this->image = $image;
	}

	public function getRegistration(): ?string {
		return $this->registration;
	}

	public function setRegistration(?string $registration): self {
		$this->registration = $registration;
		return $this;
	}

	public function addSocialNetwork(SocialNetwork $socialNetwork): self {
		$this->socialNetworks->add($socialNetwork);

		return $this;
	}

	public function removeSocialNetwork(SocialNetwork $socialNetwork): bool {
		return $this->socialNetworks->removeElement($socialNetwork);
	}

	public function getSocialNetworks(): Collection {
		return $this->socialNetworks;
	}

	public function getLatitude(): ?string {
		return $this->latitude;
	}

	public function setLatitude(?string $latitude): self {
		$this->latitude = $latitude;
		return $this;
	}

	public function getLongitude(): ?string {
		return $this->longitude;
	}

	public function setLongitude(?string $longitude): self {
		$this->longitude = $longitude;
		return $this;
	}

	public function getMapsAddress(): ?string {
		return $this->mapsAddress;
	}

	public function setMapsAddress(?string $mapsAddress): self {
		$this->mapsAddress = $mapsAddress;
		return $this;
	}

	public function isLocationFixed(): bool {
		return $this->locationFixed;
	}

	public function setLocationFixed(bool $locationFixed): self {
		$this->locationFixed = $locationFixed;
		return $this;
	}

	public function __toString() {
		return $this->name . ', ' . $this->city->getName();
	}

	public function setUser(User $user = null): self {
		$this->user = $user;

		return $this;
	}

	public function getUser(): User {
		return $this->user;
	}

	public function getSectorArea1(): ?SectorArea {
		return $this->sectorArea1;
	}

	public function setSectorArea1(?SectorArea $sectorArea1): self {
		$this->sectorArea1 = $sectorArea1;
		return $this;
	}

	public function getSectorArea2(): ?SectorArea {
		return $this->sectorArea2;
	}

	public function setSectorArea2(?SectorArea $sectorArea2): self {
		$this->sectorArea2 = $sectorArea2;
		return $this;
	}

	public function getSectorArea3(): ?SectorArea {
		return $this->sectorArea3;
	}

	public function setSectorArea3(?SectorArea $sectorArea3): self {
		$this->sectorArea3 = $sectorArea3;
		return $this;
	}

	public function getSectorArea4(): ?SectorArea {
		return $this->sectorArea4;
	}

	public function setSectorArea4(?SectorArea $sectorArea4): self {
		$this->sectorArea4 = $sectorArea4;
		return $this;
	}

	public function getSectorArea5(): ?SectorArea {
		return $this->sectorArea5;
	}

	public function setSectorArea5(?SectorArea $sectorArea5): self {
		$this->sectorArea5 = $sectorArea5;
		return $this;
	}

	public function getSectorArea6(): ?SectorArea {
		return $this->sectorArea6;
	}

	public function setSectorArea6(?SectorArea $sectorArea6): self {
		$this->sectorArea6 = $sectorArea6;
		return $this;
	}

	public function addActivities1(Activity $activities1): self {
		$this->activities1->add($activities1);

		return $this;
	}

	public function removeActivities1(Activity $activities1): void {
		$this->activities1->removeElement($activities1);
	}

	public function getActivities1(): Collection {
		return $this->activities1;
	}

	public function addActivities2(Activity $activities2): self {
		$this->activities2->add($activities2);

		return $this;
	}

	public function removeActivities2(Activity $activities2): void {
		$this->activities2->removeElement($activities2);
	}

	public function getActivities2(): Collection {
		return $this->activities2;
	}

	public function addActivities3(Activity $activities3): self {
		$this->activities3->add($activities3);

		return $this;
	}

	public function removeActivities3(Activity $activities3): void {
		$this->activities3->removeElement($activities3);
	}

	public function getActivities3(): Collection {
		return $this->activities3;
	}

	public function addActivities4(Activity $activities4): self {
		$this->activities4->add($activities4);

		return $this;
	}

	public function removeActivities4(Activity $activities4): void {
		$this->activities4->removeElement($activities4);
	}

	public function getActivities4(): Collection {
		return $this->activities4;
	}

	public function addDegree(Degree $degree): self {
		$this->degrees->add($degree);

		return $this;
	}

	public function removeDegree(Degree $degree): void {
		$this->degrees->removeElement($degree);
	}

	public function getDegrees(): Collection {
		return $this->degrees;
	}

	public function getOtherDegree(): ?string {
		return $this->otherDegree;
	}

	public function setOtherDegree(?string $otherDegree): self {
		$this->otherDegree = $otherDegree;
		return $this;
	}

	public function setOtherActivity1(?string $otherActivity1): self {
		$this->otherActivity1 = $otherActivity1;

		return $this;
	}

	public function getOtherActivity1(): ?string {
		return $this->otherActivity1;
	}

	public function setOtherActivity2(?string $otherActivity2): self {
		$this->otherActivity2 = $otherActivity2;

		return $this;
	}

	public function getOtherActivity2(): ?string {
		return $this->otherActivity2;
	}

	public function setOtherActivity3(?string $otherActivity3): self {
		$this->otherActivity3 = $otherActivity3;

		return $this;
	}

	public function getOtherActivity3(): ?string {
		return $this->otherActivity3;
	}

	public function setOtherActivity4(?string $otherActivity4): self {
		$this->otherActivity4 = $otherActivity4;

		return $this;
	}

	public function getOtherActivity4(): ?string {
		return $this->otherActivity4;
	}

	public function getOtherActivity5(): ?string {
		return $this->otherActivity5;
	}

	public function setOtherActivity5(?string $otherActivity5): self {
		$this->otherActivity5 = $otherActivity5;
		return $this;
	}

	public function getOtherActivity6(): ?string {
		return $this->otherActivity6;
	}

	public function setOtherActivity6(?string $otherActivity6): self {
		$this->otherActivity6 = $otherActivity6;
		return $this;
	}


	public function getAgreeRgpd(): bool {
		return $this->agreeRgpd;
	}

	public function getLocationFixed(): bool {
		return $this->locationFixed;
	}

	public function addPersonDegree(PersonDegree $personDegree) {
		$this->personDegrees->add($personDegree);

		return $this;
	}

	public function removePersonDegree(PersonDegree $personDegree): void {
		$this->personDegrees->removeElement($personDegree);
	}

	public function getPersonDegrees(): Collection {
		return $this->personDegrees;
	}

	public function addCompany(Company $company): self {
		$this->companies->add($company);

		return $this;
	}

	public function removeCompany(Company $company): void {
		$this->companies->removeElement($company);
	}

	public function getCompanies(): Collection {
		return $this->companies;
	}

	public function addActivities5(Activity $activities5): self {
		$this->activities5->add($activities5);

		return $this;
	}

	public function removeActivities5(Activity $activities5): bool {
		return $this->activities5->removeElement($activities5);
	}

	public function getActivities5(): Collection {
		return $this->activities5;
	}

	public function addActivities6(Activity $activities6): self {
		$this->activities6->add($activities6);

		return $this;
	}

	public function removeActivities6(Activity $activities6): bool {
		return $this->activities6->removeElement($activities6);
	}

	public function getActivities6(): Collection {
		return $this->activities6;
	}
}
