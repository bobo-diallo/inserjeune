<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Country;
use App\Form\CompanyType;
use App\Repository\CompanyRepository;
use App\Repository\UserRepository;
use App\Services\ActivityService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/company')]
#[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_LEGISLATEUR')")]
class CompanyController extends AbstractController {
	private EntityManagerInterface $em;
	private ActivityService $activityService;
	private UserRepository $userRepository;
	private CompanyRepository $companyRepository;

	public function __construct(
		EntityManagerInterface $em,
		ActivityService        $activityService,
		UserRepository         $userRepository,
		CompanyRepository      $companyRepository
	) {
		$this->em = $em;
		$this->activityService = $activityService;
		$this->userRepository = $userRepository;
		$this->companyRepository = $companyRepository;
	}

	#[Route(path: '/', name: 'company_index', methods: ['GET'])]
	public function indexAction(): Response {
		$userCountry = $this->getUser()->getCountry();

		$companies = $this->companyRepository->findAll();
		if ($userCountry)
			$companies = $this->companyRepository->findByCountry($userCountry);

		return $this->render('company/index.html.twig', ['companies' => $companies]);
	}

	#[Route(path: '/new', name: 'company_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$company = new Company();
		$form = $this->createForm(CompanyType::class, $company);
		$form->handleRequest($request);
		$selectedCountry = new Country();

		if ($form->isSubmitted() && $form->isValid()) {
			$company->setCreatedDate(new \DateTime());
			$company->setUpdatedDate(new \DateTime());
			$dnsServer = $this->getParameter('dnsServer');
			if (php_uname('n') != $dnsServer)
				$company->setClientUpdateDate(new \DateTime());

			$this->em->persist($company);
			$this->em->flush();

			return $this->redirectToRoute('company_show', ['id' => $company->getId()]);
		}
		return $this->render('company/new.html.twig', [
			'company' => $company,
			'form' => $form->createView(),
			'allActivities' => $this->activityService->getAllActivities(),
			'selectedCountry' => $selectedCountry
		]);
	}

	#[Route(path: '/{id}', name: 'company_show', methods: ['GET'])]
	public function showAction(Company $company): Response {
		return $this->render('company/show.html.twig', array(
			'company' => $company,
		));
	}

	#[Route(path: '/{id}/edit', name: 'company_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request, Company $company): RedirectResponse|Response {
		$createdDate = $company->getCreatedDate();
		$editForm = $this->createForm(CompanyType::class, $company);
		$editForm->handleRequest($request);
		$selectedCountry = $company->getCountry();

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$currentUser = $this->userRepository->getFromCompany($company->getId());

			if (count($currentUser) > 0)
				$company->setUser($currentUser[0]);

			$company->setCreatedDate($createdDate);
			if ($company->getCreatedDate() == null) {
				if ($company->getUpdatedDate()) {
					$company->setCreatedDate($company->getUpdatedDate());
				} else {
					$company->setCreatedDate(new \DateTime());
				}
			}
			$company->setUpdatedDate(new \DateTime());

			$dnsServer = $this->getParameter('dnsServer');
			if (php_uname('n') != $dnsServer)
				$company->setClientUpdateDate(new \DateTime());

			$this->em->persist($company);
			$this->em->flush();

			return $this->redirectToRoute('company_show', array('id' => $company->getId()));
		}

		return $this->render('company/edit.html.twig', array(
			'company' => $company,
			'edit_form' => $editForm->createView(),
			'allActivities' => $this->activityService->getAllActivities(),
			'selectedCountry' => $selectedCountry
		));
	}

	#[Route(path: '/delete/{id}', name: 'company_delete', methods: ['GET'])]
	public function deleteElementAction(Request $request, ?Company $company): RedirectResponse {
		if (array_key_exists('HTTP_REFERER', $request->server->all())) {
			if ($company) {
				$this->em->remove($company);
				$this->em->flush();
				$this->addFlash('success', 'La suppression est faite avec success');
			} else {
				$this->addFlash('warning', 'Impossible de suppression l\'entreprise');
				return $this->redirect($request->server->all()['HTTP_REFERER']);
			}
		}
		return $this->redirectToRoute('company_index');
	}
}
