<?php

namespace App\Controller;

use App\Entity\Contract;
use App\Form\ContractType;
use App\Repository\ContractRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/contract')]
#[IsGranted('ROLE_ADMIN')]
class ContractController extends AbstractController {
	private EntityManagerInterface $em;
	private ContractRepository $contractRepository;
	private TranslatorInterface $translator;

	public function __construct(
		EntityManagerInterface $em,
		ContractRepository     $contractRepository,
		TranslatorInterface $translator
	) {
		$this->em = $em;
		$this->contractRepository = $contractRepository;
		$this->translator = $translator;
	}

	#[Route(path: '/', name: 'contract_index', methods: ['GET'])]
	public function indexAction(): Response {
		$contracts = $this->contractRepository->findAll();

		return $this->render('contract/index.html.twig', ['contracts' => $contracts]);
	}

	#[Route(path: '/new', name: 'contract_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$contract = new Contract();
		$form = $this->createForm(ContractType::class, $contract);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->em->persist($contract);
			$this->em->flush();

			return $this->redirectToRoute('contract_show', ['id' => $contract->getId()]);
		}

		return $this->render('contract/new.html.twig', [
			'contract' => $contract,
			'form' => $form->createView()]);
	}

	#[Route(path: '/{id}', name: 'contract_show', methods: ['GET'])]
	public function showAction(Contract $contract): Response {
		return $this->render('contract/show.html.twig', [
			'contract' => $contract,
		]);
	}

	#[Route(path: '/{id}/edit', name: 'contract_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request, Contract $contract): RedirectResponse|Response {
		$editForm = $this->createForm(ContractType::class, $contract);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$this->em->flush();

			return $this->redirectToRoute('contract_show', ['id' => $contract->getId()]);
		}

		return $this->render('contract/edit.html.twig', [
			'contract' => $contract,
			'edit_form' => $editForm->createView()
		]);
	}

	#[Route(path: '/delete/{id}', name: 'contract_delete', methods: ['GET'])]
	public function deleteAction(Request $request, ?Contract $contract): RedirectResponse {
		if (array_key_exists('HTTP_REFERER', $request->server->all())) {
			if ($contract) {
				$this->em->remove($contract);
				$this->em->flush();
				$this->addFlash('success', $this->translator->trans('flashbag.the_deletion_is_done_successfully'));
			} else {
				$this->addFlash('warning', $this->translator->trans('flashbag.unable_to_delete_the_country'));
				return $this->redirect($request->server->all()['HTTP_REFERER']);
			}
		}
		return $this->redirectToRoute('contract_index');
	}
}
