<?php

namespace App\Controller\Front;

use App\Entity\SatisfactionCreator;
use App\Entity\SatisfactionSalary;
use App\Entity\SatisfactionSearch;
use App\Form\SatisfactionCreatorType;
use App\Form\SatisfactionSalaryType;
use App\Form\SatisfactionSearchType;
use App\Repository\SatisfactionCreatorRepository;
use App\Repository\SatisfactionSalaryRepository;
use App\Repository\SatisfactionSearchRepository;
use App\Services\PersonDegreeService;
use App\Services\SatisfactionService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Tools\Utils;

#[Route(path: 'front/persondegree')]
#[IsGranted('ROLE_DIPLOME')]
class FrontPersonDegreeSatisfactionController extends AbstractController {
	private EntityManagerInterface $em;
	private PersonDegreeService $personDegreeService;
	private SatisfactionSalaryRepository $satisfactionSalaryRepository;
	private SatisfactionSearchRepository $searchRepository;
	private SatisfactionCreatorRepository $satisfactionCreatorRepository;
	private SatisfactionService $satisfactionService;
	private RequestStack $requestStack;

	public function __construct(
		EntityManagerInterface        $em,
		PersonDegreeService           $personDegreeService,
		SatisfactionSalaryRepository  $satisfactionSalaryRepository,
		SatisfactionSearchRepository  $searchRepository,
		SatisfactionCreatorRepository $satisfactionCreatorRepository,
		SatisfactionService           $satisfactionService,
		RequestStack                  $requestStack
	) {
		$this->em = $em;
		$this->personDegreeService = $personDegreeService;
		$this->satisfactionSalaryRepository = $satisfactionSalaryRepository;
		$this->searchRepository = $searchRepository;
		$this->satisfactionCreatorRepository = $satisfactionCreatorRepository;
		$this->satisfactionService = $satisfactionService;
		$this->requestStack = $requestStack;
	}

	#[Route(path: '/satisfaction', name: 'front_persondegree_satisfaction', methods: ['GET', 'POST'])]
	public function setSatisfaction(Request $request): RedirectResponse|Response {
		return $this->personDegreeService->checkUnCompletedAccountBefore(function () use ($request) {
			$personDegree = $this->personDegreeService->getPersonDegree();
			if (!$personDegree) return $this->redirectToRoute('front_persondegree_new');

			return match ($personDegree->getTypeUtils()) {
				PersonDegreeService::TYPE_EMPLOYED => $this->redirectToRoute('front_persondegree_satisfactionsalary_index'),
				PersonDegreeService::TYPE_UNEMPLOYED => $this->redirectToRoute('front_persondegree_satisfaction_search_index'),
				PersonDegreeService::TYPE_CONTRACTOR => $this->redirectToRoute('front_persondegree_satisfactioncreator_index'),
				default => $this->redirectToRoute('front_persondegree_show'),
			};
		});
	}

	#[Route(path: '/satisfactions', name: 'front_persondegree_satisfactions_index', methods: ['GET'])]
	public function indexSatisfactionsAction(): Response {
		return $this->personDegreeService->checkUnCompletedAccountBefore(function () {
			$personDegree = $this->personDegreeService->getPersonDegree();

			$satisfactionSalaries = $this->satisfactionSalaryRepository->getSatisfactionSalaries($personDegree);
			$satisfactionSearchs = $this->searchRepository->getSatisfactionSearchs($personDegree);
			$satisfactionCreators = $this->satisfactionCreatorRepository->getSatisfactionCreators($personDegree);

			return $this->render('frontPersondegree/satisfactions.html.twig', [
				'satisfactionSalaries' => $satisfactionSalaries,
				'satisfactionSearchs' => $satisfactionSearchs,
				'satisfactionCreators' => $satisfactionCreators,
			]);
		});
	}

	#[Route(path: '/satisfactionsalary', name: 'front_persondegree_satisfactionsalary_index', methods: ['GET'])]
	public function indexSatisfactionSalaryAction(): Response {
		return $this->personDegreeService->checkUnCompletedAccountBefore(function () {
			$satisfactionSalaries = $this
				->satisfactionSalaryRepository
				->getSatisfactionSalaries($this->personDegreeService->getPersonDegree());

			return $this->render('satisfactionsalary/index.html.twig', [
				'satisfactionSalaries' => $satisfactionSalaries,
			]);
		});
	}

