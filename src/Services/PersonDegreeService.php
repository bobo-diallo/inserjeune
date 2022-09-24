<?php

namespace App\Services;

use App\Entity\PersonDegree;
use App\Repository\PersonDegreeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Entity\User;

class PersonDegreeService {
	private TokenStorageInterface $tokenStorage;

	private EntityManagerInterface $manager;

	private array $types;

	const TYPE_TRAINING = 'TYPE_TRAINING';
	const TYPE_EMPLOYED = 'TYPE_EMPLOYED';
	const TYPE_UNEMPLOYED = 'TYPE_UNEMPLOYED';
	const TYPE_CONTRACTOR = 'TYPE_CONTRACTOR';
	const TYPE_STUDY = 'TYPE_STUDY';
	const TYPE_SEARCH = 'TYPE_SEARCH';
	const TYPE_COMPANY = 'TYPE_COMPANY';
	private PersonDegreeRepository $personDegreeRepository;

	public function __construct(
		TokenStorageInterface $tokenStorage,
		EntityManagerInterface $manager,
		PersonDegreeRepository $personDegreeRepository
	) {
		$this->tokenStorage = $tokenStorage;
		$this->manager = $manager;
		$this->types = [
			self::TYPE_TRAINING => 'En cours de Formation Professionnelle',
			self::TYPE_EMPLOYED => 'En emploi',
			self::TYPE_CONTRACTOR => 'Entrepreneur',
			self::TYPE_SEARCH => 'En recherche d\'emploi',
			self::TYPE_STUDY => "En poursuite d'études",
			self::TYPE_UNEMPLOYED => "Sans emploi",
		];
		$this->personDegreeRepository = $personDegreeRepository;
	}

	public function getPersonDegree(): ?PersonDegree {
		$user = $this->tokenStorage->getToken()->getUser();
		return $this->personDegreeRepository->findOneBy(['user' => $user]);
	}

	public function getTypes(): array {
		return $this->types;
	}

	/**
	 * @param User $user
	 */
	public function removeRelations(User $user): void {
		$em = $this->manager;
		if ($user->getPersonDegree()) {
			$em->remove($user->getPersonDegree());
		}
		if ($user->getCompany()) {
			$em->remove($user->getCompany());
		}
		if ($user->getSchool()) {
			$em->remove($user->getSchool());
		}
	}
}
