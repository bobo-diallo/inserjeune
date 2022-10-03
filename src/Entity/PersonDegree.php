<?php

namespace App\Entity;

use App\Repository\PersonDegreeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

#[ORM\Table(name: 'person_degree')]
#[ORM\Entity(repositoryClass: PersonDegreeRepository::class)]
#[ORM\HasLifecycleCallbacks]
class PersonDegree {
	use Person;

	#[ORM\Column(name: 'status', type: 'boolean', nullable: true)]
	private bool $status = true;

	#[ORM\Column(name: 'type', type: 'string', nullable: true)]
	private ?string $type;

	#[ORM\Column(name: 'agree_rgpd', type: 'boolean', nullable: true)]
	private bool $agreeRgpd = false;

	#[ORM\Column(name: 'last_degree_year', type: 'integer', nullable: true)]
	private ?int $lastDegreeYear;

	#[ORM\Column(name: 'last_degree_month', type: 'integer', nullable: true)]
	private ?int $lastDegreeMonth;

	#[ORM\Column(name: 'other_degree', type: 'string', length: 255, nullable: true)]
	private ?string $otherDegree;

	#[ORM\Column(name: 'created_date', type: 'datetime', nullable: true)]
	private ?\DateTime $createdDate;

	#[ORM\Column(name: 'updated_date', type: 'datetime', nullable: true)]
	private ?\DateTime $updatedDate = null;

	#[ORM\Column(name: 'client_updated_date', type: 'datetime', nullable: true)]
	private ?\DateTime $clientUpdateDate = null;

	#[ORM\Column(name: 'previousEndedContract', type: 'datetime', nullable: true)]
	private ?\DateTime $previousEndedContract = null;

	#[ORM\Column(name: 'registration_student_school', type: 'string', length: 255, nullable: true)]
	private ?string $registrationStudentSchool;

	#[ORM\Column(name: 'check_school', type: 'boolean', length: 255, nullable: true)]
	private ?bool $checkSchool = false;

	#[ORM\Column(name: 'other_school', type: 'string', length: 255, nullable: true)]
	private ?string $otherSchool;

	#[ORM\Column(name: 'monthly_salary', type: 'integer', nullable: true)]
	private ?int $monthlySalary;

	#[ORM\Column(name: 'other_activity', type: 'string', length: 255, nullable: true)]
	private ?string $otherActivity;

	#[ORM\Column(name: 'lastIdSatisfactionSalary', type: 'integer', nullable: true)]
	private ?int $lastIdSatisfactionSalary;

	#[ORM\Column(name: 'lastIdSatisfactionSearch', type: 'integer', nullable: true)]
	private ?int $lastIdSatisfactionSearch;

	#[ORM\Column(name: 'lastIdSatisfactionCreato', type: 'integer', nullable: true)]
	private ?int $lastIdSatisfactionCreator;

	#[ORM\ManyToOne(targetEntity: Degree::class)]
	#[ORM\JoinColumn(name: 'id_degree', referencedColumnName: 'id', nullable: true)]
	private ?Degree $degree = null;

	#[ORM\ManyToMany(targetEntity: SocialNetwork::class, cascade: ['persist', 'remove'])]
	#[ORM\JoinTable(name: 'persons_socials')]
	#[ORM\JoinColumn(name: 'person_id', referencedColumnName: 'id')]
	#[ORM\InverseJoinColumn(name: 'social_id', referencedColumnName: 'id')]
	private Collection $socialNetworks;

	#[ORM\ManyToOne(targetEntity: School::class)]
	#[ORM\JoinColumn(name: 'school_id', referencedColumnName: 'id', nullable: true)]
	private ?School $school = null;

	#[ORM\ManyToOne(targetEntity: Company::class)]
	#[ORM\JoinColumn(name: 'lasted_company_id', referencedColumnName: 'id', nullable: true)]
	private ?Company $lastedCompany = null;

	#[ORM\ManyToOne(targetEntity: Activity::class)]
	#[ORM\JoinColumn(nullable: true)]
	private ?Activity $activity = null;

	#[ORM\ManyToOne(targetEntity: SectorArea::class)]
	#[ORM\JoinColumn(name: 'id_sectorArea', referencedColumnName: 'id')]
	private ?SectorArea $sectorArea;

	#[ORM\ManyToOne(targetEntity: Contract::class)]
	#[ORM\JoinColumn(name: 'lasted_contract_id', referencedColumnName: 'id', nullable: true)]
	private ?Contract $contract;

	#[ORM\ManyToOne(targetEntity: Company::class)]
	#[ORM\JoinColumn(name: 'lasted_company_id', referencedColumnName: 'id', nullable: true)]
	private ?Company $company = null;

	#[ORM\OneToOne(inversedBy: 'personDegree', targetEntity: User::class)]
	#[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true)]
	private ?User $user = null;

