<?php

namespace App\Controller;

use App\Entity\SectorArea;
use App\Form\SectorAreaType;
use App\Repository\SectorAreaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/sectorarea')]
#[IsGranted('ROLE_ADMIN')]
class SectorAreaController extends AbstractController {
	private EntityManagerInterface $em;
	private SectorAreaRepository $sectorAreaRepository;

	public function __construct(
		EntityManagerInterface $em,
		SectorAreaRepository   $sectorAreaRepository
	) {
		$this->em = $em;
		$this->sectorAreaRepository = $sectorAreaRepository;
	}

	#[Route(path: '/', name: 'sectorarea_index', methods: ['GET'])]
	public function indexAction(): Response {
		return $this->render('sectorarea/index.html.twig', [
			'sectorAreas' => $this->sectorAreaRepository->findAll()
		]);
	}

	#[Route(path: '/new', name: 'sectorarea_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request) {
		$sectorArea = new Sectorarea();
		$form = $this->createForm(SectorAreaType::class, $sectorArea);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->em->persist($sectorArea);
			$this->em->flush();

			return $this->redirectToRoute('sectorarea_show', ['id' => $sectorArea->getId()]);
		}

		return $this->render('sectorarea/new.html.twig', [
			'sectorArea' => $sectorArea,
			'form' => $form->createView(),
		]);
	}

	#[Route(path: '/{id}', name: 'sectorarea_show', methods: ['GET'])]
	public function showAction(SectorArea $sectorArea): Response {
		return $this->render('sectorarea/show.html.twig', [
			'sectorArea' => $sectorArea
		]);
	}

	#[Route(path: '/{id}/edit', name: 'sectorarea_edit', methods: ['GET', 'POST', 'PUT'])]
	public function editAction(Request $request, SectorArea $sectorArea): RedirectResponse|Response {
		$editForm = $this->createForm(SectorAreaType::class, $sectorArea);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$this->em->flush();

			return $this->redirectToRoute('sectorarea_show', ['id' => $sectorArea->getId()]);
		}

		return $this->render('sectorarea/edit.html.twig', [
			'sectorArea' => $sectorArea,
			'edit_form' => $editForm->createView(),
		]);
	}

	#[Route(path: '/delete/{id}', name: 'sectorarea_delete', methods: ['GET'])]
	public function deleteAction(Request $request, ?SectorArea $sectorArea): RedirectResponse {
		if (array_key_exists('HTTP_REFERER', $request->server->all())) {
			if ($sectorArea) {
				$this->em->remove($sectorArea);
				$this->em->flush();
				$this->addFlash('success', 'La suppression est faite avec success');
			} else {
				$this->addFlash('warning', 'Impossible de suppression le secteur d\'activité');
				return $this->redirect($request->server->all()['HTTP_REFERER']);
			}
		}
		return $this->redirectToRoute('sectorarea_index');
	}
}
