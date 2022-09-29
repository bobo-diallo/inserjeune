<?php

namespace App\Controller;

use App\Entity\JobNotFoundReason;
use App\Form\JobNotFoundReasonType;
use App\Repository\JobNotFoundReasonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/jobNotFoundReason')]
#[IsGranted('ROLE_ADMIN')]
class JobNotFoundReasonController extends AbstractController {
	private EntityManagerInterface $em;
	private JobNotFoundReasonRepository $jobNotFoundReasonRepository;

	public function __construct(
		EntityManagerInterface      $em,
		JobNotFoundReasonRepository $jobNotFoundReasonRepository
	) {
		$this->em = $em;
		$this->jobNotFoundReasonRepository = $jobNotFoundReasonRepository;
	}

	#[Route(path: '/', name: 'job_not_found_reason_index', methods: ['GET'])]
	public function indexAction(): Response {
		return $this->render('jobNotFoundReason/index.html.twig', [
			'jobNotFoundReasons' => $this->jobNotFoundReasonRepository->findAll()
		]);
	}

	#[Route(path: '/new', name: 'job_not_found_reason_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$jobNotFoundReason = new JobNotFoundReason();
		$form = $this->createForm(JobNotFoundReasonType::class, $jobNotFoundReason);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->em->persist($jobNotFoundReason);
			$this->em->flush();

			return $this->redirectToRoute('job_not_found_reason_show', ['id' => $jobNotFoundReason->getId()]);
		}
		return $this->render('jobNotFoundReason/new.html.twig', [
			'jobNotFoundReason' => $jobNotFoundReason,
			'form' => $form->createView(),
		]);
	}

	#[Route(path: '/{id}', name: 'job_not_found_reason_show', methods: ['GET'])]
	public function showAction(JobNotFoundReason $jobNotFoundReason): Response {
		return $this->render('jobNotFoundReason/show.html.twig', [
			'jobNotFoundReason' => $jobNotFoundReason,
		]);
	}

	#[Route(path: '/{id}/edit', name: 'job_not_found_reason_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request, JobNotFoundReason $jobNotFoundReason): RedirectResponse|Response {
		$editForm = $this->createForm(JobNotFoundReasonType::class, $jobNotFoundReason);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$this->em->flush();

			return $this->redirectToRoute('job_not_found_reason_show', array('id' => $jobNotFoundReason->getId()));
		}

		return $this->render('jobNotFoundReason/edit.html.twig', [
			'jobNotFoundReason' => $jobNotFoundReason,
			'edit_form' => $editForm->createView()
		]);
	}

	#[Route(path: '/delete/{id}', name: 'job_not_found_reason_delete', methods: ['GET'])]
	public function deleteElementAction(Request $request, ?JobNotFoundReason $jobNotFoundReason): RedirectResponse {
		if (array_key_exists('HTTP_REFERER', $request->server->all())) {
			if ($jobNotFoundReason) {
				$this->em->remove($jobNotFoundReason);
				$this->em->flush();
				$this->addFlash('success', 'La suppression est faite avec success');
			} else {
				$this->addFlash('warning', 'Impossible de suppression la raison');
				return $this->redirect($request->server->all()['HTTP_REFERER']);
			}
		}
		return $this->redirectToRoute('job_not_found_reason_index');
	}
}

