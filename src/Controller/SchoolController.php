<?php

namespace App\Controller;

use App\Entity\School;
use App\Form\SchoolType;
use App\Repository\SchoolRepository;
use App\Repository\UserRepository;
use App\Services\ActivityService;
use App\Services\SchoolService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/school')]
#[Security("is_granted('ROLE_ADMIN') or 
            is_granted('ROLE_LEGISLATEUR') or 
            is_granted('ROLE_DIRECTEUR') or 
            is_granted('ROLE_ADMIN_REGIONS') or 
            is_granted('ROLE_ADMIN_PAYS') or 
            is_granted('ROLE_ADMIN_VILLES') or 
            is_granted('ROLE_PRINCIPAL')")]
class SchoolController extends AbstractController {
	private EntityManagerInterface $em;
	private SchoolRepository $schoolRepository;
	private ActivityService $activityService;
	private UserRepository $userRepository;
    private SchoolService $schoolService;
	private TranslatorInterface $translator;

	public function __construct(
		EntityManagerInterface $em,
		SchoolRepository       $schoolRepository,
		ActivityService        $activityService,
		UserRepository         $userRepository,
        SchoolService          $schoolService,
		TranslatorInterface $translator
	) {
		$this->em = $em;
		$this->schoolRepository = $schoolRepository;
		$this->activityService = $activityService;
		$this->userRepository = $userRepository;
        $this->schoolService = $schoolService;
		$this->translator = $translator;
	}

	#[Route(path: '/', name: 'school_index', methods: ['GET'])]
	public function indexAction(): Response {
		$userCountry = $this->getUser()->getCountry();
        $userCountries = [];
        $userRegions = [];
        $userCities = [];
        if ($this->getUser()->hasRole('ROLE_ADMIN_REGIONS')) {
            $userRegions =  $this->getUser()->getAdminRegions();
        } else if ($this->getUser()->hasRole('ROLE_ADMIN_VILLES')) {
            $userCities =  $this->getUser()->getAdminCities();
        }

		$schools = ($userCountry) ?
			$this->schoolRepository->findByCountry($userCountry) :
			$this->schoolRepository->findAll();

        if(count($userRegions) >0) {
            $schools = [];
            foreach ($userRegions as $region) {
                $schools = array_merge($schools, $this->schoolRepository->findByRegion($region));
            }
        } else if(count($userCities) >0) {
            $schools = [];
            foreach ($userCities as $city) {
                $schools = array_merge($schools, $this->schoolRepository->findByCity($city));
            }
        }
		return $this->render('school/index.html.twig', [
			'schools' => $schools
		]);
	}

	#[Route(path: '/new', name: 'school_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$school = new School();
		$school->setLocationMode(true);

		$form = $this->createForm(SchoolType::class, $school);
		$form->handleRequest($request);
		$selectedCountry = $school->getCountry();

        //adaptation for DBTA
        $selectedRegion = null;
        if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'true') {
            $selectedRegion = $this->getUser()->getRegion();
        }

		if ($form->isSubmitted() && $form->isValid()) {
			$school->setCreatedDate(new \DateTime());
			$school->setUpdatedDate(new \DateTime());
			$dnsServer = $this->getParameter('dnsServer');
			$school->setUpdatedDate(new \DateTime());

            if ((php_uname('n') != $dnsServer)&&(php_uname('n') != null)) {
				$school->setClientUpdateDate(new \DateTime());
			}

			$this->em->persist($school);
			$this->em->flush();

			return $this->redirectToRoute('school_show', ['id' => $school->getId()]);
		}

		return $this->render('school/new.html.twig', [
			'school' => $school,
			'form' => $form->createView(),
			'allActivities' => $this->activityService->getAllActivities(),
			'selectedCountry' => $selectedCountry,
            'selectedRegion' => $selectedRegion
		]);
	}

	#[Route(path: '/{id}', name: 'school_show', methods: ['GET'])]
	public function showAction(School $school): Response {
		return $this->render('school/show.html.twig', [
			'school' => $school
		]);
	}

    // // #[IsGranted('ROLE_PRINCIPAL')]
    #[Route(path: '/mySchool', name: 'my_school', methods: ['GET'])]
    public function mySchoolAction(Request $request): Response {
        $school = new School();
        // if($this->getUser()->getPrincipalSchool()) {
        // return $id ; die();
            // $school = $this->schoolRepository->find(intval($id));
            // return $this->render('school/show.html.twig', [
            //     'school' => $school
            // ]);
        // }
        return $this->redirectToRoute('dashboard_index');
    }

	#[IsGranted('ROLE_ADMIN')]
	#[Route(path: '/{id}/edit', name: 'school_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request, School $school): Response {
		$createdDate = $school->getCreatedDate();
		$editForm = $this->createForm(SchoolType::class, $school);
		$editForm->handleRequest($request);
		$selectedCountry = $school->getCountry();

        //adaptation for DBTA
        $selectedRegion = null;
        if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'true') {
            $selectedRegion = $school->getUser()->getRegion();
        }

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$school->setCreatedDate($createdDate);
			if ($school->getCreatedDate() == null) {
				if ($school->getUpdatedDate()) {
					$school->setCreatedDate($school->getUpdatedDate());
				} else {
					$school->setCreatedDate(new \DateTime());
				}
			}
			$school->setUpdatedDate(new \DateTime());
			$dnsServer = $this->getParameter('dnsServer');

            if ((php_uname('n') != $dnsServer)&&(php_uname('n') != null)) {
				$school->setClientUpdateDate(new \DateTime());
			}

            $currentUser = $this->userRepository->getFromSchool($school->getId());
            if (count($currentUser) > 0) {
                $school->setUser($currentUser[0]);
            }

			$school->setMapsAddress(null);

			$this->em->persist($school);
			$this->em->flush();

			return $this->redirectToRoute('school_show', ['id' => $school->getId()]);
		}

		return $this->render('school/edit.html.twig', [
			'school' => $school,
			'edit_form' => $editForm->createView(),
			'allActivities' => $this->activityService->getAllActivities(),
			'selectedCountry' => $selectedCountry,
            'selectedRegion' => $selectedRegion
		]);
	}

	#[Route(path: '/delete/{id}', name: 'school_delete', methods: ['GET'])]
	public function deleteElementAction(Request $request, ?School $school): RedirectResponse {
		if (array_key_exists('HTTP_REFERER', $request->server->all())) {
			if ($school) {
                // delete Principals users
                $principals = $this->userRepository->findByPrincipalSchool($school->getId());
                foreach ($principals as $principal) {
                    $this->em->remove($principal);
                }

                $user = $school->getUser();
                if($user) {
					if ($school->getPersonDegrees()->count() > 0) {
						$this->addFlash('warning', $this->translator->trans('flashbag.unable_to_delete_establishment_please_remove_its_graduates_first'));
					} else {
						$this->schoolService->removeRelations($user);
						$this->em->remove($user);
						$this->em->flush();
						$this->addFlash('success', $this->translator->trans('flashbag.the_deletion_of_the_user_is_done_with_success'));
					}

                } else {
                    $this->addFlash('warning', $this->translator->trans('flashbag.unable_to_delete_user'));
                }
			} else {
				$this->addFlash('warning', $this->translator->trans('flashbag.unable_to_delete_school'));
				return $this->redirect($request->server->all()['HTTP_REFERER']);
			}
		}
		return $this->redirectToRoute('school_index');
	}
}
