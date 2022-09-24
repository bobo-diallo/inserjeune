<?php

namespace App\Controller;

use App\Entity\Country;
use App\Form\CountryType;
use App\Repository\CountryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/country')]
#[IsGranted('ROLE_ADMIN')]
class CountryController extends AbstractController {
	private EntityManagerInterface $em;
	private CountryRepository $countryRepository;

	public function __construct(EntityManagerInterface $em, CountryRepository $countryRepository) {
		$this->em = $em;
		$this->countryRepository = $countryRepository;
	}

	#[Route(path: '/', name: 'country_index', methods: ['GET'])]
	public function indexAction(): Response {
		return $this->render('country/index.html.twig', [
			'countries' => $this->countryRepository->findAll()
		]);
	}

	#[Route(path: '/new', name: 'country_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$country = new Country();
		$form = $this->createForm(CountryType::class, $country);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->em->persist($country);
			$this->em->flush();

			return $this->redirectToRoute('country_show', ['id' => $country->getId()]);
		}

		return $this->render('country/new.html.twig', [
			'country' => $country,
			'form' => $form->createView(),
		]);
	}

	#[Route(path: '/{id}', name: 'country_show', methods: ['GET'])]
	public function showAction(Country $country): Response {
		return $this->render('country/show.html.twig', array(
			'country' => $country,
		));
	}

	#[Route(path: '/{id}/edit', name: 'country_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request, Country $country): RedirectResponse|Response {
		$editForm = $this->createForm(CountryType::class, $country);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$this->em->flush();
			return $this->redirectToRoute('country_show', ['id' => $country->getId()]);
		}

		return $this->render('country/edit.html.twig', [
			'country' => $country,
			'edit_form' => $editForm->createView(),
		]);
	}

	#[Route(path: '/delete/{id}', name: 'country_delete', methods: ['GET'])]
	public function deleteAction(Request $request, ?Country $country): RedirectResponse {
		if (array_key_exists('HTTP_REFERER', $request->server->all())) {
			if ($country) {
				$this->em->remove($country);
				$this->em->flush();
				$this->addFlash('success', 'La suppression est faite avec success');
			} else {
				$this->addFlash('warning', 'Impossible de suppression le pays');
				return $this->redirect($request->server->all()['HTTP_REFERER']);
			}
		}
		return $this->redirectToRoute('country_index');
	}
}
