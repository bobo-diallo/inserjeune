<?php

namespace App\Entity;

use App\Repository\JobOfferRepository;
use App\Tools\Utils;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Collection;
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
	private ?int $id = null;

	#[ORM\Column(name: 'title', type: 'string', length: 255)]
	#[Assert\NotBlank]
	private ?string $title;

	#[ORM\Column(name: 'description', type: 'text', nullable: true)]
	#[Assert\Length(min: '20', max: 2000)]
	private ?string $description;

	#[ORM\Column(name: 'created_date', type: 'datetime', nullable: true)]
	private ?\DateTime $createdDate = null;

	#[ORM\Column(name: 'closed_date', type: 'datetime', nullable: true)]
	private ?\DateTime $closedDate = null;

	#[ORM\Column(name: 'posted_contact', type: 'string', length: 255, nullable: true)]
	private ?string $postedContact;

	#[ORM\Column(name: 'posted_phone', type: 'string', length: 255, nullable: true)]
	private ?string $postedPhone;

	#[ORM\Column(name: 'posted_email', type: 'string', length: 255)]
	#[Assert\Email]
	private ?string $postedEmail;

	#[ORM\Column(name: 'cover_letter', type: 'string', nullable: true)]
	private ?string $coverLetter;

	#[ORM\Column(name: 'other_activity', type: 'string', length: 255, nullable: true)]
	private ?string $otherActivity;

	#[ORM\Column(name: 'filename', type: 'string', length: 255, nullable: true)]
	private ?string $filename = null;

	#[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'jobOffers')]
	#[ORM\JoinColumn(name: 'id_company', referencedColumnName: 'id')]
	private ?Company $company = null;

	#[ORM\ManyToOne(targetEntity: School::class, inversedBy: 'jobOffers')]
	#[ORM\JoinColumn(name: 'id_school', referencedColumnName: 'id')]
	private ?School $school = null;

	#[ORM\ManyToOne(targetEntity: SectorArea::class)]
	#[ORM\JoinColumn(name: 'id_sectorArea', referencedColumnName: 'id')]
	private ?SectorArea $sectorArea = null;

	#[ORM\ManyToOne(targetEntity: Activity::class)]
	#[ORM\JoinColumn(nullable: true)]
	private ?Activity $activity = null;

	#[ORM\ManyToOne(targetEntity: Contract::class)]
	#[ORM\JoinColumn(name: 'lasted_contract_id', referencedColumnName: 'id', nullable: true)]
	private ?Contract $contract = null;

	#[ORM\ManyToOne(targetEntity: City::class)]
	#[ORM\JoinColumn(name: 'id_city', referencedColumnName: 'id')]
	private ?City $city = null;

	#[ORM\Column(name: 'other_city', type: 'string', length: 255, nullable: true)]
	private ?string $otherCity;

	#[ORM\ManyToOne(targetEntity: Region::class)]
	#[ORM\JoinColumn(name: 'id_region', referencedColumnName: 'id')]
	private ?Region $region = null;

	#[ORM\ManyToOne(targetEntity: Country::class)]
	#[ORM\JoinColumn(name: 'id_country', referencedColumnName: 'id')]
	private ?Country $country = null;

	#[ORM\Column(name: 'updated_date', type: 'datetime', nullable: true)]
	private ?\DateTime $updatedDate;

	#[ORM\Column(name: 'is_view', type: 'boolean')]
	private bool $isView = false;

	#[ORM\Column(name: 'candidate_profile', type: 'text', nullable: true)]
	#[Assert\Length(min: '20', max: 2000)]
	private ?string $candidateProfile;

    #[ORM\Column(name: 'candidate_sended', type: 'text', nullable: true)]
    private ?string $candidateSended ;

	public function __construct() {
		$this->createdDate = new \DateTime();
		$this->updatedDate = new \DateTime();
		// Closed after 3 months
		$this->closedDate = (new \DateTime())->add(new \DateInterval('P3M'));
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getTitle(): ?string {
		return $this->title;
	}

	public function setTitle(?string $title): self {
		$this->title = $title;

		return $this;
	}

	public function getCreatedDate(): ?\DateTime {
		return $this->createdDate;
	}

	public function setCreatedDate(?\DateTime $createdDate): self {
		$this->createdDate = $createdDate;

		return $this;
	}

	public function getClosedDate(): ?string {
		return ($this->closedDate) ? $this->closedDate->format(Utils::FORMAT_FR): null;
	}

	public function setClosedDate(?string $closedDate): self {
		if ($closedDate) {
			$this->closedDate = \DateTime::createFromFormat(Utils::FORMAT_FR, $closedDate);
		}

		return $this;
	}

	public function getCompany(): ?Company {
		return $this->company;
	}

	public function setCompany(?Company $company = null): self {
		$this->company = $company;

		return $this;
	}

	public function getSchool(): ?School {
		return $this->school;
	}

	public function setSchool(?School $school = null): self {
		$this->school = $school;

		return $this;
	}

	public function getDescription(): ?string {
		return $this->description;
	}

	public function setDescription(?string $description): self {
		$this->description = $description;

		return $this;
	}

	public function getPostedEmail(): ?string {
		return $this->postedEmail;
	}

	public function setPostedEmail(?string $postedEmail): self {
		$this->postedEmail = $postedEmail;

		return $this;
	}

	public function getPostedContact(): ?string {
		return $this->postedContact;
	}

	public function setPostedContact(?string $postedContact): self {
		$this->postedContact = $postedContact;
		return $this;
	}

	public function getPostedPhone(): ?string {
		return $this->postedPhone;
	}

	public function setPostedPhone(?string $postedPhone): self {
		$this->postedPhone = $postedPhone;
		return $this;
	}

	public function getCoverLetter(): ?string {
		return $this->coverLetter;
	}

	public function setCoverLetter(?string $coverLetter): self {
		$this->coverLetter = $coverLetter;
		return $this;
	}

	public function getSectorArea(): ?SectorArea {
		return $this->sectorArea;
	}

	public function setSectorArea(?SectorArea $sectorArea): self {
		$this->sectorArea = $sectorArea;
		return $this;
	}

	public function getActivity(): ?Activity {
		return $this->activity;
	}

	public function setActivity(?Activity $activity): self {
		$this->activity = $activity;
		return $this;
	}

	public function getOtherActivity(): ?string {
		return $this->otherActivity;
	}

	public function setOtherActivity(?string $otherActivity): self {
		$this->otherActivity = $otherActivity;
		return $this;
	}

	public function getContract(): ?Contract {
		return $this->contract;
	}

	public function setContract(?Contract $contract): self {
		$this->contract = $contract;
		return $this;
	}

	public function getOtherCity(): ?string {
		return $this->otherCity;
	}

	public function setOtherCity(?string $otherCity): self {
		$this->otherCity = $otherCity;
		return $this;
	}

	public function getCity(): ?City {
		return $this->city;
	}

	public function setCity(?City $city): self {
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

	public function setFilename(?string $filename): self {
		$this->filename = $filename;

		return $this;
	}

	public function getFilename(): ?string {
		return $this->filename;
	}

	public function getUpdatedDate(): ?\DateTime {
		return $this->updatedDate ?: $this->createdDate;
	}

	public function setUpdatedDate(?\DateTime $updatedDate): self {
		$this->updatedDate = $updatedDate;
		return $this;
	}

	public function getCandidateProfile(): ?string {
		return $this->candidateProfile;
	}

	public function setCandidateProfile(?string $candidateProfile): self {
		$this->candidateProfile = $candidateProfile;
		return $this;
	}

	public function isView(): bool {
		return $this->isView;
	}

	public function setIsView(bool $isView): self {
		$this->isView = $isView;

		return $this;
	}

    /**
     * @return ?string
     */
    public function getCandidateSended(): ?string
    {
        return $this->candidateSended;
    }

    /**
     * @param ?string $candidateSended
     */
    public function setCandidateSended(?string $candidateSended): void
    {
        $this->candidateSended = $candidateSended;
    }

    /**
     * @param int $candidateSendedId
     */
    public function addCandidateSended(int $candidateSendedId): void
    {
        if(!$this->candidateSended) {
            $this->candidateSended = $candidateSendedId;
        } else if (!in_array($candidateSendedId, explode(',', $this->candidateSended))) {
            $this->candidateSended .=  ',' .  $candidateSendedId ;
        }
    }

    public  function isCandidateSended(int $candidateSendedId): bool
    {
        return in_array($candidateSendedId, explode($this->candidateSended, ','));
    }
}
