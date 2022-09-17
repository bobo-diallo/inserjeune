<?php

namespace App\Entity;

use App\Repository\ContactCompanyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'contact_company')]
#[ORM\Entity(repositoryClass: ContactCompanyRepository::class)]
class ContactCompany {
	use Person;

	#[ORM\Column(name: 'job', type: 'string', length: 255)]
	private string $job;

	#[ORM\ManyToOne(targetEntity: Contract::class)]
	#[ORM\JoinColumn(name: 'id_contract', referencedColumnName: 'id', nullable: false)]
	private Contract $contract;

	#[ORM\ManyToOne(targetEntity: Company::class)]
	#[ORM\JoinColumn(name: 'id_company', referencedColumnName: 'id', nullable: false)]
	private Company $company;

	public function setJob(string $job): static {
		$this->job = $job;

		return $this;
	}

	public function getJob(): string {
		return $this->job;
	}

	public function getContract(): Contract {
		return $this->contract;
	}

	public function setContract(Contract $contract): self {
		$this->contract = $contract;

		return $this;
	}

	public function getCompany(): Company {
		return $this->company;
	}

	public function setCompany(Company $company): self {
		$this->company = $company;

		return $this;
	}

}
