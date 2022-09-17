<?php

namespace App\Entity;

use App\Repository\ApprenticeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'apprentice')]
#[ORM\Entity(repositoryClass: ApprenticeRepository::class)]
class Apprentice {
	use Person;

	#[ORM\Column(name: 'monthly_salary', type: 'integer')]
	private int $monthlySalary;

	#[ORM\ManyToOne(targetEntity: Activity::class)]
	private Activity $activity;

	#[ORM\ManyToOne(targetEntity: Contract::class)]
	private string $contract;

	#[ORM\ManyToOne(targetEntity: Company::class)]
	private string $company;

	public function setMonthlySalary(int $monthlySalary): self {
		$this->monthlySalary = $monthlySalary;

		return $this;
	}

	public function getMonthlySalary(): int {
		return $this->monthlySalary;
	}

	public function setActivity(Activity $activity): self {
		$this->activity = $activity;

		return $this;
	}

	public function getActivity(): Activity {
		return $this->activity;
	}

	public function setContractType(Contract $contract): self {
		$this->contract = $contract;

		return $this;
	}

	public function getContract(): string {
		return $this->contract;
	}

	public function getCompany(): string {
		return $this->company;
	}

	public function setCompany($company): self {
		$this->company = $company;

		return $this;
	}
}
