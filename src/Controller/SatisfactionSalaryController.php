<?php

namespace App\Controller;

use App\Entity\SatisfactionSalary;
use App\Form\SatisfactionSalaryType;
use App\Repository\ActivityRepository;
use App\Repository\SatisfactionSalaryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/satisfactionsalary')]
#[IsGranted('ROLE_ADMIN')]
class SatisfactionSalaryController extends AbstractController {
	private EntityManagerInterface $em;
	private SatisfactionSalaryRepository $satisfactionSalaryRepository;
	private ActivityRepository $activityRepository;

	public function __construct(
		EntityManagerInterface       $em,
		SatisfactionSalaryRepository $satisfactionSalaryRepository,
		ActivityRepository           $activityRepository
	) {
		$this->em = $em;
		$this->satisfactionSalaryRepository = $satisfactionSalaryRepository;
		$this->activityRepository = $activityRepository;
	}

	#[Route(path: '/', name: 'satisfactionsalary_index', methods: ['GET'])]
	public function indexAction(): Response {
		return $this->render('satisfactionsalary/index.html.twig', array(
			'satisfactionSalaries' => $this->satisfactionSalaryRepository->findAll(),
		));
	}

	#[Route(path: '/new', name: 'satisfactionsalary_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$satisfactionSalary = new Satisfactionsalary();

		$form = $this->createForm(SatisfactionSalaryType::class, $satisfactionSalary);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$satisfactionSalary->setCreatedDate(new \DateTime());
			$satisfactionSalary->setUpdatedDate(new \DateTime());
			$this->em->persist($satisfactionSalary);
			$this->em->flush();

			return $this->redirectToRoute('satisfactionsalary_show', ['id' => $satisfactionSalary->getId()]);
		}

		return $this->render('satisfactionsalary/new.html.twig', [
			'satisfactionSalary' => $satisfactionSalary,
			'form' => $form->createView(),
			'allActivities' => $this->activityRepository->findAll(),
		]);
	}

	#[Route(path: '/{id}', name: 'satisfactionsalary_show', methods: ['GET'])]
	public function showAction(SatisfactionSalary $satisfactionSalary): Response {
		return $this->render('satisfactionsalary/show.html.twig', [
			'satisfactionSalary' => $satisfactionSalary
		]);
	}

	#[Route(path: '/{id}/edit', name: 'satisfactionsalary_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request, SatisfactionSalary $satisfactionSalary): RedirectResponse|Response {
		$createdDate = $satisfactionSalary->getCreatedDate();
		$selectedCountry = $satisfactionSalary->getPersonDegree()->getCountry();

		$editForm = $this->createForm(SatisfactionSalaryType::class, $satisfactionSalary, ['selectedCountry' => $selectedCountry->getId()]);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
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

			return $this->redirectToRoute('satisfactionsalary_show', ['id' => $satisfactionSalary->getId()]);
		}

		return $this->render('satisfactionsalary/edit.html.twig', [
			'satisfactionSalary' => $satisfactionSalary,
			'edit_form' => $editForm->createView(),
			'allActivities' => $this->activityRepository->findAll(),
			'selectedCountry' => $selectedCountry
		]);
	}

	#[Route(path: '/delete/{id}', name: 'satisfactionsalary_delete', methods: ['GET'])]
	public function deleteElementAction(Request $request, ?SatisfactionSalary $satisfactionSalary): RedirectResponse {
		if (array_key_exists('HTTP_REFERER', $request->server->all())) {
			if ($satisfactionSalary) {
				$this->em->remove($satisfactionSalary);
				$this->em->flush();
				$this->addFlash('success', 'La suppression est faite avec success');
			} else {
				$this->addFlash('warning', 'Impossible de suppression la satisfaction');
				return $this->redirect($request->server->all()['HTTP_REFERER']);
			}
		}
		return $this->redirectToRoute('satisfactionsalary_index');
	}
}
