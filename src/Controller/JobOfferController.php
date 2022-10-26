<?php

namespace App\Controller;

use App\Entity\JobOffer;
use App\Form\JobOfferType;
use App\Repository\JobOfferRepository;
use App\Services\CompanyService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/jobOffer')]
class JobOfferController extends AbstractController {
	private EntityManagerInterface $em;
	private JobOfferRepository $jobOfferRepository;
	private CompanyService $companyService;

	public function __construct(
		EntityManagerInterface $em,
		JobOfferRepository     $jobOfferRepository,
		CompanyService $companyService
	) {
		$this->em = $em;
		$this->jobOfferRepository = $jobOfferRepository;
		$this->companyService = $companyService;
	}

	#[IsGranted('ROLE_USER')]
	#[Route(path: '/', name: 'jobOffer_index', methods: ['GET'])]
	public function indexAction(): Response {
		return $this->render('jobOffer/index.html.twig', [
			'jobOffers' => $this->jobOfferRepository->findAll()
		]);
	}

	#[IsGranted('ROLE_ADMIN')]
	#[Route(path: '/new', name: 'jobOffer_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$jobOffer = new JobOffer();
		$form = $this->createForm(JobOfferType::class, $jobOffer);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->em->persist($jobOffer);
			$this->em->flush();

			return $this->redirectToRoute('jobOffer_show', ['id' => $jobOffer->getId()]);
		}

		return $this->render('jobOffer/new.html.twig', [
			'jobOffer' => $jobOffer,
			'form' => $form->createView(),
		]);
	}

	#[Route(path: '/{id}', name: 'jobOffer_show', methods: ['GET'])]
	public function showAction(JobOffer $jobOffer): Response {
		$this->companyService->markJobOfferAsView($jobOffer->getId());
		return $this->render('jobOffer/show.html.twig', [
			'jobOffer' => $jobOffer,
		]);
	}

	#[IsGranted('ROLE_ADMIN')]
	#[Route(path: '/{id}/edit', name: 'jobOffer_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request, JobOffer $jobOffer): RedirectResponse|Response {
		$company = $jobOffer->getCompany();
		$editForm = $this->createForm(JobOfferType::class, $jobOffer);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$jobOffer->setCompany($company);
			$jobOffer->setUpdatedDate(new \DateTime());
			$this->em->flush();

			return $this->redirectToRoute('jobOffer_show', ['id' => $jobOffer->getId()]);
		}
		return $this->render('jobOffer/edit.html.twig', [
			'jobOffer' => $jobOffer,
			'edit_form' => $editForm->createView()
		]);
	}

	#[IsGranted('ROLE_ADMIN')]
	#[Route(path: '/delete/{id}', name: 'jobOffer_delete', methods: ['GET'])]
	public function deleteAction(Request $request, ?JobOffer $jobOffer): RedirectResponse {
		if (array_key_exists('HTTP_REFERER', $request->server->all())) {
			if ($jobOffer) {
				$this->em->remove($jobOffer);
				$this->em->flush();
				$this->addFlash('success', 'La suppression est faite avec success');
			} else {
				$this->addFlash('warning', 'Impossible de suppression l\'offre');
				return $this->redirect($request->server->all()['HTTP_REFERER']);
			}
		}
		return $this->redirectToRoute('jobOffer_index');
	}
}
