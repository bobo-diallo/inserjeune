<?php

namespace App\Services;

use App\Entity\School;
use App\Repository\SchoolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Entity\User;

class SchoolService {
	private TokenStorageInterface $tokenStorage;

	private EntityManagerInterface $manager;
	private SchoolRepository $schoolRepository;

	public function __construct(
		TokenStorageInterface $tokenStorage,
		EntityManagerInterface $manager,
		SchoolRepository $schoolRepository
	) {
		$this->tokenStorage = $tokenStorage;
		$this->manager = $manager;
		$this->schoolRepository = $schoolRepository;
	}

	public function getSchool(): ?School {
		$user = $this->tokenStorage->getToken()->getUser();
		return $this->schoolRepository->findOneBy(['user' => $user]);
	}

	public function removeRelations(User $user): void {
		$em = $this->manager;

		if ($user->getSchool()) {
			$em->remove($user->getSchool());
		}
	}
}
