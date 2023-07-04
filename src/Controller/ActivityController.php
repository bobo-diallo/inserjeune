<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Form\ActivityType;
use App\Repository\ActivityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/activity')]
#[IsGranted('ROLE_ADMIN')]
class ActivityController extends AbstractController {
	private EntityManagerInterface $manager;
	private ActivityRepository $activityRepository;
	private TranslatorInterface $translator;

	public function __construct(
		EntityManagerInterface $manager,
		ActivityRepository $activityRepository,
		TranslatorInterface $translator
	) {
		$this->manager = $manager;
		$this->activityRepository = $activityRepository;
		$this->translator = $translator;
	}

	#[Route(path: '/', name: 'activity_index', methods: ['GET'])]
	public function indexAction(): Response {
		$activities = $this->activityRepository->findAll();

		return $this->render('activity/index.html.twig', ['activities' => $activities]);
	}

	#[Route(path: '/new', name: 'activity_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$activity = new Activity();
		$form = $this->createForm(ActivityType::class, $activity);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->manager->persist($activity);
			$this->manager->flush();

			return $this->redirectToRoute('activity_show', ['id' => $activity->getId()]);
		}

		return $this->render('activity/new.html.twig', [
			'activity' => $activity,
			'form' => $form->createView()
		]);
	}

	#[Route(path: '/{id}', name: 'activity_show', methods: ['GET'])]
	public function showAction(Activity $activity): Response {
		return $this->render('activity/show.html.twig', [
			'activity' => $activity,
		]);
	}

	#[Route(path: '/{id}/edit', name: 'activity_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request, Activity $activity): RedirectResponse|Response {
		$editForm = $this->createForm(ActivityType::class, $activity);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$this->manager->flush();

			return $this->redirectToRoute('activity_edit', ['id' => $activity->getId()]);
		}

		return $this->render('activity/edit.html.twig', [
			'activity' => $activity,
			'edit_form' => $editForm->createView()
		]);
	}

	#[Route(path: '/delete/{id}', name: 'activity_delete', methods: ['GET'])]
	public function deleteAction(Request $request, ?Activity $activity): RedirectResponse {
		if (array_key_exists('HTTP_REFERER', $request->server->all())) {
			if ($activity) {
				$this->manager->remove($activity);
				$this->manager->flush();
				$this->addFlash('success', $this->translator->trans('flashbag.the_deletion_is_done_successfully'));
			} else {
				$this->addFlash('warning', $this->translator->trans('flashbag.unable_to_delete_the_job'));
				return $this->redirect($request->server->all()['HTTP_REFERER']);
			}
		}
		return $this->redirectToRoute('activity_index');
	}
}
