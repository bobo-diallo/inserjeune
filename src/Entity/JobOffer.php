<?php

namespace App\Entity;

use App\Repository\JobOfferRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use UploadBundle\Annotation\Uploadable;
use UploadBundle\Annotation\UploadableField;

#[ORM\Table(name: 'job_offer')]
#[ORM\Entity(repositoryClass: JobOfferRepository::class)]
#[Uploadable]
class JobOffer {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id;

	#[ORM\Column(name: 'title', type: 'string', length: 255)]
	#[Assert\NotBlank]
	private string $title;

	#[ORM\Column(name: 'description', type: 'text', length: 512)]
	#[Assert\Length(min: '20')]
	private string $description;

	#[ORM\Column(name: 'created_date', type: 'datetime', nullable: true)]
	private \DateTime $createdDate;

	#[ORM\Column(name: 'closed_date', type: 'datetime', nullable: true)]
	private \DateTime $closedDate;

	#[ORM\Column(name: 'posted_contact', type: 'string', length: 255, nullable: true)]
	private string $postedContact;

	#[ORM\Column(name: 'posted_phone', type: 'string', length: 255, nullable: true)]
	private string $postedPhone;

	#[ORM\Column(name: 'posted_email', type: 'string', length: 255)]
	#[Assert\Email]
	private string $postedEmail;

	#[ORM\Column(name: 'cover_letter', type: 'string', nullable: true)]
	private string $coverLetter;

	#[ORM\Column(name: 'other_activity', type: 'string', length: 255, nullable: true)]
	private string $otherActivity;

	#[ORM\Column(name: 'filename', type: 'string', length: 255, nullable: true)]
	private string $filename;

	#[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'jobOffers')]
	#[ORM\JoinColumn(name: 'id_company', referencedColumnName: 'id')]
	private Company $company;

	#[ORM\ManyToOne(targetEntity: SectorArea::class)]
	#[ORM\JoinColumn(name: 'id_sectorArea', referencedColumnName: 'id')]
	private SectorArea $sectorArea;

	#[ORM\ManyToOne(targetEntity: Activity::class)]
	#[ORM\JoinColumn(nullable: true)]
	private Activity $activity;

	#[ORM\ManyToOne(targetEntity: Contract::class)]
	#[ORM\JoinColumn(name: 'lasted_contract_id', referencedColumnName: 'id', nullable: true)]
	private Contract $contract;

	#[ORM\ManyToOne(targetEntity: Image::class)]
	#[ORM\JoinColumn(name: 'id_image', referencedColumnName: 'id', nullable: true)]
	private Image $image;

	#[ORM\ManyToOne(targetEntity: City::class)]
	#[ORM\JoinColumn(name: 'id_city', referencedColumnName: 'id')]
	private City $city;

	#[ORM\Column(name: 'other_city', type: 'string', length: 255, nullable: true)]
	private string $otherCity;

	#[ORM\ManyToOne(targetEntity: Region::class)]
	#[ORM\JoinColumn(name: 'id_region', referencedColumnName: 'id')]
	private Region $region;

	#[ORM\ManyToOne(targetEntity: Country::class)]
	#[ORM\JoinColumn(name: 'id_country', referencedColumnName: 'id')]
	private Country $country;

	#[UploadableField(filename: 'filename', path: 'uploads')]
	#[Assert\Image(maxWidth: '2000', maxHeight: '2000')]
	private ?File $file;

	public function __construct() {
		$this->createdDate = new \DateTime();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getTitle(): string {
		return $this->title;
	}

	public function setTitle(string $title): static {
		$this->title = $title;

		return $this;
	}

	public function getCreatedDate(): \DateTime {
		return $this->createdDate;
	}

	public function setCreatedDate(\DateTime $createdDate): static {
		$this->createdDate = $createdDate;

		return $this;
	}

	public function getClosedDate(): \DateTime {
		return $this->closedDate;
	}

	public function setClosedDate(\DateTime $closedDate): static {
		$this->closedDate = $closedDate;

		return $this;
	}

	public function getCompany(): Company {
		return $this->company;
	}

	public function setCompany(Company $company = null): static {
		$this->company = $company;

		return $this;
	}

	public function getDescription(): string {
		return $this->description;
	}

	public function setDescription(string $description): static {
		$this->description = $description;

		return $this;
	}

	public function getPostedEmail(): string {
		return $this->postedEmail;
	}

	public function setPostedEmail(string $postedEmail): static {
		$this->postedEmail = $postedEmail;

		return $this;
	}

	public function getPostedContact(): string {
		return $this->postedContact;
	}

	public function setPostedContact(string $postedContact): static {
		$this->postedContact = $postedContact;
		return $this;
	}

	public function getPostedPhone(): string {
		return $this->postedPhone;
	}

	public function setPostedPhone(string $postedPhone): static {
		$this->postedPhone = $postedPhone;
		return $this;
	}

	public function getCoverLetter(): string {
		return $this->coverLetter;
	}

	public function setCoverLetter(string $coverLetter): static {
		$this->coverLetter = $coverLetter;
		return $this;
	}

	public function getSectorArea(): SectorArea {
		return $this->sectorArea;
	}

	public function setSectorArea(SectorArea $sectorArea): static {
		$this->sectorArea = $sectorArea;
		return $this;
	}

	public function getActivity(): Activity {
		return $this->activity;
	}

	public function setActivity(Activity $activity): static {
		$this->activity = $activity;
		return $this;
	}

	public function getOtherActivity(): string {
		return $this->otherActivity;
	}

	public function setOtherActivity(string $otherActivity): static {
		$this->otherActivity = $otherActivity;
		return $this;
	}

	public function getContract(): Contract {
		return $this->contract;
	}

	public function setContract(Contract $contract): static {
		$this->contract = $contract;
		return $this;
	}

	public function getImage(): Image {
		return $this->image;
	}

	public function setImage(Image $image): static {
		$this->image = $image;
		return $this;
	}

	public function getOtherCity(): string {
		return $this->otherCity;
	}

	public function setOtherCity(string $otherCity): static {
		$this->otherCity = $otherCity;
		return $this;
	}

	public function getCity(): City {
		return $this->city;
	}

	public function setCity(City $city): static {
		$this->city = $city;
		return $this;
	}

	public function getRegion(): Region {
		return $this->region;
	}

	public function setRegion(Region $region): static {
		$this->region = $region;
		return $this;
	}

	public function getCountry(): Country {
		return $this->country;
	}

	public function setCountry(Country $country): static {
		$this->country = $country;
		return $this;
	}

	public function setFilename(string $filename): static {
		$this->filename = $filename;

		return $this;
	}

	public function getFilename(): string {
		return $this->filename;
	}

	public function getFile(): ?File {
		return $this->file;
	}

	public function setFile($file): void {
		$this->file = $file;
	}
}