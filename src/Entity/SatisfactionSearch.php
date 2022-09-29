<?php

namespace App\Entity;

use App\Repository\SatisfactionSearchRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;

#[ORM\Table(name: 'satisfaction_search')]
#[ORM\Entity(repositoryClass: SatisfactionSearchRepository::class)]
class SatisfactionSearch {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id = null;

	#[ORM\Column(name: "registered_training", type: "boolean")]
	private bool $registeredTraining;

	#[ORM\Column(name: "formation_pursuit_last_degree", type: "boolean")]
	private bool $formationPursuitLastDegree = false;

	#[ORM\Column(name: "other_formation_degree_name", type: "string", length: 255, nullable: true)]
	private string $otherFormationDegreeName;

	#[ORM\Column(name: "other_formation_activity_name", type: "string", length: 255, nullable: true)]
	private string $otherFormationActivityName;

	#[ORM\Column(name: "search_work", type: "boolean")]
	private bool $searchWork = false;

	#[ORM\Column(name: "no_search_work_reason", type: "string", length: 255, nullable: true)]
	private string $noSearchWorkReason;

	#[ORM\Column(name: "active_volunteer", type: "boolean")]
	private bool $activeVolunteer = false;

	#[ORM\Column(name: "other_domain_volunteer", type: "string", length: 255, nullable: true)]
	private string $otherDomainVolunteer;

	#[ORM\Column(name: "job_volunteer", type: "string", length: 255, nullable: true)]
	private string $jobVolunteer;

	#[ORM\Column(name: "job_refuse", type: "boolean")]
	private bool $jobRefuse = false;

	#[ORM\Column(name: "job_from_formation", type: "boolean")]
	private bool $jobFromFormation = false;

	#[ORM\Column(name: "job_time", type: "string", length: 255)]
	private string $jobTime;

	#[ORM\ManyToOne(targetEntity: SectorArea::class)]
	private SectorArea $sectorAreaVolunteer;

	#[ORM\ManyToOne(targetEntity: Activity::class)]
	private Activity $activityVolunteer;

	#[ORM\ManyToOne(targetEntity: PersonDegree::class)]
	#[ORM\JoinColumn(nullable: false)]
	private PersonDegree $personDegree;

	#[ORM\ManyToOne(targetEntity: Degree::class)]
	#[ORM\JoinColumn(nullable: true)]
	private Degree $degree;

	#[ORM\ManyToOne(targetEntity: SectorArea::class)]
	#[ORM\JoinColumn(name: 'id_sectorArea', referencedColumnName: 'id')]
	private SectorArea $sectorArea;

	#[ORM\ManyToMany(targetEntity: Activity::class)]
	#[ORM\JoinTable(name: 'satisfaction_search_activities')]
	#[ORM\JoinColumn(name: 'satisfaction_search_id', referencedColumnName: 'id')]
	#[ORM\InverseJoinColumn(name: 'activity_id', referencedColumnName: 'id')]
	private Collection $activities;

	#[ORM\ManyToMany(targetEntity: JobNotFoundReason::class)]
	#[ORM\JoinTable(name: 'satisfaction_search_reasons')]
	#[ORM\JoinColumn(name: 'satisfaction_search_id', referencedColumnName: 'id')]
	#[ORM\InverseJoinColumn(name: 'job_not_found_reason_id', referencedColumnName: 'id')]
	private Collection $jobNotFoundReasons;

	#[ORM\Column(name: 'job_not_found_other', type: 'string', length: 255, nullable: true)]
	private string $jobNotFoundOther;

	#[ORM\Column(name: 'degree_date', type: 'string', length: 255, nullable: true)]
	private string $degreeDate;

	#[ORM\Column(name: 'created_date', type: 'datetime')]
	private \DateTime $createdDate;

	#[ORM\Column(name: 'updated_date', type: 'datetime', nullable: true)]
	private \DateTime $updatedDate;

	public function __construct() {
		$this->jobNotFoundReasons = new ArrayCollection();
		$this->activities = new ArrayCollection();
	}

	public function getPersonDegree(): PersonDegree {
		return $this->personDegree;
	}

	public function setPersonDegree(PersonDegree $personDegree): self {
		$this->personDegree = $personDegree;

		return $this;
	}

	public function getRegisteredTraining(): bool {
		return $this->registeredTraining;
	}

	public function setRegisteredTraining(bool $registeredTraining): self {
		$this->registeredTraining = $registeredTraining;

		return $this;
	}

	public function isFormationPursuitLastDegree(): bool {
		return $this->formationPursuitLastDegree;
	}

	public function getFormationPursuitLastDegree(): bool {
		return $this->formationPursuitLastDegree;
	}

