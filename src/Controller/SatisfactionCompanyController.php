<?php

namespace App\Controller;

use App\Entity\SatisfactionCompany;
use App\Form\SatisfactionCompanyType;
use App\Repository\ActivityRepository;
use App\Repository\SatisfactionCompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/satisfactioncompany')]
#[IsGranted('ROLE_ADMIN')]
class SatisfactionCompanyController extends AbstractController {

	private EntityManagerInterface $em;
	private SatisfactionCompanyRepository $satisfactionCompanyRepository;
	private ActivityRepository $activityRepository;

	public function __construct(
		EntityManagerInterface        $em,
		SatisfactionCompanyRepository $satisfactionCompanyRepository,
		ActivityRepository            $activityRepository
	) {
		$this->em = $em;
		$this->satisfactionCompanyRepository = $satisfactionCompanyRepository;
		$this->activityRepository = $activityRepository;
	}

	#[Route(path: '/', name: 'satisfactioncompany_index', methods: ['GET'])]
	public function indexAction(): Response {
		return $this->render('satisfactioncompany/index.html.twig', array(
			'satisfactionCompanies' => $this->satisfactionCompanyRepository->findAll(),
		));
	}

	#[Route(path: '/new', name: 'satisfactioncompany_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$satisfactionCompany = new Satisfactioncompany();
		$form = $this->createForm(SatisfactionCompanyType::class, $satisfactionCompany);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$satisfactionCompany->setCreatedDate(new \DateTime());
			$satisfactionCompany->setUpdatedDate(new \DateTime());

			$this->em->persist($satisfactionCompany);
			$this->em->flush();

			return $this->redirectToRoute('satisfactioncompany_show', ['id' => $satisfactionCompany->getId()]);
		}

		return $this->render('satisfactioncompany/new.html.twig', [
			'satisfactionCompany' => $satisfactionCompany,
			'form' => $form->createView(),
			'allActivities' => $this->activityRepository->findAll(),
		]);
	}

	#[Route(path: '/{id}', name: 'satisfactioncompany_show', methods: ['GET'])]
	public function showAction(SatisfactionCompany $satisfactionCompany): Response {
		return $this->render('satisfactioncompany/show.html.twig', [
			'satisfactionCompany' => $satisfactionCompany
		]);
	}


	#[Route(path: '/{id}/edit', name: 'satisfactioncompany_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request, SatisfactionCompany $satisfactionCompany): RedirectResponse|Response {
		$createdDate = $satisfactionCompany->getCreatedDate();
		$editForm = $this->createForm(SatisfactionCompanyType::class, $satisfactionCompany);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$satisfactionCompany->setCreatedDate($createdDate);
			if ($satisfactionCompany->getCreatedDate() == null) {
				if ($satisfactionCompany->getUpdatedDate()) {
					$satisfactionCompany->setCreatedDate($satisfactionCompany->getUpdatedDate());
				} else {
					$satisfactionCompany->setCreatedDate(new \DateTime());
				}
			}

			$satisfactionCompany->setUpdatedDate(new \DateTime());
			$this->em->flush();

			return $this->redirectToRoute('satisfactioncompany_show', ['id' => $satisfactionCompany->getId()]);
		}

		return $this->render('satisfactioncompany/edit.html.twig', [
			'satisfactionCompany' => $satisfactionCompany,
			'edit_form' => $editForm->createView(),
			'allActivities' => $this->activityRepository->findAll(),
		]);
	}

	#[Route(path: '/delete/{id}', name: 'satisfactioncompany_delete', methods: ['GET'])]
	public function deleteElementAction(Request $request, ?SatisfactionCompany $satisfactionCompany): RedirectResponse {
		if (array_key_exists('HTTP_REFERER', $request->server->all())) {
			if ($satisfactionCompany) {
				$this->em->remove($satisfactionCompany);
				$this->em->flush();
				$this->addFlash('success', 'La suppression est faite avec success');
			} else {
				$this->addFlash('warning', 'Impossible de suppression la satisfaction');
				return $this->redirect($request->server->all()['HTTP_REFERER']);
			}
		}
		return $this->redirectToRoute('satisfactioncompany_index');
	}
}
