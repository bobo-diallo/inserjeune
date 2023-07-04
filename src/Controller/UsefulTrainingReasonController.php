<?php

namespace App\Controller;

use App\Entity\UsefulTrainingReason;
use App\Form\UsefulTrainingReasonType;
use App\Repository\UsefulTrainingReasonRepository;
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

#[Route(path: '/usefultrainingreason')]
#[IsGranted('ROLE_ADMIN')]
class UsefulTrainingReasonController extends AbstractController {
	private EntityManagerInterface $em;
	private UsefulTrainingReasonRepository $usefulTrainingReasonRepository;
	private TranslatorInterface $translator;

	public function __construct(
		EntityManagerInterface         $em,
		UsefulTrainingReasonRepository $usefulTrainingReasonRepository,
		TranslatorInterface $translator
	) {
		$this->em = $em;
		$this->usefulTrainingReasonRepository = $usefulTrainingReasonRepository;
		$this->translator = $translator;
	}

	#[Route(path: '/', name: 'usefultrainingreason_index', methods: ['GET'])]
	public function indexAction(): Response {
		return $this->render('usefultrainingreason/index.html.twig', array(
			'usefulTrainingReasons' => $this->usefulTrainingReasonRepository->findAll(),
		));
	}

	#[Route(path: '/new', name: 'usefultrainingreason_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$usefulTrainingReason = new Usefultrainingreason();
		$form = $this->createForm(UsefulTrainingReasonType::class, $usefulTrainingReason);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->em->persist($usefulTrainingReason);
			$this->em->flush();

			return $this->redirectToRoute('usefultrainingreason_show', ['id' => $usefulTrainingReason->getId()]);
		}

		return $this->render('usefultrainingreason/new.html.twig', [
			'usefulTrainingReason' => $usefulTrainingReason,
			'form' => $form->createView(),
		]);
	}

	#[Route(path: '/{id}', name: 'usefultrainingreason_show', methods: ['GET'])]
	public function showAction(UsefulTrainingReason $usefulTrainingReason): Response {
		$deleteForm = $this->createDeleteForm($usefulTrainingReason);

		return $this->render('usefultrainingreason/show.html.twig', [
			'usefulTrainingReason' => $usefulTrainingReason,
			'delete_form' => $deleteForm->createView(),
		]);
	}

	#[Route(path: '/{id}/edit', name: 'usefultrainingreason_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request, UsefulTrainingReason $usefulTrainingReason): RedirectResponse|Response {
		$deleteForm = $this->createDeleteForm($usefulTrainingReason);
		$editForm = $this->createForm(UsefulTrainingReasonType::class, $usefulTrainingReason);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$this->em->flush();

			return $this->redirectToRoute('usefultrainingreason_show', ['id' => $usefulTrainingReason->getId()]);
		}

		return $this->render('usefultrainingreason/edit.html.twig', [
			'usefulTrainingReason' => $usefulTrainingReason,
			'edit_form' => $editForm->createView(),
			'delete_form' => $deleteForm->createView(),
		]);
	}

	#[Route(path: '/{id}', name: 'usefultrainingreason_delete', methods: ['DELETE'])]
	public function deleteAction(Request $request, UsefulTrainingReason $usefulTrainingReason): RedirectResponse {
		$form = $this->createDeleteForm($usefulTrainingReason);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->em->remove($usefulTrainingReason);
			$this->em->flush();
		}

		return $this->redirectToRoute('usefultrainingreason_index');
	}

	private function createDeleteForm(UsefulTrainingReason $usefulTrainingReason): Form {
		return $this->createFormBuilder()
			->setAction($this->generateUrl('usefultrainingreason_delete', ['id' => $usefulTrainingReason->getId()]))
			->setMethod('DELETE')
			->getForm();
	}
}