	#[Route(path: '/satisfactionSearch', name: 'front_persondegree_satisfaction_search_index', methods: ['GET'])]
	public function indexSatisfactionSearchAction(): Response {
		return $this->personDegreeService->checkUnCompletedAccountBefore(function () {
			$satisfactionSearches = $this->searchRepository
				->findBy(['personDegree' => $this->personDegreeService->getPersonDegree()]);

			return $this->render('satisfactionSearch/index.html.twig', array(
				'satisfactionSearches' => $satisfactionSearches,
			));
		});
	}

	#[Route(path: '/satisfactioncreator', name: 'front_persondegree_satisfactioncreator_index', methods: ['GET'])]
	public function indexSatisfactionCreatorAction(): Response {
		return $this->personDegreeService->checkUnCompletedAccountBefore(function () {
			$satisfactionCreators = $this->satisfactionCreatorRepository
				->findBy(['personDegree' => $this->personDegreeService->getPersonDegree()]);

			return $this->render('satisfactioncreator/index.html.twig', array(
				'satisfactionCreators' => $satisfactionCreators,
			));
		});
	}

	/**
	 * Permet au diplomé de remplir une satisfaction selon son type (en emploi, chercheur d'emploi ou créateur d'emploi)
	 */
	#[Route(path: '/satisfaction/new', name: 'front_persondegree_satisfaction_new', methods: ['GET', 'POST'])]
	public function newSatisfactionAction(Request $request): RedirectResponse {
		return $this->personDegreeService->checkUnCompletedAccountBefore(function () use ($request) {
			$personDegree = $this->personDegreeService->getPersonDegree();
			$typePerson = $personDegree->getType();

			switch ($typePerson) {
				case PersonDegreeService::TYPE_SEARCH:
				case PersonDegreeService::TYPE_STUDY:
				case PersonDegreeService::TYPE_UNEMPLOYED:
					{
						$redirect = 'front_persondegree_satisfaction_search';
						$repository = SatisfactionSearch::class;
					}
					break;
				case PersonDegreeService::TYPE_EMPLOYED:
					{
						$redirect = 'front_persondegree_satisfactionsalary';
						$repository = SatisfactionSalary::class;
					}
					break;
				case PersonDegreeService::TYPE_CONTRACTOR:
					{
						$redirect = 'front_persondegree_satisfactioncreator';
						$repository = SatisfactionCreator::class;
					}
					break;
				default:
				{
					$this->addFlash(Utils::FB_WARNING, "Vous devez être diplômé pour créer une enquête ");
					return $this->redirectToRoute('front_persondegree_show');
				}
			}

			// calcul de la date de creation de l'enquête:  0 mois apres l'obstension du diplôme
			$degreeDateStr = sprintf('%s-%s-01', $personDegree->getLastDegreeYear(), $personDegree->getLastDegreeMonth());
			$beginDate = $this->satisfactionService->createBeginDate(new \DateTime($degreeDateStr), 0);

			// calcul de la date de fin d'enquête sur le formulaire: 12 mois apres la date de début
			$endedUpdateDate = $this->satisfactionService->createEndedUpdateDate(new \DateTime($degreeDateStr), 12);

			// recherche la derniere satisfaction créé en fonction de la situation professionelle du diplômé
			// $satisfaction = $this->getDoctrine()->getRepository($repository)->getLastSatisfaction($personDegree);
			$satisfaction = $this->em->getRepository($repository)->getLastSatisfaction($personDegree);

			// si la date de l'obstention du diplôme est dépassée, pas d'enquête possible
			if (strtotime($this->formatUS($endedUpdateDate)) < strtotime($this->now())) {
				$this->addFlash(Utils::FB_WARNING, sprintf("Vous devez répondre à une nouvelle enquête depuis le %s", $endedUpdateDate->format(Utils::FORMAT_FR)));
				$redirect = sprintf('%s_%s', $redirect, 'new');

				// si la date est située entre $beginDate et $endedUpdateDate
			} else if (strtotime($this->formatUS($beginDate)) <= strtotime($this->now()) && strtotime($this->formatUS($endedUpdateDate)) > strtotime($this->now())) {
				// si satisfaction existante et valide : modification possible
				if ($satisfaction) {
					$satisfactionDate = $satisfaction->getCreatedDate();
					if (strtotime($this->formatUS($satisfactionDate)) >= strtotime($this->formatUS($beginDate)) && strtotime($this->formatUS($satisfactionDate)) < strtotime($this->formatUS($endedUpdateDate))) {
						$redirect = sprintf('%s_%s', $redirect, 'edit');
						$this->addFlash(Utils::FB_WARNING, sprintf("Vous pouvez modifier cette enquête jusqu'au %s", $endedUpdateDate->format(Utils::FORMAT_FR)));
						return $this->redirectToRoute($redirect, ['id' => $satisfaction->getId()]);
					}
				}

				// sinon creation d'une nouvelle
				$redirect = sprintf('%s_%s', $redirect, 'new');
			} else {
				$this->addFlash(Utils::FB_WARNING, sprintf("Vous pouvez créer une enquête à partir de %s", $beginDate->format(Utils::FORMAT_FR)));
				$redirect = "front_persondegree_show";
			}

			return $this->redirectToRoute($redirect);
		});
	}

