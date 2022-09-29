<?php

namespace App\Entity;

use App\Repository\CompanyCreatorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'company_creator')]
#[ORM\Entity(repositoryClass: CompanyCreatorRepository::class)]
class CompanyCreator extends PersonDegree {
	#[ORM\ManyToOne(targetEntity: Company::class)]
	private Company $company;

	#[ORM\OneToMany(mappedBy: "companyCreator", targetEntity: SatisfactionCreator::class, cascade: ["persist", "remove"])]
	private Collection $satisfactions;

	#[ORM\ManyToMany(targetEntity: InfoCreator::class, cascade: ['persist'])]
	#[ORM\JoinTable(name: 'company_creators_info_creators')]
	#[ORM\JoinColumn(name: 'company_creator_id', referencedColumnName: 'id')]
	#[ORM\InverseJoinColumn(name: 'info_creator_id', referencedColumnName: 'id')]
	private Collection $infoCreators;

	public function __construct() {
		$this->satisfactions = new ArrayCollection();
		$this->infoCreators = new ArrayCollection();
	}

	public function getCompany(): Company {
		return $this->company;
	}

	public function addSatisfaction(SatisfactionCreator $satisfaction): self {
		$this->satisfactions->add($satisfaction);

		return $this;
	}

	public function removeSatisfaction(SatisfactionCreator $satisfaction): bool {
		return $this->satisfactions->removeElement($satisfaction);
	}

	public function getSatisfactions(): Collection {
		return $this->satisfactions;
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
}
