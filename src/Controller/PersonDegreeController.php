<?php

namespace App\Controller;

use App\Entity\PersonDegree;
use App\Entity\School;
use App\Entity\User;
use App\Form\PersonDegreeType;
use App\Repository\PersonDegreeRepository;
use App\Repository\UserRepository;
use App\Repository\CountryRepository;
use App\Repository\RegionRepository;
use App\Services\ActivityService;
use App\Services\PersonDegreeDatatableService;
use App\Services\PersonDegreeService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;


#[Route(path: '/persondegree')]
#[Security("is_granted('ROLE_ADMIN') or 
            is_granted('ROLE_LEGISLATEUR') or 
            is_granted('ROLE_DIRECTEUR') or 
            is_granted('ROLE_ADMIN_REGIONS') or 
            is_granted('ROLE_ADMIN_PAYS') or 
            is_granted('ROLE_ADMIN_VILLES') or 
            is_granted('ROLE_PRINCIPAL')")]
class PersonDegreeController extends AbstractController {
	private EntityManagerInterface $em;
	private PersonDegreeRepository $personDegreeRepository;
	private ActivityService $activityService;
	private UserRepository $userRepository;
	private CountryRepository $countryRepository;
	private RegionRepository $regionRepository;
	private PersonDegreeService $personDegreeService;
	private TranslatorInterface $translator;

	public function __construct(
		EntityManagerInterface $em,
		PersonDegreeRepository $personDegreeRepository,
		ActivityService $activityService,
		UserRepository $userRepository,
		PersonDegreeService $personDegreeService,
		CountryRepository $countryRepository,
		RegionRepository $regionRepository,
		TranslatorInterface $translator
	) {
		$this->em = $em;
		$this->personDegreeRepository = $personDegreeRepository;
		$this->activityService = $activityService;
		$this->userRepository = $userRepository;
		$this->personDegreeService = $personDegreeService;
		$this->countryRepository = $countryRepository;
		$this->regionRepository = $regionRepository;
		$this->translator = $translator;
	}

	#[Route(path: '/', name: 'persondegree_index', methods: ['GET', 'POST'])]
	public function indexDatatableAction(
		Request $request,
		PersonDegreeDatatableService $datatableService
	): Response {
		/** @var User $currentUser */
		$currentUser = $this->getUser();
		$table = $datatableService->generateDatatable($request, $currentUser, null);
		$table->handleRequest($request);

		if ($table->isCallback()) {
			return $table->getResponse();
		}

		return $this->render('persondegree/index_datatable.html.twig', [
			'datatable' => $table
		]);
	}

	#[Route(path: '/new_asup', name: 'persondegree_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$personDegree = new Persondegree();
		$user = $this->getUser();

		$personDegree->setPhoneMobile1($user->getPhone());
		$personDegree->setLocationMode(true);
		$residenceCountryPhoneCode = null;

		$selectedCountry = $personDegree->getCountry();
		if ($this->getUser()->getResidenceCountry()) {
			$residenceCountryPhoneCode = $this->getUser()->getResidenceCountry()->getPhoneCode();
		}

		$personDegree->setDiaspora($user->isDiaspora());
		$personDegree->setResidenceCountry($user->getResidenceCountry());

		// adaptation dbta
		$selectedRegion = null;
		if ($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'true') {
			if ($user->getCountry()?->getId() != $user->getRegion()->getCountry()?->getId()) {
				$user->setCountry($user->getRegion()->getCountry());
				$this->em->persist($user);
				$this->em->flush();
			}
			$personDegree->setRegion($user->getRegion());
			$selectedRegion = $personDegree->getRegion();
		}

		$form = $this->createForm(PersonDegreeType::class, $personDegree, [
			'selectedCountry' => $selectedCountry->getId()
		]);
		$form->handleRequest($request);

		$otherCountries = $this->countryRepository->getNameAndIndicatif($selectedCountry->getId());
		if ($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'true') {
			$otherCountries = $this->regionRepository->getNameAndIndicatif($selectedCountry->getId());
		}

