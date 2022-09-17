<?php

namespace App\Entity;

use App\Repository\SatisfactionCreatorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\ExclusionPolicy;

#[ORM\Table(name: 'satisfaction_creator')]
#[ORM\Entity(repositoryClass: SatisfactionCreatorRepository::class)]
class SatisfactionCreator {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id;

	#[ORM\ManyToOne(targetEntity: PersonDegree::class)]
	#[ORM\JoinColumn(nullable: false)]
	private PersonDegree $personDegree;

	#[ORM\ManyToOne(targetEntity: SectorArea::class)]
	#[ORM\JoinColumn(name: 'id_sectorArea', referencedColumnName: 'id')]
	private SectorArea $sectorArea;

	#[ORM\ManyToMany(targetEntity: Activity::class)]
	#[ORM\JoinTable(name: 'satisfaction_creator_activities')]
	#[ORM\JoinColumn(name: 'satisfaction_creator_id', referencedColumnName: 'id')]
	#[ORM\InverseJoinColumn(name: 'activity_id', referencedColumnName: 'id')]
	private Collection $activities;

	#[ORM\Column(name: 'other_activity', type: 'string', length: 255, nullable: true)]
	private string $otherActivity;

	#[ORM\Column(name: 'legal_company', type: 'boolean')]
	private bool $legalCompany = false;

	#[ORM\Column(name: 'monthly_salary', type: 'integer')]
	#[Assert\GreaterThan(0)]
	private int $monthlySalary;

	#[ORM\ManyToOne(targetEntity: Currency::class)]
	#[ORM\JoinColumn(nullable: true)]
	private Currency $currency;


	#[ORM\ManyToMany(targetEntity: JobNotFoundReason::class)]
	#[ORM\JoinTable(name: 'satisfaction_creator_reasons')]
	#[ORM\JoinColumn(name: 'satisfaction_creator_id', referencedColumnName: 'id')]
	#[ORM\InverseJoinColumn(name: 'job_not_found_reason_id', referencedColumnName: 'id')]
	private Collection $jobNotFoundReasons;

	#[ORM\Column(name: 'useful_training', type: 'boolean')]
	private bool $usefulTraining = false;


	#[ORM\Column(name: 'job_not_found_other', type: 'string', length: 255, nullable: true)]
	private string $jobNotFoundOther;

	#[ORM\Column(name: 'degree_date', type: 'string', length: 255, nullable: true)]
	private string $degreeDate;

	#[ORM\Column(name: 'created_date', type: 'datetime', nullable: true)]
	private \DateTime $createdDate;

	#[ORM\Column(name: 'updated_date', type: 'datetime', nullable: true)]
	private \DateTime $updatedDate;

	public function __construct() {
		$this->jobNotFoundReasons = new ArrayCollection();
		$this->activities = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function setPersonDegree(PersonDegree $personDegree): self {
		$this->personDegree = $personDegree;

		return $this;
	}

	public function getOtherActivity(): string {
		return $this->otherActivity;
	}

	public function getSectorArea(): SectorArea {
		return $this->sectorArea;
	}

	public function setSectorArea(SectorArea $sectorArea): self {
		$this->sectorArea = $sectorArea;
		return $this;
	}

	public function setOtherActivity(string $otherActivity): self {
		$this->otherActivity = $otherActivity;
		return $this;
	}

	public function setLegalCompany(bool $legalCompany): self {
		$this->legalCompany = $legalCompany;

		return $this;
	}

	public function getLegalCompany(): bool {
		return $this->legalCompany;
	}

	public function setMonthlySalary(int $monthlySalary): self {
		$this->monthlySalary = $monthlySalary;

		return $this;
	}

	public function getMonthlySalary(): int {
		return $this->monthlySalary;
	}

	public function getCurrency(): Currency {
		return $this->currency;
	}

	public function setCurrency(Currency $currency): self {
		$this->currency = $currency;
		return $this;
	}

	public function setUsefulTraining(bool $usefulTraining): self {
		$this->usefulTraining = $usefulTraining;

		return $this;
	}

	public function getJobNotFoundOther(): string {
		return $this->jobNotFoundOther;
	}

	public function setJobNotFoundOther(string $jobNotFoundOther): self {
		$this->jobNotFoundOther = $jobNotFoundOther;
		return $this;
	}

	public function getUsefulTraining(): bool {
		return $this->usefulTraining;
	}

	public function getDegreeDate(): string {
		return $this->degreeDate;
	}

	public function setDegreeDate(string $degreeDate): self {
		$this->degreeDate = $degreeDate;
		return $this;
	}

	public function getCreatedDate(): \DateTime {
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

	public function __toString() {
		return sprintf('%s  %s - id=%d ',
			ucfirst($this->personDegree->getFirstname()),
			ucfirst($this->personDegree->getLastname()),
			ucfirst($this->getId())
		);
	}

	public function getPersonDegree(): PersonDegree {
		return $this->personDegree;
	}


	public function addJobNotFoundReason(JobNotFoundReason $jobNotFoundReason): self {
		$this->jobNotFoundReasons->add($jobNotFoundReason);

		return $this;
	}

	public function removeJobNotFoundReason(JobNotFoundReason $jobNotFoundReason): void {
		$this->jobNotFoundReasons->removeElement($jobNotFoundReason);
	}

	public function getJobNotFoundReasons(): ArrayCollection {
		return $this->jobNotFoundReasons;
	}

	public function addActivity(Activity $activity): self {
		$this->activities->add($activity);

		return $this;
	}

	public function removeActivity(Activity $activity): void {
		$this->activities->removeElement($activity);
	}

	public function getActivities(): ArrayCollection {
		return $this->activities;
	}
}