	#[Route(path: '/satisfactionSalary/new', name: 'front_persondegree_satisfactionsalary_new', methods: ['GET', 'POST'])]
	public function newSatisfactionSalaryAction(Request $request): RedirectResponse|bool|Response {
		return $this->personDegreeService->checkUnCompletedAccountBefore(function () use ($request) {
			$checkRedirection = $this->checkRedirection($request);
			if (!$checkRedirection) {
				return $checkRedirection;
			}
			$personDegree = $this->personDegreeService->getPersonDegree();
			$selectedCountry = $personDegree->getCountry();

			$satisfactionSalary = new SatisfactionSalary();
			$satisfactionSalary->setPersonDegree($personDegree);
			$satisfactionSalary->setDegreeDate(sprintf('%02d', $personDegree->getLastDegreeMonth()) . '/' . $personDegree->getLastDegreeYear());

			$form = $this->createForm(SatisfactionSalaryType::class, $satisfactionSalary, ['selectedCountry' => $selectedCountry->getId()]);
			$form->handleRequest($request);

			if ($form->isSubmitted() && $form->isValid()) {
				$satisfactionSalary->setCreatedDate(new \DateTime());
				$satisfactionSalary->setUpdatedDate(new \DateTime());
				$satisfactionSalary->setPersonDegree($personDegree);

				$this->em->persist($satisfactionSalary);
				$this->em->flush();

				$this->notifSatisfaction();
				return $this->redirectToRoute('satisfactionsalary_show', ['id' => $satisfactionSalary->getId()]);
			}

			$this->notifSatisfaction(Utils::FB_WARNING, "Merci de compléter ce questionnaire d'insertion");

			return $this->render('satisfactionsalary/new.html.twig', [
				'satisfactionSalary' => $satisfactionSalary,
				'form' => $form->createView(),
				'personDegree' => $personDegree,
				'selectedCountry' => $selectedCountry
			]);
		});
	}

	#[Route(path: '/satisfactionSearch/new', name: 'front_persondegree_satisfaction_search_new', methods: ['GET', 'POST'])]
	public function newSatisfactionSearchAction(Request $request): RedirectResponse|bool|Response {
		return $this->personDegreeService->checkUnCompletedAccountBefore(function () use ($request) {
			$checkRedirection = $this->checkRedirection($request);
			if (!$checkRedirection) {
				return $checkRedirection;
			}

			$personDegree = $this->personDegreeService->getPersonDegree();

			$satisfactionSearch = new SatisfactionSearch();
			$satisfactionSearch->setPersonDegree($personDegree);
			$satisfactionSearch->setDegreeDate(sprintf('%02d', $personDegree->getLastDegreeMonth()) . '/' . $personDegree->getLastDegreeYear());
			$form = $this->createForm(SatisfactionSearchType::class, $satisfactionSearch);
			$form->handleRequest($request);

			if ($form->isSubmitted() && $form->isValid()) {
				$satisfactionSearch->setCreatedDate(new \DateTime());
				$satisfactionSearch->setUpdatedDate(new \DateTime());
				$satisfactionSearch->setPersonDegree($personDegree);

				$this->em->persist($satisfactionSearch);
				$this->em->flush();

				$this->notifSatisfaction();
				return $this->redirectToRoute('satisfaction_search_show', ['id' => $satisfactionSearch->getId()]);
			}
			$this->notifSatisfaction(Utils::FB_WARNING, "Merci de compléter ce questionnaire d'insertion");
			return $this->render('satisfactionSearch/new.html.twig', [
				'satisfactionSearch' => $satisfactionSearch,
				'form' => $form->createView(),
				'personDegree' => $personDegree
			]);
		});
	}