		if ($form->isSubmitted() && $form->isValid()) {
			$agreeRgpd = $form->get('agreeRgpd')->getData();
			if ($agreeRgpd) {
				$user->setEmail($personDegree->getEmail());
				$user->setDiaspora($personDegree->isDiaspora());
				$user->setResidenceCountry($personDegree->getResidenceCountry());

				$personDegree->setUser($user);
				$personDegree->setCreatedDate(new \DateTime());
				$personDegree->setUpdatedDate(new \DateTime());
				$personDegree->setPhoneMobile1($user->getPhone());
				$personDegree->setUnlocked(false);

				$dnsServer = $this->getParameter('dnsServer');
				if ((php_uname('n') != $dnsServer) && (php_uname('n') != null))
					$personDegree->setClientUpdateDate(new \DateTime());

				$this->em->persist($user);
				$this->em->persist($personDegree);
				$this->em->flush();

				return $this->redirectToRoute('persondegree_show', ['id' => $personDegree->getId()]);
			}
		}

		return $this->render('persondegree/new.html.twig', [
			'personDegree' => $personDegree,
			'form' => $form->createView(),
			'allActivities' => $this->activityService->getAllActivities(),
			'selectedCountry' => $selectedCountry,
			'selectedRegion' => $selectedRegion,
			'residenceCountryPhoneCode' => $residenceCountryPhoneCode,
			'otherCountries' => $otherCountries,
		]);
	}

	#[Route(path: '/{id}', name: 'persondegree_show', methods: ['GET'])]
	public function showAction(PersonDegree $personDegree): Response {
		return $this->render('persondegree/show.html.twig', [
			'personDegree' => $personDegree
		]);
	}

	#[Security("is_granted('ROLE_ADMIN') or 
                is_granted('ROLE_ADMIN_PAYS') or
                is_granted('ROLE_ADMIN_REGIONS') or
                is_granted('ROLE_ADMIN_VILLES')")]
	#[Route(path: '/{id}/edit', name: 'persondegree_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request, PersonDegree $personDegree): RedirectResponse|Response {
		$createdDate = $personDegree->getCreatedDate();
		$currentUser = $personDegree->getUser();
		$selectedCountry = $currentUser->getCountry();

		if (!$selectedCountry) {
			$selectedCountry = $personDegree->getCountry();
		}

		$otherCountries = $this->countryRepository->getNameAndIndicatif($selectedCountry->getId());
		$residenceCountryPhoneCode = null;
		if ($currentUser->getResidenceCountry()) {
			$residenceCountryPhoneCode = $currentUser->getResidenceCountry()->getPhoneCode();
		}

		//adaptation for DBTA
		$selectedRegion = null;
		if ($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'true') {
			$otherCountries = $this->regionRepository->getNameAndIndicatif($selectedCountry->getId());
			$residenceCountryPhoneCode = $currentUser->getResidenceRegion()?->getPhoneCode();
			$selectedRegion = $currentUser->getRegion();
		}

		$editForm = $this->createForm(PersonDegreeType::class, $personDegree, ['selectedCountry' => $selectedCountry->getId()]);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {

			$currentUser->setDiaspora($personDegree->isDiaspora());
			$currentUser->setResidenceCountry($personDegree->getResidenceCountry());
			$currentUser->setResidenceRegion($personDegree->getResidenceRegion());
			$this->em->persist($currentUser);

			// Patch if no createdDate found
			$personDegree->setCreatedDate($createdDate);
			if ($personDegree->getCreatedDate() == null) {
				if ($personDegree->getUpdatedDate()) {
					$personDegree->setCreatedDate($personDegree->getUpdatedDate());
				} else {
					$personDegree->setCreatedDate(new \DateTime());
				}
			}// end patch
			$personDegree->setUpdatedDate(new \DateTime());

			$dnsServer = $this->getParameter('dnsServer');
			if ((php_uname('n') != $dnsServer) && (php_uname('n') != null))
				$personDegree->setClientUpdateDate(new \DateTime());

			$personDegree->setUser($currentUser);
			$this->em->persist($currentUser);
			$this->em->persist($personDegree);
			$this->em->flush();
			return $this->redirectToRoute('persondegree_show', ['id' => $personDegree->getId()]);
		}
		$personDegree->setDiaspora($currentUser->isDiaspora());
		$personDegree->setResidenceCountry($currentUser->getResidenceCountry());

		$residenceCountryPhoneCode = $personDegree->getUser()->getResidenceCountry()?->getPhoneCode();
		if ($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'true') {
			$residenceCountryPhoneCode = $personDegree->getUser()->getResidenceRegion()?->getPhoneCode();
		}

		return $this->render('persondegree/edit.html.twig', [
			'personDegree' => $personDegree,
			'edit_form' => $editForm->createView(),
			'allActivities' => $this->activityService->getAllActivities(),
			'selectedCountry' => $selectedCountry,
			'selectedRegion' => $selectedRegion,
			'residenceCountryPhoneCode' => $residenceCountryPhoneCode,
			'otherCountries' => $otherCountries
		]);
	}

	#[Route(path: '/delete/{id}', name: 'persondegree_delete', methods: ['GET'])]
	public function deleteElementAction(Request $request, ?PersonDegree $personDegree): RedirectResponse {
		if (array_key_exists('HTTP_REFERER', $request->server->all())) {
			if ($personDegree) {
				$user = $personDegree->getUser();
				if ($user) {
					$this->personDegreeService->removeRelations($user);
					$this->em->remove($user);
					$this->em->flush();
					$this->addFlash('success', $this->translator->trans('flashbag.the_deletion_of_the_user_is_done_with_success'));

				} else {
					$this->addFlash('warning', $this->translator->trans('flashbag.unable_to_delete_user'));
					return $this->redirect($request->server->all()['HTTP_REFERER']);
				}
			} else {
				$this->addFlash('warning', $this->translator->trans('flashbag.unable_to_delete_graduate'));
				return $this->redirect($request->server->all()['HTTP_REFERER']);
			}
		}
		return $this->redirectToRoute('persondegree_index');
	}

	#[Route(path: '/filters/{id}/school', name: 'persondegree_filters_school', methods: ['GET'])]
	public function getFiltersBySchoolAction(School $school): JsonResponse {
		$listDegree = [];
		/** @var Degree $degree */
		foreach ($school->getDegrees() as $degree) {
			$listDegree[] = [
				'id' => $degree->getId(),
				'name' => $degree->getName()
			];
		}

		$listSectorArea = [];
		$listSectorArea[] = ['id' => $school->getSectorArea1()->getId(), 'name' => $school->getSectorArea1()->getName()];
		if ($school->getSectorArea2())
			$listSectorArea[] = ['id' => $school->getSectorArea2()->getId(), 'name' => $school->getSectorArea2()->getName()];
		if ($school->getSectorArea3())
			$listSectorArea[] = ['id' => $school->getSectorArea3()->getId(), 'name' => $school->getSectorArea3()->getName()];
		if ($school->getSectorArea4())
			$listSectorArea[] = ['id' => $school->getSectorArea4()->getId(), 'name' => $school->getSectorArea4()->getName()];

		$listActivity = [];
		/** @var Activity $activity */
		foreach ($school->getActivities1() as $activity) {
			$listActivity[] = ['id' => $activity->getId(), 'name' => $activity->getName()];
		}
		if ($school->getActivities2()) {
			/** @var Activity $activity */
			foreach ($school->getActivities2() as $activity) {
				$listActivity[] = ['id' => $activity->getId(), 'name' => $activity->getName()];
			}
		}
		if ($school->getActivities3()) {
			/** @var Activity $activity */
			foreach ($school->getActivities3() as $activity) {
				$listActivities[] = ['id' => $activity->getId(), 'name' => $activity->getName()];
			}
		}
		if ($school->getActivities4()) {
			/** @var Activity $activity */
			foreach ($school->getActivities4() as $activity) {
				$listActivity[] = ['id' => $activity->getId(), 'name' => $activity->getName()];
			}
		}
		return new JsonResponse([$listDegree, $listSectorArea, $listActivity]);
	}
}