	#[ORM\ManyToMany(targetEntity: InfoCreator::class, cascade: ['persist'])]
	#[ORM\JoinTable(name: 'person_degree_info_creators')]
	#[ORM\JoinColumn(name: 'person_degree_id', referencedColumnName: 'id')]
	#[ORM\InverseJoinColumn(name: 'info_creator_id', referencedColumnName: 'id')]
	private Collection $infoCreators;

	#[ORM\OneToMany(mappedBy: 'personDegree', targetEntity: SatisfactionSalary::class, cascade: ['persist', 'remove'])]
	private Collection $satisfactionSalaries;

	#[ORM\OneToMany(mappedBy: 'personDegree', targetEntity: SatisfactionSearch::class, cascade: ['persist', 'remove'])]
	private Collection $satisfactionSearches;

	#[ORM\OneToMany(mappedBy: 'personDegree', targetEntity: SatisfactionCreator::class, cascade: ['persist', 'remove'])]
	private Collection $satisfactionCreators;

	#[ORM\Column(name: 'unlocked', type: 'boolean', nullable: true)]
	private ?bool $unlocked = true;

	public function __construct() {
		$this->createdDate = new \DateTime();
		$this->socialNetworks = new ArrayCollection();
		$this->satisfactionSearches = new ArrayCollection();
		$this->satisfactionSalaries = new ArrayCollection();
		$this->satisfactionCreators = new ArrayCollection();
	}

	#[ORM\PrePersist]
	public function prePersist(): void {
		if ($this->satisfactionSearches->count()) {
			/** @var SatisfactionSearch $satisfaction */
			foreach ($this->satisfactionSearches as $satisfaction) {
				$satisfaction->setPersonDegree($this);
			}
		}
		if ($this->satisfactionSalaries->count()) {
			/** @var SatisfactionSalary $satisfaction */
			foreach ($this->satisfactionSalaries as $satisfaction) {
				$satisfaction->setPersonDegree($this);
			}
		}
		if ($this->satisfactionCreators->count()) {
			/** @var SatisfactionCreator $satisfaction */
			foreach ($this->satisfactionCreators as $satisfaction) {
				$satisfaction->setPersonDegree($this);
			}
		}
	}

	public function isStatus(): bool {
		return $this->status;
	}

	public function isAgreeRgpd(): bool {
		return $this->agreeRgpd;
	}

	public function setAgreeRgpd(bool $agreeRgpd): self {
		$this->agreeRgpd = $agreeRgpd;
		return $this;
	}

	public function setStatus(?bool $status = true) {
		if (!is_bool($status)) $status = true;
		$this->status = $status;
	}

	public function getTypeUtils(): string {
		return $this->type;
	}

	public function getType(): ?string {
		return $this->type;
	}

	public function setType(?string $type): void {
		$this->type = $type;
	}

	public function getLastDegreeYear(): ?int {
		return $this->lastDegreeYear;
	}

	public function setLastDegreeYear(?int $lastDegreeYear): void {
		$this->lastDegreeYear = $lastDegreeYear;
	}

	public function getLastDegreeMonth(): ?int {
		return $this->lastDegreeMonth;
	}

	public function setLastDegreeMonth(?int $lastDegreeMonth): self {
		$this->lastDegreeMonth = $lastDegreeMonth;
		return $this;
	}

	public function getDegree(): ?Degree {
		return $this->degree;
	}

	public function setDegree(?Degree $degree): self {
		$this->degree = $degree;
		return $this;
	}

	public function getOtherDegree(): ?string {
		return $this->otherDegree;
	}

