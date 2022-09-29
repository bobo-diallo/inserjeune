<?php

namespace App\Controller;

use App\Entity\City;
use App\Form\CityType;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

#[Route(path: '/city')]
#[IsGranted('ROLE_ADMIN')]
class CityController extends AbstractController {
	private EntityManagerInterface $manager;
	private CityRepository $cityRepository;

	public function __construct(
		EntityManagerInterface $manager,
		CityRepository $cityRepository
	) {
		$this->manager = $manager;
		$this->cityRepository = $cityRepository;
	}

	#[Route('/', name: 'city_index', methods: ['GET'])]
	public function indexAction(): Response {
		$cities = $this->cityRepository->findAll();

		return $this->render('city/index.html.twig', array(
			'cities' => $cities,
		));
	}

	#[Route('/new', name: 'city_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$city = new City();
		$form = $this->createForm(CityType::class, $city);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->manager->persist($city);
			$this->manager->flush();

			return $this->redirectToRoute('city_show', array('id' => $city->getId()));
		}

		return $this->render('city/new.html.twig', array(
			'city' => $city,
			'form' => $form->createView(),
		));
	}

	#[Route('/{id}', name: 'city_show', methods: ['GET'])]
	public function showAction(City $city): Response {
		return $this->render('city/show.html.twig', array(
			'city' => $city,
		));
	}

	#[Route('/{id}/edit', name: 'city_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request, City $city): RedirectResponse|Response {
		$editForm = $this->createForm(CityType::class, $city);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$this->manager->flush();

			return $this->redirectToRoute('city_show', ['id' => $city->getId()]);
		}

		return $this->render('city/edit.html.twig', array(
			'city' => $city,
			'edit_form' => $editForm->createView(),
		));
	}


	#[Route('/delete/{id}', name: 'city_delete', methods: ['GET'])]
	public function deleteAction(Request $request, ?City $city): RedirectResponse {
		if (array_key_exists('HTTP_REFERER', $request->server->all())) {
			if ($city) {
				$this->manager->remove($city);
				$this->manager->flush();
				$this->addFlash('success', 'La suppression est faite avec success');
			} else {
				$this->addFlash('warning', 'Impossible de suppression le pays');
				return $this->redirect($request->server->all()['HTTP_REFERER']);
			}
		}
		return $this->redirectToRoute('city_index');
	}
}