	/**
	 * Les liens actions ajouter de satisfactions seront par redirection de newSatisfactionAction()
	 * @param Request $request
	 * @return bool|RedirectResponse
	 */
	public function checkRedirection(Request $request): RedirectResponse|bool {
		if (!array_key_exists('HTTP_REFERER', $request->server->all())) {
			$this->addFlash(Utils::FB_WARNING, "Veuillez cliquez 'Enquête en cours' pour ajouter ou modifier une enquête");
			return $this->redirectToRoute('front_persondegree_show');
		}
		return true;
	}

	#[Route(path: '/satisfactionCreator/new', name: 'front_persondegree_satisfactioncreator_new', methods: ['GET', 'POST'])]
	public function newSatisfactionCreatorAction(Request $request): RedirectResponse|bool|Response {
		return $this->personDegreeService->checkUnCompletedAccountBefore(function () use ($request) {
			$checkRedirection = $this->checkRedirection($request);
			if (!$checkRedirection) {
				return $checkRedirection;
			}

			$personDegree = $this->personDegreeService->getPersonDegree();

			$satisfactionCreator = new SatisfactionCreator();
			$satisfactionCreator->setPersonDegree($personDegree);
			$satisfactionCreator->setDegreeDate(sprintf('%02d', $personDegree->getLastDegreeMonth()) . '/' . $personDegree->getLastDegreeYear());

			$form = $this->createForm(SatisfactionCreatorType::class, $satisfactionCreator);
			$form->handleRequest($request);

			if ($form->isSubmitted() && $form->isValid()) {
				$satisfactionCreator->setCreatedDate(new \DateTime());
				$satisfactionCreator->setUpdatedDate(new \DateTime());
				$satisfactionCreator->setPersonDegree($personDegree);

				$this->em->persist($satisfactionCreator);
				$this->em->flush();

				$this->notifSatisfaction();
				return $this->redirectToRoute('satisfactioncreator_show', ['id' => $satisfactionCreator->getId()]);
			}
			$this->notifSatisfaction(Utils::FB_WARNING, "Merci de compléter ce questionnaire d'insertion");
			return $this->render('satisfactioncreator/new.html.twig', [
				'satisfactionCreator' => $satisfactionCreator,
				'form' => $form->createView(),
				'personDegree' => $personDegree
			]);
		});
	}

	#[Route(path: '/satisfactioncreator/{id}/show', name: 'front_persondegree_satisfactioncreator_show', methods: ['GET', 'POST'])]
	public function showSatisfactionCreatorAction(SatisfactionCreator $satisfactionCreator): Response {
		return $this->personDegreeService->checkUnCompletedAccountBefore(function () use ($satisfactionCreator) {
			$personDegree = $this->personDegreeService->getPersonDegree();
			if (!$personDegree) return $this->redirectToRoute('front_persondegree_new');

			return $this->render('satisfactioncreator/show.html.twig', [
				'persondegree' => $personDegree,
				'satisfactionCreator' => $satisfactionCreator,
			]);
		});
	}

	#[Route(path: '/satisfactionSearch/{id}/show', name: 'front_persondegree_satisfaction_search_show', methods: ['GET'])]
	public function showSatisfactionSearchAction(SatisfactionSearch $satisfactionSearch): Response {
		return $this->personDegreeService->checkUnCompletedAccountBefore(function () use ($satisfactionSearch) {
			$personDegree = $this->personDegreeService->getPersonDegree();
			if (!$personDegree) return $this->redirectToRoute('front_persondegree_new');

			return $this->render('satisfactionSearch/show.html.twig', [
				'persondegree' => $personDegree,
				'satisfactionSearch' => $satisfactionSearch,
			]);
		});
	}

