<?php

namespace App\Entity;

use App\Repository\SatisfactionCompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;

#[ORM\Table(name: "satisfaction_company")]
#[ORM\Entity(repositoryClass: SatisfactionCompanyRepository::class)]
class SatisfactionCompany {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id;

	#[ORM\ManyToOne(targetEntity: Company::class)]
	#[ORM\JoinColumn(nullable: false)]
	private Company $company;

	#[ORM\ManyToOne(targetEntity: SectorArea::class)]
	#[ORM\JoinColumn(nullable: true)]
	private SectorArea $workerSectorArea;

	#[ORM\ManyToOne(targetEntity: SectorArea::class)]
	#[ORM\JoinColumn(nullable: true)]
	private SectorArea $technicianSectorArea;

	#[ORM\ManyToMany(targetEntity: Activity::class)]
	#[ORM\JoinTable(name: 'satisfaction_company_activities')]
	#[ORM\JoinColumn(name: 'satisfaction_company_id', referencedColumnName: 'id')]
	#[ORM\InverseJoinColumn(name: 'activity_id', referencedColumnName: 'id')]
	private Collection $workerActivities;

	#[ORM\ManyToMany(targetEntity: Activity::class)]
	#[ORM\JoinTable(name: 'satisfaction_company_technicians_activities')]
	#[ORM\JoinColumn(name: 'satisfaction_company_id', referencedColumnName: 'id')]
	#[ORM\InverseJoinColumn(name: 'activity_id', referencedColumnName: 'id')]
	private Collection $technicianActivities;

	#[ORM\ManyToMany(targetEntity: OmissionReason::class)]
	#[ORM\JoinTable(name: 'satisfaction_company_reason')]
	#[ORM\JoinColumn(name: 'satisfaction_company_id', referencedColumnName: 'id')]
	#[ORM\InverseJoinColumn(name: 'omission_reason_id', referencedColumnName: 'id')]
	private Collection $omissionPeoples;

	#[ORM\Column(name: 'salary_number', type: 'string', length: 255)]
	private string $salaryNumber;

	#[ORM\Column(name: 'apprentice_number', type: 'string', length: 255)]
	private string $apprenticeNumber;

	#[ORM\Column(name: 'student_number', type: 'string', length: 255)]
	private string $studentNumber;

	#[ORM\Column(name: 'other_worker_job', type: 'string', length: 255, nullable: true)]
	private string $otherWorkerJob;

	#[ORM\Column(name: 'other_technician_job', type: 'string', length: 255, nullable: true)]
	private string $otherTechnicianJob;

	#[ORM\Column(name: 'level_skill', type: 'string', length: 255)]
	private string $levelSkill;

	#[ORM\Column(name: 'level_global_skill', type: 'integer')]
	private int $levelGlobalSkill;

	#[ORM\Column(name: 'level_technical_skill', type: 'integer')]
	private int $levelTechnicalSkill;

	#[ORM\Column(name: 'level_communication_hygiene_health_env_skill', type: 'integer')]
	private int $levelCommunicationHygieneHealthEnvSkill;

	#[ORM\Column(name: 'level_other_skill', type: 'integer', nullable: true)]
	private int $levelOtherSkill;

	#[ORM\Column(name: 'level_other_name', type: 'string', length: 255, nullable: true)]
	private string $levelOtherName;

	#[ORM\Column(name: 'other_omission_people', type: 'string', length: 255, nullable: true)]
	private string $otherOmissionPeople;

	#[ORM\Column(name: 'hiring_same_profile', type: 'boolean')]
	private bool $hiringSameProfile;

	#[ORM\Column(name: 'complete_training', type: 'boolean')]
	private bool $completeTraining;

	#[ORM\Column(name: 'complete_global_training', type: 'boolean')]
	private bool $completeGlobalTraining;

	#[ORM\Column(name: 'complete_technical_training', type: 'boolean')]
	private bool $completeTechnicalTraining;

	#[ORM\Column(name: 'complete_communication_hygiene_health_env_training', type: 'boolean')]
	private bool $completeCommunicationHygieneHealthEnvTraining;

	#[ORM\Column(name: 'complete_Other_training', type: 'boolean')]
	private bool $completeOtherTraining;

	#[ORM\Column(name: 'hiring_6_months_worker', type: 'string', length: 255)]
	private string $hiring6MonthsWorker;

	#[ORM\Column(name: 'hiring_6_months_technician', type: 'string', length: 255)]
	private string $hiring6MonthsTechnician;

