<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Country;
use App\Form\CompanyType;
use App\Repository\CompanyRepository;
use App\Repository\SchoolRepository;
use App\Repository\UserRepository;
use App\Services\ActivityService;
use App\Services\CompanyService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;


#[Route(path: '/company')]
#[Security("is_granted('ROLE_ADMIN') or 
            is_granted('ROLE_LEGISLATEUR') or 
            is_granted('ROLE_DIRECTEUR') or 
            is_granted('ROLE_ADMIN_REGIONS') or 
            is_granted('ROLE_ADMIN_PAYS') or 
            is_granted('ROLE_ADMIN_VILLES') or 
            is_granted('ROLE_PRINCIPAL')")]
class CompanyController extends AbstractController {
	private EntityManagerInterface $em;
	private ActivityService $activityService;
	private UserRepository $userRepository;
	private CompanyRepository $companyRepository;
    private SchoolRepository $schoolRepository;
    private CompanyService $companyService;
	private TranslatorInterface $translator;

	public function __construct(
		EntityManagerInterface $em,
		ActivityService        $activityService,
		UserRepository         $userRepository,
		CompanyRepository      $companyRepository,
        SchoolRepository       $schoolRepository,
        CompanyService         $companyService,
		TranslatorInterface    $translator
	) {
		$this->em = $em;
		$this->activityService = $activityService;
		$this->userRepository = $userRepository;
		$this->companyRepository = $companyRepository;
        $this->schoolRepository = $schoolRepository;
		$this->companyService = $companyService;
		$this->translator = $translator;
	}

	#[Route(path: '/', name: 'company_index', methods: ['GET'])]
	public function indexAction(Request $request): Response {
		$userCountry = $this->getUser()->getCountry();

		$companies = $this->companyRepository->findAll();
		if ($userCountry) {
            $companies = $this->companyRepository->findByCountry($userCountry);
        }

        // adaptation for multi administrators
        $userRegions = [];
        $userCities = [];
        if ($this->getUser()->hasRole('ROLE_ADMIN_REGIONS')) {
            $userRegions =  $this->getUser()->getAdminRegions();
        } else if ($this->getUser()->hasRole('ROLE_ADMIN_VILLES')) {
            $userCities =  $this->getUser()->getAdminCities();
        }

        if(count($userRegions) > 0) {
            $companies = [];
            foreach ($userRegions as $region) {
                $companies = array_merge($companies, $this->companyRepository->findByRegion($region));
            }
        } else if(count($userCities) > 0) {
            $companies = [];
            foreach ($userCities as $city) {
                $companies = array_merge($companies, $this->companyRepository->findByCity($city));
            }
        }

        //For principal Role
        if($this->getUser()->getPrincipalSchool()) {
            $school = $this->schoolRepository->find($this->getUser()->getPrincipalSchool());
            $companies = $this->companyRepository->getBySchool($school);
        }

		return $this->render('company/index.html.twig', ['companies' => $companies]);
	}

	#[Route(path: '/new', name: 'company_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$company = new Company();
		$company->setLocationMode(true);

		$form = $this->createForm(CompanyType::class, $company);
		$form->handleRequest($request);
		$selectedCountry = new Country();

        //adaptation for DBTA
        $selectedRegion = null;
        if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'true') {
            $selectedRegion = new Region();
        }

		if ($form->isSubmitted() && $form->isValid()) {
			$company->setCreatedDate(new \DateTime());
			$company->setUpdatedDate(new \DateTime());
			$dnsServer = $this->getParameter('dnsServer');

			if ((php_uname('n') != $dnsServer)&&(php_uname('n') != null))
				$company->setClientUpdateDate(new \DateTime());

			$this->em->persist($company);
			$this->em->flush();

			return $this->redirectToRoute('company_show', ['id' => $company->getId()]);
		}
		return $this->render('company/new.html.twig', [
			'company' => $company,
			'form' => $form->createView(),
			'allActivities' => $this->activityService->getAllActivities(),
			'selectedCountry' => $selectedCountry,
            'selectedRegion' => $selectedRegion
		]);
	}

	#[Route(path: '/{id}', name: 'company_show', methods: ['GET'])]
	public function showAction(Company $company): Response {
		return $this->render('company/show.html.twig', array(
			'company' => $company
		));
	}

	#[Route(path: '/{id}/edit', name: 'company_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request, Company $company): RedirectResponse|Response {
		$createdDate = $company->getCreatedDate();
		$editForm = $this->createForm(CompanyType::class, $company);
		$editForm->handleRequest($request);
		$selectedCountry = $company->getCountry();

        //adaptation for DBTA
        $selectedRegion = null;
        if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'true') {
            $selectedRegion = $this->getUser()->getRegion();
        }

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
            if ((php_uname('n') != $dnsServer)&&(php_uname('n') != null))
				$company->setClientUpdateDate(new \DateTime());

			$this->em->persist($company);
			$this->em->flush();

			return $this->redirectToRoute('company_show', array('id' => $company->getId()));
		}

		return $this->render('company/edit.html.twig', array(
			'company' => $company,
			'edit_form' => $editForm->createView(),
			'allActivities' => $this->activityService->getAllActivities(),
			'selectedCountry' => $selectedCountry,
            'selectedRegion' => $selectedRegion
		));
	}

	#[Route(path: '/delete/{id}', name: 'company_delete', methods: ['GET'])]
	public function deleteElementAction(Request $request, ?Company $company): RedirectResponse {
		if (array_key_exists('HTTP_REFERER', $request->server->all())) {
			if ($company) {
                $user = $company->getUser();
                if($user) {
                    $this->companyService->removeRelations($user);
                    $this->em->remove($user);
                    $this->em->flush();
                    $this->addFlash('success', $this->translator->trans('flashbag.the_deletion_of_the_user_is_done_with_success'));
                } else {
                    $this->addFlash('warning', $this->translator->trans('flashbag.unable_to_delete_user'));
                }
			} else {
				$this->addFlash('warning', $this->translator->trans('flashbag.unable_to_delete_company'));
				return $this->redirect($request->server->all()['HTTP_REFERER']);
			}
		}
		return $this->redirectToRoute('company_index');
	}
}
