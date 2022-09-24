<?php

namespace App\Controller;

use App\Entity\Region;
use App\Form\RegionType;
use App\Repository\RegionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/region')]
#[IsGranted('ROLE_ADMIN')]
class RegionController extends AbstractController {
	private EntityManagerInterface $em;
	private RegionRepository $regionRepository;

	public function __construct(
		EntityManagerInterface $em,
		RegionRepository       $regionRepository,
	) {
		$this->em = $em;
		$this->regionRepository = $regionRepository;
	}

	#[Route(path: '/', name: 'region_index', methods: ['GET'])]
	public function indexAction(): Response {

		return $this->render('region/index.html.twig', array(
			'regions' => $this->regionRepository->findAll(),
		));
	}

	#[Route(path: '/new', name: 'region_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$region = new Region();
		$form = $this->createForm(RegionType::class, $region);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->em->persist($region);
			$this->em->flush();

			return $this->redirectToRoute('region_show', ['id' => $region->getId()]);
		}

		return $this->render('region/new.html.twig', [
			'region' => $region,
			'form' => $form->createView(),
		]);
	}

	#[Route(path: '/{id}', name: 'region_show', methods: ['GET'])]
	public function showAction(Region $region): Response {
		return $this->render('region/show.html.twig', [
			'region' => $region
		]);
	}

	#[Route(path: '/{id}/edit', name: 'region_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request, Region $region): RedirectResponse|Response {
		$editForm = $this->createForm(RegionType::class, $region);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$this->em->flush();

			return $this->redirectToRoute('region_show', array('id' => $region->getId()));
		}

		return $this->render('region/edit.html.twig', [
			'region' => $region,
			'edit_form' => $editForm->createView(),
		]);
	}

	#[Route(path: '/delete/{id}', name: 'region_delete', methods: ['GET'])]
	public function deleteAction(Request $request, ?Region $region): RedirectResponse {
		if (array_key_exists('HTTP_REFERER', $request->server->all())) {
			if ($region) {
				$this->em->remove($region);
				$this->em->flush();
				$this->addFlash('success', 'La suppression est faite avec success');
			} else {
				$this->addFlash('warning', 'Impossible de suppression la région');
				return $this->redirect($request->server->all()['HTTP_REFERER']);
			}
		}
		return $this->redirectToRoute('region_index');
	}
}