	#[ORM\Column(name: 'hiring_6_months_apprentice', type: 'string', length: 255)]
	private string $hiring6MonthsApprentice;

	#[ORM\Column(name: 'hiring_6_months_student', type: 'string', length: 255)]
	private string $hiring6MonthsStudent;

	#[ORM\Column(name: 'created_date', type: 'datetime', nullable: true)]
	private \DateTime $createdDate;

	#[ORM\Column(name: 'updated_date', type: 'datetime', nullable: true)]
	private \DateTime $updatedDate;

	public function __construct() {
		$this->createdDate = new \DateTime();
		$this->updatedDate = new \DateTime();
		$this->omissionPeoples = new ArrayCollection();
		$this->workerActivities = new ArrayCollection();
		$this->technicianActivities = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function setCompany(Company $company): self {
		$this->company = $company;

		return $this;
	}

	public function getCompany(): Company {
		return $this->company;
	}

	public function setSalaryNumber(int $salaryNumber) {
		$this->salaryNumber = $salaryNumber;

		return $this;
	}

	public function getSalaryNumber(): int|string {
		return $this->salaryNumber;
	}

	public function setApprenticeNumber(int $apprenticeNumber) {
		$this->apprenticeNumber = $apprenticeNumber;

		return $this;
	}

	public function getApprenticeNumber(): int|string {
		return $this->apprenticeNumber;
	}

	public function getStudentNumber(): int|string {
		return $this->studentNumber;
	}

	public function setStudentNumber(int $studentNumber): self {
		$this->studentNumber = $studentNumber;
		return $this;
	}

	public function getOtherWorkerJob(): string {
		return $this->otherWorkerJob;
	}

	public function setOtherWorkerJob(string $otherWorkerJob): self {
		$this->otherWorkerJob = $otherWorkerJob;
		return $this;
	}

	public function getOtherTechnicianJob(): string {
		return $this->otherTechnicianJob;
	}

	public function setOtherTechnicianJob(string $otherTechnicianJob): self {
		$this->otherTechnicianJob = $otherTechnicianJob;
		return $this;
	}

	public function getLevelSkill(): int|string {
		return $this->levelSkill;
	}

	public function setLevelSkill(int $levelSkill): self {
		$this->levelSkill = $levelSkill;
		return $this;
	}

	public function getLevelGlobalSkill(): int {
		return $this->levelGlobalSkill;
	}

	public function setLevelGlobalSkill(int $levelGlobalSkill): self {
		$this->levelGlobalSkill = $levelGlobalSkill;
		return $this;
	}

	public function getLevelTechnicalSkill(): int {
		return $this->levelTechnicalSkill;
	}

	public function setLevelTechnicalSkill(int $levelTechnicalSkill): self {
		$this->levelTechnicalSkill = $levelTechnicalSkill;
		return $this;
	}

	public function getLevelCommunicationHygieneHealthEnvSkill(): int {
		return $this->levelCommunicationHygieneHealthEnvSkill;
	}

	public function setLevelCommunicationHygieneHealthEnvSkill(int $levelCommunicationHygieneHealthEnvSkill): self {
		$this->levelCommunicationHygieneHealthEnvSkill = $levelCommunicationHygieneHealthEnvSkill;
		return $this;
	}

	public function getLevelOtherSkill(): int {
		return $this->levelOtherSkill;
	}

	public function setLevelOtherSkill(int $levelOtherSkill): self {
		$this->levelOtherSkill = $levelOtherSkill;
		return $this;
	}

	public function getLevelOtherName(): string {
		return $this->levelOtherName;
	}

	public function setLevelOtherName(string $levelOtherName): self {
		$this->levelOtherName = $levelOtherName;
		return $this;
	}

	public function setOtherOmissionPeople(string $otherOmissionPeople): self {
		$this->otherOmissionPeople = $otherOmissionPeople;

		return $this;
	}

	public function getOtherOmissionPeople(): string {
		return $this->otherOmissionPeople;
	}

	public function getCreatedDate(): \DateTime|string {
		return $this->createdDate;
	}

	public function setCreatedDate(\DateTime $createdDate) {
		$this->createdDate = $createdDate;
	}

	public function getUpdatedDate(): \DateTime {
		return $this->updatedDate;
	}

	public function setUpdatedDate(\DateTime $updatedDate): self {
		$this->updatedDate = $updatedDate;
		return $this;
	}

	public function isHiringSameProfile(): bool {
		return $this->hiringSameProfile;
	}

	public function setHiringSameProfile(bool $hiringSameProfile): self {
		$this->hiringSameProfile = $hiringSameProfile;
		return $this;
	}

	public function isCompleteTraining(): bool {
		return $this->completeTraining;
	}

	public function setCompleteTraining(bool $completeTraining): self {
		$this->completeTraining = $completeTraining;
		return $this;
	}

	public function isCompleteGlobalTraining(): bool {
		return $this->completeGlobalTraining;
	}

	public function setCompleteGlobalTraining(bool $completeGlobalTraining): self {
		$this->completeGlobalTraining = $completeGlobalTraining;
		return $this;
	}

	public function isCompleteTechnicalTraining(): bool {
		return $this->completeTechnicalTraining;
	}

	public function setCompleteTechnicalTraining(bool $completeTechnicalTraining): self {
		$this->completeTechnicalTraining = $completeTechnicalTraining;
		return $this;
	}

	public function isCompleteCommunicationHygieneHealthEnvTraining(): bool {
		return $this->completeCommunicationHygieneHealthEnvTraining;
	}

	public function setCompleteCommunicationHygieneHealthEnvTraining(bool $completeCommunicationHygieneHealthEnvTraining): self {
		$this->completeCommunicationHygieneHealthEnvTraining = $completeCommunicationHygieneHealthEnvTraining;
		return $this;
	}

	public function isCompleteOtherTraining(): bool {
		return $this->completeOtherTraining;
	}

	public function setCompleteOtherTraining(bool $completeOtherTraining): self {
		$this->completeOtherTraining = $completeOtherTraining;
		return $this;
	}

	public function getHiring6MonthsWorker(): string {
		return $this->hiring6MonthsWorker;
	}

	public function setHiring6MonthsWorker(string $hiring6MonthsWorker): self {
		$this->hiring6MonthsWorker = $hiring6MonthsWorker;
		return $this;
	}

	public function getHiring6MonthsTechnician(): string {
		return $this->hiring6MonthsTechnician;
	}

	public function setHiring6MonthsTechnician(string $hiring6MonthsTechnician): self {
		$this->hiring6MonthsTechnician = $hiring6MonthsTechnician;
		return $this;
	}

	public function getHiring6MonthsApprentice(): string {
		return $this->hiring6MonthsApprentice;
	}

	public function setHiring6MonthsApprentice(string $hiring6MonthsApprentice): self {
		$this->hiring6MonthsApprentice = $hiring6MonthsApprentice;
		return $this;
	}

	public function getHiring6MonthsStudent(): string {
		return $this->hiring6MonthsStudent;
	}

	public function setHiring6MonthsStudent(string $hiring6MonthsStudent): self {
		$this->hiring6MonthsStudent = $hiring6MonthsStudent;
		return $this;
	}


	public function addOmissionPeople(OmissionReason $omissionPeople): self {
		$this->omissionPeoples->add($omissionPeople);

		return $this;
	}

	public function removeOmissionPeople(OmissionReason $omissionPeople): void {
		$this->omissionPeoples->removeElement($omissionPeople);
	}

	public function getOmissionPeoples(): ArrayCollection {
		return $this->omissionPeoples;
	}

	public function setWorkerSectorArea(SectorArea $workerSectorArea = null): self {
		$this->workerSectorArea = $workerSectorArea;

		return $this;
	}

	public function getWorkerSectorArea(): SectorArea {
		return $this->workerSectorArea;
	}

	public function setTechnicianSectorArea(SectorArea $technicianSectorArea = null): self {
		$this->technicianSectorArea = $technicianSectorArea;

		return $this;
	}

	public function getTechnicianSectorArea(): SectorArea {
		return $this->technicianSectorArea;
	}


	public function addWorkerActivity(Activity $workerActivity): self {
		$this->workerActivities[] = $workerActivity;

		return $this;
	}

	public function removeWorkerActivity(Activity $workerActivity): void {
		$this->workerActivities->removeElement($workerActivity);
	}

	public function getWorkerActivities(): ArrayCollection {
		return $this->workerActivities;
	}

	public function addTechnicianActivity(Activity $technicianActivity): self {
		$this->technicianActivities->add($technicianActivity);

		return $this;
	}

	public function removeTechnicianActivity(Activity $technicianActivity): void {
		$this->technicianActivities->removeElement($technicianActivity);
	}

	public function getTechnicianActivities(): ArrayCollection {
		return $this->technicianActivities;
	}

	public function __toString() {
		return sprintf('%s - id=%d ',
			ucfirst($this->getCompany()),
			ucfirst($this->getCompany()->getId())
		);
	}
}
