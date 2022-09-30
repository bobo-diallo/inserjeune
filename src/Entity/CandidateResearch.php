<?php

namespace App\Entity;

use App\Repository\CandidateResearchRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'candidate_research')]
#[ORM\Entity(repositoryClass: CandidateResearchRepository::class)]
#[ORM\HasLifecycleCallbacks]
class CandidateResearch extends PersonDegree {

	#[ORM\Column(name: "previousEndedContract", type: "date")]
	private \DateTime $previousEndedContract;

	#[ORM\ManyToOne(targetEntity: Company::class)]
	private Company $lastedCompany;

	#[ORM\OneToMany(mappedBy: 'candidateResearch', targetEntity: SatisfactionSearch::class, cascade: ['persist', 'remove'])]
	private Collection $satisfactions;

	public function __construct() {
		$this->satisfactions = new ArrayCollection();
	}

	public function setPreviousEndedContract(?\DateTime $previousEndedContract): self {
		$this->previousEndedContract = $previousEndedContract;

		return $this;
	}

	public function getPreviousEndedContract(): \DateTime {
		return $this->previousEndedContract;
	}

	public function setLastedCompany(?Company $lastedCompany): self {
		$this->lastedCompany = $lastedCompany;

		return $this;
	}

	/**
	 * Get lastedCompany.
	 *
	 * @return Company
	 */
	public function getLastedCompany(): Company {
		return $this->lastedCompany;
	}

	#[ORM\PrePersist]
	public function prePersist(): void {
		if ($this->satisfactions->count()) {
			/** @var SatisfactionSearch $satisfaction */
			foreach ($this->satisfactions as $satisfaction) {
				$satisfaction->setCandidateResearch($this);
			}
		}
	}

	public function addSatisfaction(SatisfactionSearch $satisfaction): self {
		$this->satisfactions->add($satisfaction);

		return $this;
	}

	public function removeSatisfaction(SatisfactionSearch $satisfaction): bool {
		return $this->satisfactions->removeElement($satisfaction);
	}

	public function getSatisfactions() {
		return $this->satisfactions;
	}
}
