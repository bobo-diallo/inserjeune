<?php

namespace App\Controller;

use App\Entity\Currency;
use App\Form\CurrencyType;
use App\Repository\CurrencyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/currency')]
#[IsGranted('ROLE_ADMIN')]
class CurrencyController extends AbstractController {
	private EntityManagerInterface $em;
	private CurrencyRepository $currencyRepository;
	private TranslatorInterface $translator;

	public function __construct(
		EntityManagerInterface $em,
		CurrencyRepository $currencyRepository,
		TranslatorInterface $translator
	) {
		$this->em = $em;
		$this->currencyRepository = $currencyRepository;
		$this->translator = $translator;
	}

	#[Route(path: '/', name: 'currency_index', methods: ['GET'])]
	public function indexAction(): Response {
		return $this->render('currency/index.html.twig', [
			'currencies' => $this->currencyRepository->findAll()
		]);
	}

	#[Route(path: '/new', name: 'currency_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$currency = new Currency();
		$form = $this->createForm(CurrencyType::class, $currency);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->em->persist($currency);
			$this->em->flush();

			return $this->redirectToRoute('currency_show', ['id' => $currency->getId()]);
		}

		return $this->render('currency/new.html.twig', [
			'currency' => $currency,
			'form' => $form->createView()
		]);
	}

	#[Route(path: '/{id}', name: 'currency_show', methods: ['GET'])]
	public function showAction(Currency $currency): Response {
		return $this->render('currency/show.html.twig', [
			'currency' => $currency
		]);
	}

	#[Route(path: '/{id}/edit', name: 'currency_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request, Currency $currency): RedirectResponse|Response {
		$editForm = $this->createForm(CurrencyType::class, $currency);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$this->em->flush();

			return $this->redirectToRoute('currency_show', ['id' => $currency->getId()]);
		}

		return $this->render('currency/edit.html.twig', [
			'currency' => $currency,
			'edit_form' => $editForm->createView(),
		]);
	}


	#[Route(path: '/delete/{id}', name: 'currency_delete', methods: ['GET'])]
	public function deleteAction(Request $request, ?Currency $currency): RedirectResponse {
		if (array_key_exists('HTTP_REFERER', $request->server->all())) {
			if ($currency) {
				$this->em->remove($currency);
				$this->em->flush();
				$this->addFlash('success', $this->translator->trans('flashbag.the_deletion_is_done_successfully'));
			} else {
				$this->addFlash('warning', $this->translator->trans('flashbag.unable_to_delete_the_country'));
				return $this->redirect($request->server->all()['HTTP_REFERER']);
			}
		}
		return $this->redirectToRoute('currency_index');
	}
}
