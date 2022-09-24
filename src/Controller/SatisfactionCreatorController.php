<?php

namespace App\Controller;

use App\Entity\SatisfactionCreator;
use App\Form\SatisfactionCreatorType;
use App\Repository\ActivityRepository;
use App\Repository\SatisfactionCreatorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/satisfactioncreator')]
#[IsGranted('ROLE_ADMIN')]
class SatisfactionCreatorController extends AbstractController {
	private EntityManagerInterface $em;
	private SatisfactionCreatorRepository $satisfactionCreatorRepository;
	private ActivityRepository $activityRepository;

	public function __construct(
		EntityManagerInterface        $em,
		SatisfactionCreatorRepository $satisfactionCreatorRepository,
		ActivityRepository            $activityRepository
	) {
		$this->em = $em;
		$this->satisfactionCreatorRepository = $satisfactionCreatorRepository;
		$this->activityRepository = $activityRepository;
	}

	#[Route(path: '/', name: 'satisfactioncreator_index', methods: ['GET'])]
	public function indexAction(): Response {
		return $this->render('satisfactioncreator/index.html.twig', array(
			'satisfactionCreators' => $this->satisfactionCreatorRepository->findAll(),
		));
	}

	#[Route(path: '/new', name: 'satisfactioncreator_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$satisfactionCreator = new Satisfactioncreator();
		$form = $this->createForm(SatisfactionCreatorType::class, $satisfactionCreator);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$satisfactionCreator->setCreatedDate(new \DateTime());
			$satisfactionCreator->setUpdatedDate(new \DateTime());
			$this->em->persist($satisfactionCreator);
			$this->em->flush();

			return $this->redirectToRoute('satisfactioncreator_show', ['id' => $satisfactionCreator->getId()]);
		}

		return $this->render('satisfactioncreator/new.html.twig', [
			'satisfactionCreator' => $satisfactionCreator,
			'form' => $form->createView(),
			'allActivities' => $this->activityRepository->findAll(),
		]);
	}

	#[Route(path: '/{id}', name: 'satisfactioncreator_show', methods: ['GET'])]
	public function showAction(SatisfactionCreator $satisfactionCreator): Response {
		return $this->render('satisfactioncreator/show.html.twig', [
			'satisfactionCreator' => $satisfactionCreator
		]);
	}

	#[Route(path: '/{id}/edit', name: 'satisfactioncreator_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request, SatisfactionCreator $satisfactionCreator): RedirectResponse|Response {
		$createdDate = $satisfactionCreator->getCreatedDate();

		$editForm = $this->createForm(SatisfactionCreatorType::class, $satisfactionCreator);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {

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

			return $this->redirectToRoute('satisfactioncreator_show', ['id' => $satisfactionCreator->getId()]);
		}

		return $this->render('satisfactioncreator/edit.html.twig', [
			'satisfactionCreator' => $satisfactionCreator,
			'edit_form' => $editForm->createView(),
			'allActivities' => $this->activityRepository->findAll(),
		]);
	}

	#[Route(path: '/delete/{id}', name: 'satisfactioncreator_delete', methods: ['GET'])]
	public function deleteElementAction(Request $request, ?SatisfactionCreator $satisfactionCreator): RedirectResponse {
		if (array_key_exists('HTTP_REFERER', $request->server->all())) {
			if ($satisfactionCreator) {
				$this->em->remove($satisfactionCreator);
				$this->em->flush();
				$this->addFlash('success', 'La suppression est faite avec success');
			} else {
				$this->addFlash('warning', 'Impossible de suppression la satisfaction');
				return $this->redirect($request->server->all()['HTTP_REFERER']);
			}
		}
		return $this->redirectToRoute('satisfactioncreator_index');
	}
}