	#[Route(path: '/satisfactionSalary/{id}/show', name: 'front_persondegree_satisfactionsalary_show', methods: ['GET'])]
	public function showSatisfactionSalaryAction(SatisfactionSalary $satisfactionSalary): Response {
		return $this->personDegreeService->checkUnCompletedAccountBefore(function () use ($satisfactionSalary) {
			$personDegree = $this->personDegreeService->getPersonDegree();
			if (!$personDegree) {
				return $this->redirectToRoute('front_persondegree_new');
			}

			return $this->render('satisfactionsalary/show.html.twig', [
				'persondegree' => $personDegree,
				'satisfactionSalary' => $satisfactionSalary,
			]);
		});
	}

	#[Route(path: '/satisfactionSalary/{id}/edit', name: 'front_persondegree_satisfactionsalary_edit', methods: ['GET', 'POST'])]
	public function editSatisfactionSalaryAction(Request $request, SatisfactionSalary $satisfactionSalary): RedirectResponse|Response {
		return $this->personDegreeService->checkUnCompletedAccountBefore(function () use ($request, $satisfactionSalary) {
			$createdDate = $satisfactionSalary->getCreatedDate();
			$personDegree = $this->personDegreeService->getPersonDegree();
			$selectedCountry = $personDegree->getCountry();

			$editForm = $this->createForm(SatisfactionSalaryType::class, $satisfactionSalary, ['selectedCountry' => $selectedCountry->getId()]);
			$editForm->handleRequest($request);

			if ($editForm->isSubmitted() && $editForm->isValid()) {
				$satisfactionSalary->setPersonDegree($personDegree);

				$satisfactionSalary->setCreatedDate($createdDate);
				if ($satisfactionSalary->getCreatedDate() == null) {
					if ($satisfactionSalary->getUpdatedDate()) {
						$satisfactionSalary->setCreatedDate($satisfactionSalary->getUpdatedDate());
					} else {
						$satisfactionSalary->setCreatedDate(new \DateTime());
					}
				}

				$satisfactionSalary->setUpdatedDate(new \DateTime());
				$this->em->flush();

				return $this->redirectToRoute('front_persondegree_satisfactionsalary_show', ['id' => $satisfactionSalary->getId()]);
			}

			return $this->render('satisfactionsalary/edit.html.twig', [
				'satisfactionSalary' => $satisfactionSalary,
				'edit_form' => $editForm->createView(),
				'selectedCountry' => $selectedCountry
			]);
		});
	}

	#[Route(path: '/satisfactionCreator/{id}/edit', name: 'front_persondegree_satisfactioncreator_edit', methods: ['GET', 'POST'])]
	public function editSatisfactionCreatorAction(Request $request, SatisfactionCreator $satisfactionCreator): RedirectResponse|Response {
		return $this->personDegreeService->checkUnCompletedAccountBefore(function () use ($request, $satisfactionCreator) {
			$createdDate = $satisfactionCreator->getCreatedDate();
			$editForm = $this->createForm(SatisfactionCreatorType::class, $satisfactionCreator);
			$editForm->handleRequest($request);

			if ($editForm->isSubmitted() && $editForm->isValid()) {
				$satisfactionCreator->setPersonDegree($this->personDegreeService->getPersonDegree());

				$satisfactionCreator->setCreatedDate($createdDate);
				if ($satisfactionCreator->getCreatedDate() == null) {
					if ($satisfactionCreator->getUpdatedDate()) {
						$satisfactionCreator->setCreatedDate($satisfactionCreator->getUpdatedDate());
					} else {
						$satisfactionCreator->setCreatedDate(new \DateTime());
					}
				}

				$satisfactionCreator->setUpdatedDate(new \DateTime());
				$this->em->flush();

				return $this->redirectToRoute('front_persondegree_satisfactioncreator_show', ['id' => $satisfactionCreator->getId()]);
			}

			return $this->render('satisfactioncreator/edit.html.twig', [
				'satisfactionCreator' => $satisfactionCreator,
				'edit_form' => $editForm->createView(),
			]);
		});
	}

