<?php

namespace App\Entity;

use App\Repository\SatisfactionSalaryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

#[ORM\Table(name: 'satisfaction_salary')]
#[ORM\Entity(repositoryClass: SatisfactionSalaryRepository::class)]
class SatisfactionSalary {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id = null;

	#[ORM\ManyToOne(targetEntity: PersonDegree::class)]
	#[ORM\JoinColumn(nullable: false)]
	private ?PersonDegree $personDegree = null;

	#[ORM\Column(name: 'monthly_salary', type: 'integer')]
	#[Assert\GreaterThan(0)]
	private int $monthlySalary;

	#[ORM\ManyToOne(targetEntity: Company::class)]
	#[ORM\JoinColumn(name: 'id_company', referencedColumnName: 'id', nullable: true)]
	private ?Company $company = null;

	#[ORM\Column(name: 'dayly_salary', type: 'integer', nullable: true)]
	private ?int $daylySalary;

	#[ORM\Column(name: 'company_name', type: 'string', length: 255)]
	private string $companyName;

	#[ORM\Column(name: 'company_city', type: 'string', length: 255)]
	private string $companyCity;

	#[ORM\Column(name: 'company_phone', type: 'string', length: 255)]
	private string $companyPhone;

	#[ORM\Column(name: 'job_name', type: 'string', length: 255, nullable: true)]
	private ?string $jobName;

	#[ORM\Column(name: 'job_status', type: 'string', length: 255, nullable: true)]
	private ?string $jobStatus;

	#[ORM\Column(name: 'job_time', type: 'datetime')]
	private ?\DateTime $jobTime = null;

	#[ORM\Column(name: 'work_hours_per_day', type: 'string', nullable: true)]
	private ?string $workHoursPerDay;

	#[ORM\Column(name: 'job_satisfied', type: 'boolean')]
	private bool $jobSatisfied = false;

	#[ORM\Column(name: 'training_satisfied', type: 'boolean')]
	private bool $trainingSatisfied = false;

	#[ORM\ManyToOne(targetEntity: Currency::class)]
	#[ORM\JoinColumn(nullable: true)]
	private ?Currency $currency = null;

	#[ORM\ManyToMany(targetEntity: JobNotFoundReason::class)]
	#[ORM\JoinTable(name: 'satisfaction_salary_reasons')]
	#[ORM\joinColumn(name: 'satisfaction_salary_id', referencedColumnName: 'id')]
	#[ORM\InverseJoinColumn(name: 'job_not_found_reason_id', referencedColumnName: 'id')]
	private Collection $jobNotFoundReasons;

	#[ORM\ManyToOne(targetEntity: Contract::class)]
	#[ORM\JoinColumn(name: 'lasted_contract_id', referencedColumnName: 'id', nullable: true)]
	private ?Contract $contract = null;

	#[ORM\Column(name: 'job_not_found_other', type: 'string', length: 255, nullable: true)]
	private ?string $jobNotFoundOther;

	#[ORM\Column(name: 'other_contract', type: 'string', length: 255, nullable: true)]
	private ?string $otherContract;

	#[ORM\Column(name: 'degree_date', type: 'string', length: 255, nullable: true)]
	private ?string $degreeDate = null;

	#[ORM\Column(name: 'created_date', type: 'datetime', nullable: true)]
	private ?\DateTime $createdDate = null;

	#[ORM\Column(name: 'updated_date', type: 'datetime', nullable: true)]
	private ?\DateTime $updatedDate = null;

	#[ORM\ManyToOne(targetEntity: SectorArea::class)]
	#[ORM\JoinColumn(name: 'id_sectorArea', referencedColumnName: 'id')]
	private SectorArea $sectorArea;

	#[ORM\ManyToOne(targetEntity: Activity::class)]
	#[ORM\JoinColumn(nullable: true)]
	private ?Activity $activity = null;

	#[ORM\Column(name: 'other_activity_name', type: 'string', length: 255, nullable: true)]
	private ?string $otherActivityName;

