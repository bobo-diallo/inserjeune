<?php

namespace App\Services;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use App\Repository\JobOfferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Entity\User;

class CompanyService {
	private TokenStorageInterface $tokenStorage;

	private EntityManagerInterface $manager;
	private CompanyRepository $companyRepository;
	private RequestStack $requestStack;
	private RouterInterface $router;
	private JobOfferRepository $jobOfferRepository;

	public function __construct(
		TokenStorageInterface $tokenStorage,
		EntityManagerInterface $manager,
		CompanyRepository $companyRepository,
		RequestStack $requestStack,
		RouterInterface $router,
		JobOfferRepository $jobOfferRepository
	) {
		$this->tokenStorage = $tokenStorage;
		$this->manager = $manager;
		$this->companyRepository = $companyRepository;
		$this->requestStack = $requestStack;
		$this->router = $router;
		$this->jobOfferRepository = $jobOfferRepository;
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

	public function markJobOfferAsView(int $jobOfferId): void {
		$this->jobOfferRepository->markJobOfferView($jobOfferId, true);
	}

	public function checkUnCompletedAccountBefore(callable $executionActionController): mixed {
		$company = $this->getCompany();

		if (!$company) {
			$this->requestStack->getSession()->getFlashBag()->set('warning', 'Veuillez completer votre profil');

			return new RedirectResponse($this->router->generate('front_company_new'));
		} else {
			return $executionActionController();
		}
	}
}
