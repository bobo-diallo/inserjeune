<?php

namespace App\Controller;

use App\Entity\ReasonSatisfied;
use App\Form\ReasonSatisfiedType;
use App\Repository\ReasonSatisfiedRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/reasonsatisfied')]
#[IsGranted('ROLE_ADMIN')]
class ReasonSatisfiedController extends AbstractController {
	private EntityManagerInterface $em;
	private ReasonSatisfiedRepository $satisfiedRepository;
	private TranslatorInterface $translator;

	public function __construct(
		EntityManagerInterface    $em,
		ReasonSatisfiedRepository $satisfiedRepository,
		TranslatorInterface $translator
	) {
		$this->em = $em;
		$this->satisfiedRepository = $satisfiedRepository;
		$this->translator = $translator;
	}

	#[Route(path: '/', name: 'reasonsatisfied_index', methods: ['GET'])]
	public function indexAction(): Response {
		return $this->render('reasonsatisfied/index.html.twig', [
			'reasonSatisfieds' => $this->satisfiedRepository->findAll()
		]);
	}

	#[Route(path: '/new', name: 'reasonsatisfied_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$reasonSatisfied = new Reasonsatisfied();
		$form = $this->createForm(ReasonSatisfiedType::class, $reasonSatisfied);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->em->persist($reasonSatisfied);
			$this->em->flush();

			return $this->redirectToRoute('reasonsatisfied_show', ['id' => $reasonSatisfied->getId()]);
		}

		return $this->render('reasonsatisfied/new.html.twig', [
			'reasonSatisfied' => $reasonSatisfied,
			'form' => $form->createView(),
		]);
	}

	#[Route(path: '/{id}', name: 'reasonsatisfied_show', methods: ['GET'])]
	public function showAction(ReasonSatisfied $reasonSatisfied): Response {
		$deleteForm = $this->createDeleteForm($reasonSatisfied);

		return $this->render('reasonsatisfied/show.html.twig', [
			'reasonSatisfied' => $reasonSatisfied,
			'delete_form' => $deleteForm->createView(),
		]);
	}

	#[Route(path: '/{id}/edit', name: 'reasonsatisfied_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request, ReasonSatisfied $reasonSatisfied): RedirectResponse|Response {
		$deleteForm = $this->createDeleteForm($reasonSatisfied);
		$editForm = $this->createForm(ReasonSatisfiedType::class, $reasonSatisfied);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$this->em->flush();

			return $this->redirectToRoute('reasonsatisfied_show', ['id' => $reasonSatisfied->getId()]);
		}

		return $this->render('reasonsatisfied/edit.html.twig', [
			'reasonSatisfied' => $reasonSatisfied,
			'edit_form' => $editForm->createView(),
			'delete_form' => $deleteForm->createView(),
		]);
	}

	#[Route(path: '/', name: 'reasonsatisfied_delete', methods: ['DELETE'])]
	public function deleteAction(Request $request, ReasonSatisfied $reasonSatisfied): RedirectResponse {
		$form = $this->createDeleteForm($reasonSatisfied);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->em->remove($reasonSatisfied);
			$this->em->flush();
		}

		return $this->redirectToRoute('reasonsatisfied_index');
	}

	private function createDeleteForm(ReasonSatisfied $reasonSatisfied): Form {
		return $this->createFormBuilder()
			->setAction($this->generateUrl('reasonsatisfied_delete', ['id' => $reasonSatisfied->getId()]))
			->setMethod('DELETE')
			->getForm();
	}
}
