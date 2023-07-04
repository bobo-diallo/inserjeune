<?php

namespace App\Controller;

use App\Entity\OmissionReason;
use App\Form\OmissionReasonType;
use App\Repository\LegalStatusRepository;
use App\Repository\OmissionReasonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/omissionreason')]
#[IsGranted('ROLE_ADMIN')]
class OmissionReasonController extends AbstractController {
	private EntityManagerInterface $em;
	private OmissionReasonRepository $omissionReasonRepository;
	private TranslatorInterface $translator;

	public function __construct(
		EntityManagerInterface $em,
		OmissionReasonRepository $omissionReasonRepository,
		TranslatorInterface $translator
	) {
		$this->em = $em;
		$this->omissionReasonRepository = $omissionReasonRepository;
		$this->translator = $translator;
	}

	#[Route(path: '/', name: 'omissionreason_index', methods: ['GET'])]
	public function indexAction(): Response {
		return $this->render('omissionreason/index.html.twig', array(
			'omissionReasons' => $this->omissionReasonRepository->findAll(),
		));
	}

	#[Route(path: '/new', name: 'omissionreason_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$omissionReason = new OmissionReason();
		$form = $this->createForm(OmissionReasonType::class, $omissionReason);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->em->persist($omissionReason);
			$this->em->flush();

			return $this->redirectToRoute('omissionreason_show', ['id' => $omissionReason->getId()]);
		}

		return $this->render('omissionreason/new.html.twig', [
			'omissionReason' => $omissionReason,
			'form' => $form->createView(),
		]);
	}

	#[Route(path: '/{id}', name: 'omissionreason_show', methods: ['GET'])]
	public function showAction(OmissionReason $omissionReason): Response {
		$deleteForm = $this->createDeleteForm($omissionReason);

		return $this->render('omissionreason/show.html.twig', [
			'omissionReason' => $omissionReason,
			'delete_form' => $deleteForm->createView(),
		]);
	}

	#[Route(path: '/{id}/edit', name: 'omissionreason_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request, OmissionReason $omissionReason): RedirectResponse|Response {
		$deleteForm = $this->createDeleteForm($omissionReason);
		$editForm = $this->createForm(OmissionReasonType::class, $omissionReason);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$this->em->flush();

			return $this->redirectToRoute('omissionreason_show', ['id' => $omissionReason->getId()]);
		}

		return $this->render('omissionreason/edit.html.twig', [
			'omissionReason' => $omissionReason,
			'edit_form' => $editForm->createView(),
			'delete_form' => $deleteForm->createView(),
		]);
	}

	#[Route(path: '/{id}', name: 'omissionreason_delete', methods: ['DELETE'])]
	public function deleteAction(Request $request, OmissionReason $omissionReason): RedirectResponse {
		$form = $this->createDeleteForm($omissionReason);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->em->remove($omissionReason);
			$this->em->flush();
		}

		return $this->redirectToRoute('omissionreason_index');
	}

	private function createDeleteForm(OmissionReason $omissionReason): Form {
		return $this->createFormBuilder()
			->setAction($this->generateUrl('omissionreason_delete', ['id' => $omissionReason->getId()]))
			->setMethod('DELETE')
			->getForm();
	}
}
