<?php

namespace App\Services;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use App\Repository\JobOfferRepository;
use App\Repository\SatisfactionCompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Entity\User;
use Symfony\Contracts\Translation\TranslatorInterface;

class CompanyService {
	private TokenStorageInterface $tokenStorage;

	private EntityManagerInterface $manager;
	private CompanyRepository $companyRepository;
	private RequestStack $requestStack;
	private RouterInterface $router;
	private JobOfferRepository $jobOfferRepository;
    private SatisfactionCompanyRepository $satisfactionCompanyRepository;
    private TranslatorInterface $translator;

	public function __construct(
		TokenStorageInterface $tokenStorage,
		EntityManagerInterface $manager,
		CompanyRepository $companyRepository,
		RequestStack $requestStack,
		RouterInterface $router,
		JobOfferRepository $jobOfferRepository,
        SatisfactionCompanyRepository $satisfactionCompanyRepository,
        TranslatorInterface $translator
	) {
		$this->tokenStorage = $tokenStorage;
		$this->manager = $manager;
		$this->companyRepository = $companyRepository;
		$this->requestStack = $requestStack;
		$this->router = $router;
		$this->jobOfferRepository = $jobOfferRepository;
        $this->satisfactionCompanyRepository = $satisfactionCompanyRepository;
        $this->translator = $translator;
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
			$this->requestStack->getSession()->getFlashBag()->set('warning', $this->translator->trans('flashbag.please_complete_your_profile'));

			return new RedirectResponse($this->router->generate('front_company_new'));
		} else {
			return $executionActionController();
		}
	}

    public function checkSatisfaction(Company $company): bool {
        $lastSatisfaction = $this->satisfactionCompanyRepository->getLastSatisfaction($company);
        $currentDate = new \DateTime();

        if($lastSatisfaction && $lastSatisfaction->getUpdatedDate()) {
            $remindAnnualDate = clone $lastSatisfaction->getUpdatedDate();
            $remindAnnualDate = $remindAnnualDate->add(new \DateInterval('P1Y'));

            if($currentDate >= $remindAnnualDate) {
                $this->requestStack->getSession()->getFlashBag()->set('warning', $this->translator->trans('flashbag.please_create_a_new_satisfaction_survey'));
                return false;
            } else {
                return true;
            }
        } else {
            $this->requestStack->getSession()->getFlashBag()->set('warning', $this->translator->trans('flashbag.please_respond_to_the_satisfaction_survey'));
            return false;
        }
    }
}
