<?php

namespace App\Services;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Entity\User;

class CompanyService {
	private TokenStorageInterface $tokenStorage;

	private EntityManagerInterface $manager;
	private CompanyRepository $companyRepository;

	public function __construct(
		TokenStorageInterface $tokenStorage,
		EntityManagerInterface $manager,
		CompanyRepository $companyRepository
	) {
		$this->tokenStorage = $tokenStorage;
		$this->manager = $manager;
		$this->companyRepository = $companyRepository;
	}

	public function getCompany(): ?Company {
		$user = $this->tokenStorage->getToken()->getUser();
		return $this->companyRepository->findOneBy(['user' => $user]);
	}

	/**
	 * @param User $user
	 */
	public function removeRelations(User $user): void {
		$em = $this->manager;
		if ($user->getCompany()) {
			$em->remove($user->getCompany());
		}
	}
}