	public function setFormationPursuitLastDegree(bool $formationPursuitLastDegree): self {
		$this->formationPursuitLastDegree = $formationPursuitLastDegree;
		return $this;
	}

	public function getDegree(): Degree {
		return $this->degree;
	}

	public function setDegree(Degree $degree): self {
		$this->degree = $degree;
		return $this;
	}

	public function getOtherFormationDegreeName(): string {
		return $this->otherFormationDegreeName;
	}

	public function setOtherFormationDegreeName(string $otherFormationDegreeName): self {
		$this->otherFormationDegreeName = $otherFormationDegreeName;
		return $this;
	}

	public function getOtherFormationActivityName(): string {
		return $this->otherFormationActivityName;
	}

	public function setOtherFormationActivityName(string $otherFormationActivityName): self {
		$this->otherFormationActivityName = $otherFormationActivityName;
		return $this;
	}

	public function getSearchWork(): bool {
		return $this->searchWork;
	}

	public function setSearchWork(bool $searchWork): self {
		$this->searchWork = $searchWork;

		return $this;
	}

	public function getNoSearchWorkReason(): string {
		return $this->noSearchWorkReason;
	}

	public function setNoSearchWorkReason(string $noSearchWorkReason): self {
		$this->noSearchWorkReason = $noSearchWorkReason;
		return $this;
	}

	public function getActiveVolunteer(): bool|string {
		return $this->activeVolunteer;
	}

	public function setActiveVolunteer(string $activeVolunteer): self {
		$this->activeVolunteer = $activeVolunteer;
		return $this;
	}

	public function getSectorArea(): SectorArea {
		return $this->sectorArea;
	}

	public function setSectorArea(SectorArea $sectorArea): self {
		$this->sectorArea = $sectorArea;
		return $this;
	}

	public function getOtherDomainVolunteer(): string {
		return $this->otherDomainVolunteer;
	}

	public function setOtherDomainVolunteer(string $otherDomainVolunteer): self {
		$this->otherDomainVolunteer = $otherDomainVolunteer;
		return $this;
	}

	public function getJobVolunteer(): string {
		return $this->jobVolunteer;
	}

	public function setJobVolunteer(string $jobVolunteer): self {
		$this->jobVolunteer = $jobVolunteer;
		return $this;
	}

	public function isJobRefuse(): bool {
		return $this->jobRefuse;
	}

	public function getJobRefuse(): bool {
		return $this->jobRefuse;
	}

	public function setJobRefuse(bool $jobRefuse): self {
		$this->jobRefuse = $jobRefuse;
		return $this;
	}

	public function isJobFromFormation(): bool {
		return $this->jobFromFormation;
	}

	public function getJobFromFormation(): bool {
		return $this->jobFromFormation;
	}

	public function setJobFromFormation(bool $jobFromFormation): self {
		$this->jobFromFormation = $jobFromFormation;
		return $this;
	}

	public function getJobTime(): string {
		return $this->jobTime;
	}

	public function setJobTime(int $jobTime): self {
		$this->jobTime = $jobTime;
		return $this;
	}

	public function getJobNotFoundOther(): string {
		return $this->jobNotFoundOther;
	}

	public function setJobNotFoundOther(string $jobNotFoundOther): self {
		$this->jobNotFoundOther = $jobNotFoundOther;
		return $this;
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

	public function setCreatedDate(\DateTime $createdDate): void {
		$this->createdDate = $createdDate;
	}

	public function getUpdatedDate(): \DateTime {
		return $this->updatedDate;
	}

	public function setUpdatedDate(\DateTime $updatedDate): self {
		$this->updatedDate = $updatedDate;
		return $this;
	}

	public function addActivity(Activity $activity): self {
		$this->activities->add($activity);

		return $this;
	}

	public function removeActivity(Activity $activity): void {
		$this->activities->removeElement($activity);
	}

	public function getActivities(): Collection {
		return $this->activities;
	}

	public function __toString() {
		return sprintf('%s  %s - id=%d ',
			ucfirst($this->personDegree->getFirstname()),
			ucfirst($this->personDegree->getLastname()),
			ucfirst($this->getId())
		);
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function setSectorAreaVolunteer(SectorArea $sectorAreaVolunteer = null): self {
		$this->sectorAreaVolunteer = $sectorAreaVolunteer;

		return $this;
	}

	public function getSectorAreaVolunteer(): SectorArea {
		return $this->sectorAreaVolunteer;
	}

	public function setActivityVolunteer(Activity $activityVolunteer = null): self {
		$this->activityVolunteer = $activityVolunteer;

		return $this;
	}

	public function getActivityVolunteer(): Activity {
		return $this->activityVolunteer;
	}

}
