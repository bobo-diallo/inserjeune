<?php

namespace App\Controller;

use App\Entity\SatisfactionSearch;
use App\Form\SatisfactionSearchType;
use App\Repository\ActivityRepository;
use App\Repository\SatisfactionSearchRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/satisfactionSearch')]
#[IsGranted('ROLE_ADMIN')]
class SatisfactionSearchController extends AbstractController {
	private EntityManagerInterface $em;
	private SatisfactionSearchRepository $satisfactionSearchRepository;
	private ActivityRepository $activityRepository;

	public function __construct(
		EntityManagerInterface       $em,
		SatisfactionSearchRepository $satisfactionSearchRepository,
		ActivityRepository           $activityRepository
	) {
		$this->em = $em;
		$this->satisfactionSearchRepository = $satisfactionSearchRepository;
		$this->activityRepository = $activityRepository;
	}

	#[Route(path: '/', name: 'satisfaction_search_index', methods: ['GET'])]
	public function indexAction(): Response {
		return $this->render('satisfactionSearch/index.html.twig', [
			'satisfactionSearches' => $this->satisfactionSearchRepository->findAll()
		]);
	}

	#[Route(path: '/new', name: 'satisfaction_search_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$satisfactionSearch = new SatisfactionSearch();
		$form = $this->createForm(SatisfactionSearchType::class, $satisfactionSearch);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$satisfactionSearch->setCreatedDate(new \DateTime());
			$satisfactionSearch->setUpdatedDate(new \DateTime());
			$this->em->persist($satisfactionSearch);
			$this->em->flush();

			return $this->redirectToRoute('satisfaction_search_show', ['id' => $satisfactionSearch->getId()]);
		}
		return $this->render('satisfactionSearch/new.html.twig', [
			'satisfactionSearch' => $satisfactionSearch,
			'form' => $form->createView(),
			'allActivities' => $this->activityRepository->findAll(),
		]);
	}

	#[Route(path: '/{id}/show', name: 'satisfaction_search_show', methods: ['GET'])]
	public function showAction(SatisfactionSearch $satisfactionSearch): Response {
		return $this->render('satisfactionSearch/show.html.twig', [
			'satisfactionSearch' => $satisfactionSearch
		]);
	}

	#[Route(path: '/{id}/edit', name: 'satisfaction_search_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request, SatisfactionSearch $satisfactionSearch): RedirectResponse|Response {
		$createdDate = $satisfactionSearch->getCreatedDate();
		$selectedCountry = $satisfactionSearch->getPersonDegree()->getCountry();

		$editForm = $this->createForm(SatisfactionSearchType::class, $satisfactionSearch);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
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

			return $this->redirectToRoute('satisfaction_search_show', ['id' => $satisfactionSearch->getId()]);
		}

		return $this->render('satisfactionSearch/edit.html.twig', [
			'satisfactionSearch' => $satisfactionSearch,
			'edit_form' => $editForm->createView(),
			'allActivities' => $this->activityRepository->findAll(),
			'selectedCountry' => $selectedCountry,
		]);
	}

	#[Route(path: '/delete/{id}', name: 'satisfaction_search_delete', methods: ['GET'])]
	public function deleteElementAction(Request $request, ?SatisfactionSearch $satisfactionSearch): RedirectResponse {
		if (array_key_exists('HTTP_REFERER', $request->server->all())) {
			if ($satisfactionSearch) {
				$this->em->remove($satisfactionSearch);
				$this->em->flush();
				$this->addFlash('success', 'La suppression est faite avec success');
			} else {
				$this->addFlash('warning', 'Impossible de suppression la satisfaction');
				return $this->redirect($request->server->all()['HTTP_REFERER']);
			}
		}
		return $this->redirectToRoute('satisfaction_search_index');
	}
}
