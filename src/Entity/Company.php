<?php

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Company {
	#[ORM\Id]
	#[ORM\Column(name: 'id', type: 'integer')]
	#[ORM\GeneratedValue(strategy: 'AUTO')]
	private ?int $id = null;

	#[ORM\Column(name: 'name', type: 'string', length: 255)]
	private ?string $name;

	#[ORM\Column(name: 'url', type: 'string', length: 255, nullable: true)]
	private ?string $url;

	#[ORM\Column(name: 'created_date', type: 'datetime')]
	private ?\DateTime $createdDate = null;

	#[ORM\Column(name: 'updated_date', type: 'datetime', nullable: true)]
	private ?\DateTime $updatedDate = null;

	#[ORM\Column(name: 'client_updated_date', type: 'datetime', nullable: true)]
	private ?\DateTime $clientUpdateDate = null;

	#[ORM\ManyToOne(targetEntity: City::class)]
	#[ORM\JoinColumn(name: 'id_city', referencedColumnName: 'id')]
	private ?City $city = null;

	#[ORM\Column(name: 'other_city', type: 'string', nullable: true)]
	private ?string $otherCity;

	#[ORM\ManyToOne(targetEntity: Region::class)]
	#[ORM\JoinColumn(name: 'id_region', referencedColumnName: 'id')]
	private ?Region $region;

	#[ORM\ManyToOne(targetEntity: Country::class)]
	#[ORM\JoinColumn(name: 'id_country', referencedColumnName: 'id')]
	private ?Country $country = null;

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

    #[ORM\Column(name: 'temporary_passwd', type: 'string', nullable: true)]
    private ?string $temporaryPasswd;

	#[ORM\ManyToOne(targetEntity: ContactCompany::class)]
	#[ORM\JoinColumn(name: 'id_contactCompany', referencedColumnName: 'id')]
	private ?ContactCompany $contactCompany = null;

	#[ORM\ManyToOne(targetEntity: SectorArea::class)]
	#[ORM\JoinColumn(name: 'id_sectorArea', referencedColumnName: 'id')]
	private ?SectorArea $sectorArea = null;

	#[ORM\ManyToOne(targetEntity: LegalStatus::class)]
	#[ORM\JoinColumn(name: 'id_legal_status', referencedColumnName: 'id')]
	private ?LegalStatus $legalStatus = null;

	#[ORM\OneToMany(mappedBy: 'company', targetEntity: JobOffer::class, cascade: ['persist', 'remove'])]
	private Collection $jobOffers;

	#[ORM\OneToMany(mappedBy: 'company', targetEntity: Publicity::class, cascade: ['persist', 'remove'])]
	private Collection $publicities;

	#[ORM\OneToMany(mappedBy: 'company', targetEntity: PersonDegree::class)]
	private Collection $salaries;

	#[ORM\OneToMany(mappedBy: 'company', targetEntity: Apprentice::class, cascade: ['persist', 'remove'])]
	private Collection $apprentices;

	#[ORM\OneToMany(mappedBy: 'company', targetEntity: SatisfactionCompany::class, cascade: ['persist', 'remove'])]
	private Collection $satisfactionCompanies;

	#[ORM\ManyToMany(targetEntity: SocialNetwork::class, cascade: ['persist', 'remove'])]
	#[ORM\JoinTable(name: 'companies_socials')]
	#[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'id')]
	#[ORM\InverseJoinColumn(name: 'social_id', referencedColumnName: 'id')]
	private Collection $socialNetworks;

	#[ORM\Column(name: 'latitude', type: 'string', nullable: true)]
	private ?string $latitude;

	#[ORM\Column(name: 'longitude', type: 'string', nullable: true)]
	private ?string $longitude;

	#[ORM\Column(name: 'maps_address', type: 'string', nullable: true)]
	private ?string $mapsAddress;

	#[ORM\Column(name: 'location_fixed', type: 'boolean')]
	private bool $locationFixed = false;

	#[ORM\OneToOne(inversedBy: 'company', targetEntity: 'User')]
	#[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true)]
	private ?User $user = null;

	#[ORM\Column(name: 'agree_rgpd', type: 'boolean', nullable: true)]
	private bool $agreeRgpd = false;

	#[ORM\ManyToMany(targetEntity: School::class, mappedBy: 'companies')]
	private Collection $schools;

	#[ORM\Column(name: 'location_mode', type: 'boolean', nullable: true)]
	private ?bool $locationMode;

	#[ORM\OneToOne(targetEntity: Image::class)]
	#[ORM\JoinColumn(name: 'id_image', referencedColumnName: 'id', nullable: true)]
	private ?Image $logo = null;

	#[ORM\Column(name: 'unlocked', type: 'boolean', nullable: true)]
	private ?bool $unlocked = true;


	public function __construct() {
		$this->jobOffers = new ArrayCollection();
		$this->publicities = new ArrayCollection();
		$this->salaries = new ArrayCollection();
		$this->apprentices = new ArrayCollection();
		$this->satisfactionCompanies = new ArrayCollection();
		$this->schools = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getName(): ?string {
		return $this->name;
	}

	public function setName(?string $name): self {
		$this->name = $name;

		return $this;
	}

	public function getUrl(): ?string {
		return $this->url;
	}

	public function setUrl(?string $url): self {
		$this->url = $url;
		return $this;
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

	public function getCity(): ?City {
		return $this->city;
	}

	public function setCity(City $city = null): self {
		$this->city = $city;

		return $this;
	}

	public function getOtherCity(): ?string {
		return $this->otherCity;
	}

	public function setOtherCity(?string $otherCity): self {
		$this->otherCity = $otherCity;
		return $this;
	}

	public function getAddressNumber(): ?int {
		return $this->addressNumber;
	}

	public function setAddressNumber(?int $addressNumber) {
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

    public function getTemporaryPasswd(): ?string {
        return $this->temporaryPasswd;
    }

    public function setTemporaryPasswd(?string $temporaryPasswd): void {
        $this->temporaryPasswd = $temporaryPasswd;
    }

	public function getSectorArea(): ?SectorArea {
		return $this->sectorArea;
	}

	public function setSectorArea(?SectorArea $sectorArea = null): self {
		$this->sectorArea = $sectorArea;

		return $this;
	}

	public function getLegalStatus(): ?LegalStatus {
		return $this->legalStatus;
	}

	public function setLegalStatus(?LegalStatus $legalStatus = null): self {
		$this->legalStatus = $legalStatus;

		return $this;
	}

	#[ORM\PrePersist]
	public function prePersist(): void {
		if ($this->jobOffers->count()) {
			/** @var JobOffer $job */
			foreach ($this->jobOffers as $job) {
				$job->setCompany($this);
			}
		}

		if ($this->publicities->count()) {
			/** @var Publicity $publicity */
			foreach ($this->publicities as $publicity) {
				$publicity->setCompany($this);
			}
		}
	}

	public function addJob(JobOffer $job): self {
		$this->jobOffers[] = $job;

		return $this;
	}

	public function removeJob(JobOffer $job): void {
		$this->jobOffers->removeElement($job);
	}

	public function addJobOffer(JobOffer $jobOffer): self {
		$this->jobOffers->add($jobOffer);

		return $this;
	}

	public function removeJobOffer(JobOffer $jobOffer): void {
		$this->jobOffers->removeElement($jobOffer);
	}

	public function getJobOffers(): Collection {
		return $this->jobOffers;
	}

	public function addPublicity(Publicity $publicity): self {
		$this->publicities->add($publicity);

		return $this;
	}

	public function removePublicity(Publicity $publicity): void {
		$this->publicities->removeElement($publicity);
	}

	public function getPublicities(): Collection {
		return $this->publicities;
	}

	public function getContactCompany(): ?ContactCompany {
		return $this->contactCompany;
	}

	public function setContactCompany(?ContactCompany $contactCompany) {
		$this->contactCompany = $contactCompany;
	}

	public function getSalaries(): Collection {
		return $this->salaries;
	}

	public function setSalaries(Collection $salaries): void {
		$this->salaries = $salaries;
	}

	public function getApprentices(): Collection {
		return $this->apprentices;
	}

	public function setApprentices(Collection $apprentices): void {
		$this->apprentices = $apprentices;
	}


	public function addSalary(PersonDegree $salary): self {
		$this->salaries->add($salary);

		return $this;
	}

	public function removeSalary(PersonDegree $salary): bool {
		return $this->salaries->removeElement($salary);
	}

	public function addApprentice(Apprentice $apprentice): self {
		$this->apprentices->add($apprentice);

		return $this;
	}

	public function removeApprentice(Apprentice $apprentice): bool {
		return $this->apprentices->removeElement($apprentice);
	}

	public function addSatisfactionCompany(SatisfactionCompany $satisfaction): self {
		$this->satisfactionCompanies->add($satisfaction);

		return $this;
	}

	public function removeSatisfactionCompany(SatisfactionCompany $satisfaction): bool {
		return $this->satisfactionCompanies->removeElement($satisfaction);
	}

	public function getSatisfactionCompanies(): Collection {
		return $this->satisfactionCompanies;
	}

	public function addSocialNetwork(SocialNetwork $socialNetwork) {
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

	public function __toString(): string {
		return sprintf('%s , %s , %s , %s',
			ucfirst($this->getName()),
			ucfirst($this->getCity()->getRegion()),
			ucfirst($this->getCity()->getName()),
			ucfirst($this->getPhoneStandard())
		);
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

	public function getCountry(): ?Country {
		return $this->country;
	}

	public function setUser(?User $user = null): self {
		$this->user = $user;

		return $this;
	}

	public function getUser(): ?User {
		return $this->user;
	}

	public function isAgreeRgpd(): bool {
		return $this->agreeRgpd;
	}

	public function setAgreeRgpd(bool $agreeRgpd): self {
		$this->agreeRgpd = $agreeRgpd;
		return $this;
	}

	public function getLocationFixed(): bool {
		return $this->locationFixed;
	}

	public function getAgreeRgpd(): bool {
		return $this->agreeRgpd;
	}

	public function addSchool(School $school): self {
		$this->schools->add($school);

		return $this;
	}

	public function removeSchool(School $school): void {
		$this->schools->removeElement($school);
	}

	public function getSchools(): Collection {
		return $this->schools;
	}

	public function isLocationMode(): ?bool {
		return $this->locationMode;
	}

	public function setLocationMode(?bool $locationMode): void {
		$this->locationMode = $locationMode;
	}

	public function getLogo(): ?Image {
		return $this->logo;
	}

	public function setLogo(?Image $logo): void {
		$this->logo = $logo;
	}

	public function isUnlocked(): ?bool {
		return $this->unlocked;
	}

	public function setUnlocked(?bool $unlocked): void {
		$this->unlocked = $unlocked;
	}

}
