<?php

namespace App\Controller;

use App\Entity\Degree;
use App\Form\DegreeType;
use App\Repository\DegreeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/degree')]
#[IsGranted('ROLE_ADMIN')]
class DegreeController extends AbstractController {
	private EntityManagerInterface $em;
	private DegreeRepository $degreeRepository;

	public function __construct(EntityManagerInterface $em, DegreeRepository $degreeRepository) {
		$this->em = $em;
		$this->degreeRepository = $degreeRepository;
	}

	#[Route(path: '/', name: 'degree_index', methods: ['GET'])]
	public function indexAction(): Response {
		return $this->render('degree/index.html.twig', [
			'degrees' => $this->degreeRepository->findAll()
		]);
	}

	#[Route(path: '/new', name: 'degree_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$degree = new Degree();
		$form = $this->createForm(DegreeType::class, $degree);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->em->persist($degree);
			$this->em->flush();

			return $this->redirectToRoute('degree_show', ['id' => $degree->getId()]);
		}

		return $this->render('degree/new.html.twig', [
			'degree' => $degree,
			'form' => $form->createView()
		]);
	}

	#[Route(path: '/{id}', name: 'degree_show', methods: ['GET'])]
	public function showAction(Degree $degree): Response {
		return $this->render('degree/show.html.twig', [
			'degree' => $degree,
		]);
	}

	#[Route(path: '/{id}/edit', name: 'degree_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request, Degree $degree): RedirectResponse|Response {
		$editForm = $this->createForm(DegreeType::class, $degree);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$this->em->flush();

			return $this->redirectToRoute('degree_show', ['id' => $degree->getId()]);
		}

		return $this->render('degree/edit.html.twig', [
			'degree' => $degree,
			'edit_form' => $editForm->createView(),
		]);
	}

	#[Route(path: '/delete/{id}', name: 'degree_delete', methods: ['GET'])]
	public function deleteAction(Request $request, ?Degree $degree): RedirectResponse {
		if (array_key_exists('HTTP_REFERER', $request->server->all())) {
			if ($degree) {
				$this->em->remove($degree);
				$this->em->flush();
				$this->addFlash('success', 'La suppression est faite avec success');
			} else {
				$this->addFlash('warning', 'Impossible de suppression le diplôme');
				return $this->redirect($request->server->all()['HTTP_REFERER']);
			}
		}
		return $this->redirectToRoute('degree_index');
	}
}