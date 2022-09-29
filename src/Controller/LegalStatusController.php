<?php

namespace App\Controller;

use App\Entity\LegalStatus;
use App\Form\LegalStatusType;
use App\Repository\LegalStatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/legalstatus')]
#[IsGranted('ROLE_ADMIN')]
class LegalStatusController extends AbstractController {
	private EntityManagerInterface $em;
	private LegalStatusRepository $legalStatusRepository;

	public function __construct(EntityManagerInterface $em, LegalStatusRepository $legalStatusRepository) {
		$this->em = $em;
		$this->legalStatusRepository = $legalStatusRepository;
	}

	#[Route(path: '/', name: 'legalstatus_index', methods: ['GET'])]
	public function indexAction(): Response {
		return $this->render('legalstatus/index.html.twig', array(
			'legalStatuses' => $this->legalStatusRepository->findAll(),
		));
	}

	#[Route(path: '/new', name: 'legalstatus_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$legalStatus = new Legalstatus();
		$form = $this->createForm(LegalStatusType::class, $legalStatus);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->em->persist($legalStatus);
			$this->em->flush();

			return $this->redirectToRoute('legalstatus_show', ['id' => $legalStatus->getId()]);
		}

		return $this->render('legalstatus/new.html.twig', [
			'legalStatus' => $legalStatus,
			'form' => $form->createView(),
		]);
	}

	#[Route(path: '/{id}', name: 'legalstatus_show', methods: ['GET'])]
	public function showAction(LegalStatus $legalStatus): Response {
		return $this->render('legalstatus/show.html.twig', [
			'legalStatus' => $legalStatus
		]);
	}

	#[Route(path: '/{id}/edit', name: 'legalstatus_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request, LegalStatus $legalStatus): RedirectResponse|Response {
		$editForm = $this->createForm(LegalStatusType::class, $legalStatus);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$this->em->flush();

			return $this->redirectToRoute('legalstatus_show', ['id' => $legalStatus->getId()]);
		}

		return $this->render('legalstatus/edit.html.twig', [
			'legalStatus' => $legalStatus,
			'edit_form' => $editForm->createView(),
		]);
	}

	#[Route(path: '/delete/{id}', name: 'legalstatus_delete', methods: ['GET'])]
	public function deleteAction(Request $request, ?LegalStatus $legalStatus): RedirectResponse {
		if (array_key_exists('HTTP_REFERER', $request->server->all())) {
			if ($legalStatus) {
				$this->em->remove($legalStatus);
				$this->em->flush();
				$this->addFlash('success', 'La suppression est faite avec success');
			} else {
				$this->addFlash('warning', 'Impossible de suppression le statut legal');
				return $this->redirect($request->server->all()['HTTP_REFERER']);
			}
		}
		return $this->redirectToRoute('legalstatus_index');
	}
}
