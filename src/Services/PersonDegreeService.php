<?php

namespace App\Services;

use App\Entity\PersonDegree;
use App\Repository\PersonDegreeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Entity\User;
use Symfony\Contracts\Translation\TranslatorInterface;

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
	const TYPE_DROPOUT = 'TYPE_DROPOUT';
	const TYPE_COMPANY = 'TYPE_COMPANY';
	private PersonDegreeRepository $personDegreeRepository;
	private RequestStack $requestStack;
	private RouterInterface $router;
    private TranslatorInterface $translator;

	public function __construct(
		TokenStorageInterface $tokenStorage,
		EntityManagerInterface $manager,
		PersonDegreeRepository $personDegreeRepository,
		RequestStack $requestStack,
		RouterInterface $router,
        TranslatorInterface $translator
	) {
		$this->tokenStorage = $tokenStorage;
		$this->manager = $manager;
		$this->types = [
			self::TYPE_TRAINING => 'En cours de Formation Professionnelle',
			self::TYPE_EMPLOYED => 'En emploi',
			self::TYPE_CONTRACTOR => 'Entrepreneur',
			self::TYPE_SEARCH => "En recherche d'emploi",
			self::TYPE_STUDY => "En poursuite d'études",
			self::TYPE_UNEMPLOYED => "Sans emploi",
			self::TYPE_DROPOUT => "Décrochage",
		];
		$this->personDegreeRepository = $personDegreeRepository;
		$this->requestStack = $requestStack;
		$this->router = $router;
        $this->translator = $translator;
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

	public function checkUnCompletedAccountBefore(callable $executionActionController): mixed {
		$personDegree = $this->getPersonDegree();

		if (!$personDegree) {
			$this->requestStack->getSession()->getFlashBag()->set('warning', $this->translator->trans('flashbag.please_complete_your_profile'));

			return new RedirectResponse($this->router->generate('front_persondegree_new'));
		} else {
			return $executionActionController();
		}
	}
}