	public function __construct() {
		$this->jobNotFoundReasons = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function setPersonDegree(?PersonDegree $personDegree): self {
		$this->personDegree = $personDegree;

		return $this;
	}

	public function getPersonDegree(): ?PersonDegree {
		return $this->personDegree;
	}

	public function setMonthlySalary(int $monthlySalary): self {
		$this->monthlySalary = $monthlySalary;

		return $this;
	}

	public function getMonthlySalary(): int {
		return $this->monthlySalary;
	}

	public function setDaylySalary(?int $daylySalary): self {
		$this->daylySalary = $daylySalary;

		return $this;
	}

	public function getDaylySalary(): ?int {
		return $this->daylySalary;
	}

	public function getCompany(): ?Company {
		return $this->company;
	}

	public function setCompany(?Company $company): self {
		$this->company = $company;
		return $this;
	}

	public function setCompanyName(string $companyName): self {
		$this->companyName = $companyName;

		return $this;
	}

	public function getCompanyName(): string {
		return $this->companyName;
	}

	public function setCompanyCity(string $companyCity): self {
		$this->companyCity = $companyCity;

		return $this;
	}

	public function getCompanyCity(): string {
		return $this->companyCity;
	}

	public function setCompanyPhone(string $companyPhone): self {
		$this->companyPhone = $companyPhone;

		return $this;
	}

	public function getCompanyPhone(): string {
		return $this->companyPhone;
	}

	public function setJobName(?string $jobName): self {
		$this->jobName = $jobName;

		return $this;
	}

	public function getJobName(): ?string {
		return $this->jobName;
	}

	public function getJobStatus(): string {
		return $this->jobStatus;
	}

	public function setJobStatus(string $jobStatus) {
		$this->jobStatus = $jobStatus;
		return $this;
	}

	public function getJobTime(): ?string {
		return ($this->jobTime) ? $this->jobTime->format('m/d/Y') : null;
	}

	public function setJobTime(?string $jobTime): self {
		$this->jobTime = \DateTime::createFromFormat('d/m/Y', $jobTime);
		return $this;
	}

	public function setWorkHoursPerDay(?string $workHoursPerDay): self {
		$this->workHoursPerDay = $workHoursPerDay;

		return $this;
	}

	public function getWorkHoursPerDay(): ?string {
		return $this->workHoursPerDay;
	}

	public function setJobSatisfied(bool $jobSatisfied): self {
		$this->jobSatisfied = $jobSatisfied;

		return $this;
	}

	public function getJobSatisfied(): bool {
		return $this->jobSatisfied;
	}

	public function setTrainingSatisfied(bool $trainingSatisfied): self {
		$this->trainingSatisfied = $trainingSatisfied;

		return $this;
	}

	public function getTrainingSatisfied(): bool {
		return $this->trainingSatisfied;
	}

	public function getJobNotFoundOther(): ?string {
		return $this->jobNotFoundOther;
	}

	public function setJobNotFoundOther(?string $jobNotFoundOther): self {
		$this->jobNotFoundOther = $jobNotFoundOther;
		return $this;
	}

	public function getOtherContract(): ?string {
		return $this->otherContract;
	}

	public function setOtherContract(?string $otherContract): self {
		$this->otherContract = $otherContract;
		return $this;
	}

	public function getDegreeDate(): ?string {
		return $this->degreeDate;
	}

	public function setDegreeDate(?string $degreeDate): self {
		$this->degreeDate = $degreeDate;
		return $this;
	}

	public function getCreatedDate(): ?\DateTime {
		return $this->createdDate;
	}

	public function setCreatedDate(?\DateTime $createdDate): void {
		$this->createdDate = $createdDate;
	}

	public function getUpdatedDate(): ?\DateTime {
		return $this->updatedDate;
	}

	public function setUpdatedDate(?\DateTime $updatedDate): self {
		$this->updatedDate = $updatedDate;
		return $this;
	}


	public function __toString() {
		return sprintf('%s  %s - id=%d ',
			ucfirst($this->personDegree->getFirstname()),
			ucfirst($this->personDegree->getLastname()),
			ucfirst($this->getId())
		);
	}

	public function setContract(?Contract $contract = null): self {
		$this->contract = $contract;

		return $this;
	}

	public function getContract(): ?Contract {
		return $this->contract;
	}

	public function addJobNotFoundReason(JobNotFoundReason $jobNotFoundReason): self {
		$this->jobNotFoundReasons->add($jobNotFoundReason);

		return $this;
	}

	public function removeJobNotFoundReason(JobNotFoundReason $jobNotFoundReason): void {
		$this->jobNotFoundReasons->removeElement($jobNotFoundReason);
	}

	public function getJobNotFoundReasons(): Collection {
		return $this->jobNotFoundReasons;
	}

	public function getSectorArea(): SectorArea {
		return $this->sectorArea;
	}

	public function setSectorArea(SectorArea $sectorArea): self {
		$this->sectorArea = $sectorArea;
		return $this;
	}

	public function setActivity(?Activity $activity = null): self {
		$this->activity = $activity;

		return $this;
	}

	public function getActivity(): ?Activity {
		return $this->activity;
	}

	public function getOtherActivityName(): ?string {
		return $this->otherActivityName;
	}

	public function setOtherActivityName(?string $otherActivityName): self {
		$this->otherActivityName = $otherActivityName;
		return $this;
	}

	public function setCurrency(?Currency $currency = null): self {
		$this->currency = $currency;

		return $this;
	}

	public function getCurrency(): ?Currency {
		return $this->currency;
	}

}