	public function setOtherDegree(?string $otherDegree): self {
		$this->otherDegree = $otherDegree;
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

	public function getPreviousEndedContract(): ?\DateTime {
		return $this->previousEndedContract;
	}

	public function setPreviousEndedContract(?\DateTime $previousEndedContract): self {
		$this->previousEndedContract = $previousEndedContract;
		return $this;
	}

	public function getSchool(): ?School {
		return $this->school;
	}

	public function setSchool(?School $school): self {
		$this->school = $school;
		return $this;
	}

	public function getRegistrationStudentSchool(): ?string {
		return $this->registrationStudentSchool;
	}

	public function setRegistrationStudentSchool(?string $registrationStudentSchool): self {
		$this->registrationStudentSchool = $registrationStudentSchool;
		return $this;
	}

	public function isCheckSchool(): ?bool {
		return $this->checkSchool;
	}

	public function setCheckSchool(?bool $checkSchool = false): self {
		$this->checkSchool = $checkSchool;
		return $this;
	}

	public function getOtherSchool(): ?string {
		return $this->otherSchool;
	}

	public function setOtherSchool(?string $otherSchool): self {
		$this->otherSchool = $otherSchool;
		return $this;
	}

	public function getLastedCompany(): ?Company {
		return $this->lastedCompany;
	}

	public function setLastedCompany(?Company $lastedCompany): self {
		$this->lastedCompany = $lastedCompany;
		return $this;
	}

	public function getMonthlySalary(): ?int {
		return $this->monthlySalary;
	}

	public function setMonthlySalary(?int $monthlySalary): self {
		$this->monthlySalary = $monthlySalary;
		return $this;
	}

	public function getActivity(): ?Activity {
		return $this->activity;
	}

	public function setActivity(?Activity $activity): self {
		$this->activity = $activity;
		return $this;
	}

	public function getContract(): ?Contract {
		return $this->contract;
	}

	public function setContract(?Contract $contract): self {
		$this->contract = $contract;
		return $this;
	}

	public function getCompany(): ?Company {
		return $this->company;
	}

	public function setCompany(?Company $company): self {
		$this->company = $company;
		return $this;
	}

	public function getSectorArea(): ?SectorArea {
		return $this->sectorArea;
	}

	public function setSectorArea(?SectorArea $sectorArea): self {
		$this->sectorArea = $sectorArea;
		return $this;
	}

	public function getOtherActivity(): ?string {
		return $this->otherActivity;
	}

	public function setOtherActivity(?string $otherActivity): self {
		$this->otherActivity = $otherActivity;
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

	public function addInfoCreator(InfoCreator $infoCreator): self {
		$this->infoCreators->add($infoCreator);

		return $this;
	}

	public function removeInfoCreator(InfoCreator $infoCreator): bool {
		return $this->infoCreators->removeElement($infoCreator);
	}

	public function getInfoCreators(): Collection {
		return $this->infoCreators;
	}

	public function addSatisfactionSalary(SatisfactionSalary $satisfactionSalary): self {
		$this->satisfactionSalaries->add($satisfactionSalary);

		return $this;
	}

	public function removeSatisfactionSalary(SatisfactionSalary $satisfactionSalary): bool {
		return $this->satisfactionSalaries->removeElement($satisfactionSalary);
	}

	public function getSatisfactionSalaries(): Collection {
		return $this->satisfactionSalaries;
	}

	public function addSatisfactionSearch(SatisfactionSearch $satisfactionSearch): self {
		$this->satisfactionSearches->add($satisfactionSearch);

		return $this;
	}

	public function removeSatisfactionSearch(SatisfactionSearch $satisfactionSearch): bool {
		return $this->satisfactionSearches->removeElement($satisfactionSearch);
	}

	public function getSatisfactionSearches(): Collection {
		return $this->satisfactionSearches;
	}

	public function addSatisfactionCreator(SatisfactionCreator $satisfactionCreator): self {
		$this->satisfactionCreators->add($satisfactionCreator);

		return $this;
	}

	public function removeSatisfactionCreator(SatisfactionCreator $satisfactionCreator): bool {
		return $this->satisfactionCreators->removeElement($satisfactionCreator);
	}

	public function getSatisfactionCreators(): Collection {
		return $this->satisfactionCreators;
	}

	public function __toString() {
		$email = "Pas d'email";
		if ($this->getEmail()) {
			$email = ucfirst($this->getEmail());
		}
		return sprintf('%s  %s  ( %s )',
			ucfirst($this->getFirstname()),
			ucfirst($this->getLastname()),
			$email
		);
	}

	public function getStatus(): bool {
		return $this->status;
	}

	public function setUser(?User $user = null): self {
		$this->user = $user;

		return $this;
	}

	public function getUser(): ?User {
		return $this->user;
	}

	public function getLastIdSatisfactionSalary(): ?int {
		return $this->lastIdSatisfactionSalary;
	}

	public function setLastIdSatisfactionSalary(?int $lastIdSatisfactionSalary): self {
		$this->lastIdSatisfactionSalary = $lastIdSatisfactionSalary;
		return $this;
	}

	public function getLastIdSatisfactionSearch(): ?int {
		return $this->lastIdSatisfactionSearch;
	}

	public function setLastIdSatisfactionSearch(?int $lastIdSatisfactionSearch): self {
		$this->lastIdSatisfactionSearch = $lastIdSatisfactionSearch;
		return $this;
	}

	public function getLastIdSatisfactionCreator(): ?int {
		return $this->lastIdSatisfactionCreator;
	}

	public function setLastIdSatisfactionCreator(?int $lastIdSatisfactionCreator): self {
		$this->lastIdSatisfactionCreator = $lastIdSatisfactionCreator;
		return $this;
	}

	public function setUnlocked(?bool $unlocked): self {
		$this->unlocked = $unlocked;
		return $this;
	}

	public function isUnlocked(): ?bool {
		return $this->unlocked;
	}
}