	#[Route(path: '/satisfactionSearch/{id}/edit', name: 'front_persondegree_satisfaction_search_edit', methods: ['GET', 'POST'])]
	public function editSatisfactionSearchAction(Request $request, SatisfactionSearch $satisfactionSearch): RedirectResponse|Response {
		return $this->personDegreeService->checkUnCompletedAccountBefore(function () use ($request, $satisfactionSearch) {
			$createdDate = $satisfactionSearch->getCreatedDate();
			$editForm = $this->createForm(SatisfactionSearchType::class, $satisfactionSearch);
			$editForm->handleRequest($request);

			if ($editForm->isSubmitted() && $editForm->isValid()) {
				$satisfactionSearch->setPersonDegree($this->personDegreeService->getPersonDegree());

				$satisfactionSearch->setCreatedDate($createdDate);
				if ($satisfactionSearch->getCreatedDate() == null) {
					if ($satisfactionSearch->getUpdatedDate()) {
						$satisfactionSearch->setCreatedDate($satisfactionSearch->getUpdatedDate());
					} else {
						$satisfactionSearch->setCreatedDate(new \DateTime());
					}
				}

				$satisfactionSearch->setUpdatedDate(new \DateTime());
				$this->em->flush();

				return $this->redirectToRoute('front_persondegree_satisfaction_search_show', ['id' => $satisfactionSearch->getId()]);
			}

			return $this->render('satisfactionSearch/edit.html.twig', [
				'satisfactionSearch' => $satisfactionSearch,
				'edit_form' => $editForm->createView(),
			]);
		});
	}

	/**
	 * @param string $type
	 * @param string $message
	 */
	private function notifSatisfaction($type = Utils::FB_SUCCESS, string $message = "Merci d'avoir répondu à l'enquête.") {
		$this->addFlash($type, $message);
	}

	/**
	 * Permet de verifier si la type de du diplomé correspond bien au questionnaire qu'il veut répondre
	 */
	private function checkTypePersonDegree(string $typePerson, string $errorMessage) {
		$personDegree = $this->personDegreeService->getPersonDegree();
		if (!$personDegree) {
			$this->notifSatisfaction(Utils::FB_WARNING, "Veuillez completer votre profil d'abord");
			return $this->redirectToRoute('front_persondegree_new');
		}
		if ($personDegree->getType() != $typePerson) {
			$this->notifSatisfaction(Utils::FB_WARNING, $errorMessage);
			return $this->redirectToRoute('front_persondegree_edit');
		}
		$request = $this->requestStack->getCurrentRequest();
		$route = $request->attributes->get('_route');

		switch ($route) {
			case 'front_persondegree_satisfactioncreator_new':
				$entityRepository = SatisfactionCreator::class;
				break;
			case 'front_persondegree_satisfactionsalary_new':
				$entityRepository = SatisfactionSalary::class;
				break;
			case 'front_persondegree_satisfaction_search_new':
				$entityRepository = SatisfactionSearch::class;
				break;
			default:
				$this->notifSatisfaction(Utils::FB_WARNING, "Aucun questionnaire à répondre");
				return $this->redirectToRoute('front_persondegree_new');
		}

		$satisfaction = $this->em
			->getRepository($entityRepository)
			->findOneBy(['personDegree' => $personDegree]);

		if ($satisfaction) {
			$this->notifSatisfaction(Utils::FB_WARNING, "Vous avez dejà répondu au questionnaire");
			return $this->redirectToRoute('front_persondegree_satisfaction');
		}

		return true;
	}

	/**
	 * @return string
	 * @throws \Exception
	 */
	private function now(): string {
		return (new \DateTime())->format(Utils::FORMAT_US);
	}

	/**
	 * Get endedUpdateDate
	 *
	 * @param \DateTime $date
	 * @return \DateTime|string|null
	 */
	public function formatUS(\DateTime $date): \DateTime|string|null {
		if ($date) {
			return $date->format(Utils::FORMAT_US);
		}
		return null;
	}
}
