<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Country;
use App\Entity\City;
use App\Entity\GeoLocation;
use App\Entity\PersonDegree;
use App\Entity\Region;
use App\Entity\School;
use App\Entity\SectorArea;
use App\Form\GeoLocationType;
use App\Repository\CompanyRepository;
use App\Repository\PersonDegreeRepository;
use App\Repository\SchoolRepository;
use App\Services\CompanyService;
use App\Services\PersonDegreeService;
use App\Services\SchoolService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Tests\Globals\IntlGlobalsTest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/geolocation')]
class GeoLocationController extends AbstractController {

	private EntityManagerInterface $em;
	private CompanyRepository $companyRepository;
	private SchoolRepository $schoolRepository;
	private PersonDegreeRepository $personDegreeRepository;
	private TranslatorInterface $translator;
    private CompanyService $companyService;
    private SchoolService $schoolService;
    private PersonDegreeService $personDegreeService;

	public function __construct(
		EntityManagerInterface $em,
		CompanyRepository      $companyRepository,
		SchoolRepository       $schoolRepository,
		PersonDegreeRepository $personDegreeRepository,
        SchoolService          $schoolService,
        CompanyService         $companyService,
        PersonDegreeService    $personDegreeService,
		TranslatorInterface    $translator
	) {
		$this->em = $em;
		$this->companyRepository = $companyRepository;
		$this->schoolRepository = $schoolRepository;
		$this->personDegreeRepository = $personDegreeRepository;
        $this->schoolService = $schoolService;
        $this->companyService = $companyService;
        $this->personDegreeService = $personDegreeService;
		$this->translator = $translator;
	}

	#[Route(path: '/', name: 'geolocation', methods: ['GET', 'POST'])]
	public function indexAction(Request $request): Response {
		$geoLocation = new GeoLocation();

        // adaptation aux multi administrateurs
        $selectedCountry = null;
        $selectedRegions = [];
        if ($this->getUser()->hasRole('ROLE_ADMIN_REGIONS')) {
            $selectedRegions =  $this->getUser()->getAdminRegions();
        } else if ($this->getUser()->hasRole('ROLE_ADMIN_VILLES')) {
            $selectedCities =  $this->getUser()->getAdminCities();
            foreach ($selectedCities as $selectedCity){
                if($selectedCity) {
                    $regionExist = false;
                    foreach ($selectedRegions as $selectedRegion) {
                        if ($selectedRegion->getId() == $selectedCity->getRegion()->getId()) {
                            $regionExist = true;
                        }
                    }
                    if (!$regionExist) {
                        $selectedRegions[] = $selectedCity->getRegion();
                    }
                }
            }
        } else {
            if($this->getUser()->getCountry()) {
                $selectedCountry = $this->getUser()->getCountry();
            }
        }

        // Adapatation pour l'env STRUCT_PROVINCE_COUNTRY_CITY DIPLOME ENTREPRISE ET SCHOOL
        if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'true') {
            if(count($selectedRegions) == 0) {
                if ($this->getUser()->getRegion()) {
                    $selectedRegions[] = $this->getUser()->getRegion();
                }
            }
        }

		if ($selectedCountry) {
			$form = $this->createForm(GeoLocationType::class, $geoLocation, ['selectedCountry' => $selectedCountry->getId()]);
		} else {
			$form = $this->createForm(GeoLocationType::class, $geoLocation, ['selectedCountry' => ""]);
		}
		$form->handleRequest($request);

        // test if account is created for company, school and personDegree
        if ($this->getUser()->hasRole('ROLE_ENTREPRISE')) {
            if(!$this->companyService->getCompany()) {
                return $this->redirectToRoute('front_company_new');
            }
        } elseif ( $this->getUser()->hasRole('ROLE_ETABLISSEMENT')) {
            if(!$this->schoolService->getSchool()) {
                return $this->redirectToRoute('front_school_new');
            }
        } elseif ( $this->getUser()->hasRole('ROLE_DIPLOME')) {
            if(!$this->personDegreeService->getPersonDegree()) {
                return $this->redirectToRoute('front_persondegree_new');
            }
        }

        //For principal Role
        $school = null;
        if($this->getUser()->getPrincipalSchool()) {
            $school = $this->schoolRepository->find($this->getUser()->getPrincipalSchool());
        }

		return $this->render('GeoLocation/maps.html.twig', [
			'form' => $form->createView(),
			'selectedCountry' => $selectedCountry,
            'selectedRegions' => $selectedRegions,
            'school' => $school
		]);
	}

	#[Route(path: '/city/{id}/company', name: 'geolocation_map_city_companies', methods: ['GET'])]
	public function getCompaniesByCityAction(Request $request, City $city): JsonResponse|Response {
		$companies = $this->companyRepository->getNameByCity($city);
		return new JsonResponse($this->createArrayCompanyData($companies));
	}

	#[Route(path: '/region/{id}/company', name: 'geolocation_map_region_companies', methods: ['GET'])]
	public function getCompaniesByRegionAction(Request $request, Region $region): JsonResponse|Response {
		$companies = $this->companyRepository->getNameByRegion($region);
		return new JsonResponse($this->createArrayCompanyData($companies));
	}

	/**
	 * Affichage des entreprise par pays
	 */
	#[Route(path: '/country/{id}/company', name: 'geolocation_map_country_companies', methods: ['GET'])]
	public function getCompaniesByCountryAction(Request $request, Country $country): JsonResponse|Response {
		$companies = $this->companyRepository->getNameByCountry($country);
		return new JsonResponse($this->createArrayCompanyData($companies));
	}

	/**
	 * Affichage des entreprise par pays et par secteur d'activité
	 */
	#[Route(path: '/country_sectorarea/{country}/{sectorArea}/company', name: 'geolocation_map_country_sectorarea_companies', methods: ['GET'])]
	#[ParamConverter('post', options: ['mapping' => ['id' => 'post_id']])]
	public function getCompaniesByCountryAndSectorAreaAction(
		Request    $request,
		Country    $country,
		SectorArea $sectorArea
	): JsonResponse|Response {
		$companies = $this->companyRepository->getByCountryAndSectorArea($country, $sectorArea);
		return new JsonResponse($companies);
	}

	/**
	 * Affichage des ecoles par ville
	 */
	#[Route(path: '/city/{id}/school', name: 'geolocation_map_city_schools', methods: ['GET'])]
	public function getSchoolsByCityAction(Request $request, City $city): JsonResponse|Response {
		$schools = $this->schoolRepository->getNameByCity($city);
		return new JsonResponse($this->createArraySchoolData($schools));
	}

	/**
	 * Affichage des ecoles par region
	 */
	#[Route(path: '/region/{id}/school', name: 'geolocation_map_region_schools', methods: ['GET'])]
	public function getSchoolsRegionAction(Request $request, Region $region): JsonResponse|Response {
		$schools = $this->schoolRepository->getNameByRegion($region);
		return new JsonResponse($this->createArraySchoolData($schools));
	}

	/**
	 * Affichage des ecoles par pays
	 */
	#[Route(path: '/country/{id}/school', name: 'geolocation_map_country_schools', methods: ['GET'])]
	public function getSchoolsCountryAction(Request $request, Country $country): JsonResponse|Response {
		$schools = $this->schoolRepository->getNameByCountry($country);
		return new JsonResponse($this->createArraySchoolData($schools));
	}

	/**
	 * Affichage des diplômes par ville
	 */
	#[Route(path: '/city/{id}/persondegree', name: 'geolocation_map_city_persondegrees', methods: ['GET'])]
	public function getPersonDegreesByCityAction(Request $request, City $city): JsonResponse|Response {
		$personDegrees = $this->personDegreeRepository->getNameByCity($city);
		return new JsonResponse($this->createArrayPersonDegreeData($personDegrees));
	}

	/**
	 * Affichage des diplômes par region
	 */
	#[Route(path: '/region/{id}/persondegree', name: 'geolocation_map_region_persondegrees', methods: ['GET'])]
	public function getPersonDegreesRegionAction(Request $request, Region $region): JsonResponse|Response {
		$personDegrees = $this->personDegreeRepository->getNameByRegion($region);
		return new JsonResponse($this->createArrayPersonDegreeData($personDegrees));
	}

    #[Route(path: '/region/{id}/unemployedpersondegree', name: 'geolocation_map_region_unemployed_persondegrees', methods: ['GET'])]
    public function getUnemployedPersonDegreesRegionAction(Request $request, Region $region): JsonResponse|Response {
        // $personDegrees = $this->personDegreeRepository->getByCountryAndType($country,'TYPE_SEARCH');
        $personDegrees = $this->personDegreeRepository->getByRegionAndType($region,'TYPE_UNEMPLOYED');
        $personDegrees = array_merge($personDegrees, $this->personDegreeRepository->getByRegionAndType($region,'TYPE_SEARCH'));
        return new JsonResponse($this->createArrayPersonDegreeData($personDegrees));
    }

    #[Route(path: '/region/{id}/otherpersondegree', name: 'geolocation_map_region_other_persondegrees', methods: ['GET'])]
    public function getOtherPersonDegreesRegionAction(Request $request, Region $region): JsonResponse|Response {
        $personDegrees = $this->personDegreeRepository->getByRegionAndType($region,'TYPE_STUDY');
        $personDegrees = array_merge($personDegrees, $this->personDegreeRepository->getByRegionAndType($region,'TYPE_TRAINING'));
        // $personDegrees = array_merge($personDegrees, $this->personDegreeRepository->getByCountryAndType($country,'TYPE_EMPLOYED'));
        // $personDegrees = array_merge($personDegrees, $this->personDegreeRepository->getByCountryAndType($country,'TYPE_CONTRACTOR'));
        return new JsonResponse($this->createArrayPersonDegreeData($personDegrees));
    }

	/**
	 * Affichage des diplômes par pays
	 */
	#[Route(path: '/country/{id}/persondegree', name: 'geolocation_map_country_persondegrees', methods: ['GET'])]
	public function getPersonDegreesCountryAction(Request $request, Country $country): JsonResponse|Response {
		$personDegrees = $this->personDegreeRepository->getNameByCountry($country);
		return new JsonResponse($this->createArrayPersonDegreeData($personDegrees));
	}

	/**
	 * Affichage des diplômes par pays
	 */
	#[Route(path: '/country/{id}/unemployedpersondegree', name: 'geolocation_map_country_unemployed_persondegrees', methods: ['GET'])]
	public function getUnemployedPersonDegreesCountryAction(Request $request, Country $country): JsonResponse|Response {
		// $personDegrees = $this->personDegreeRepository->getByCountryAndType($country,'TYPE_SEARCH');
		$personDegrees = $this->personDegreeRepository->getByCountryAndType($country,'TYPE_UNEMPLOYED');
        $personDegrees = array_merge($personDegrees, $this->personDegreeRepository->getByCountryAndType($country,'TYPE_SEARCH'));
		return new JsonResponse($this->createArrayPersonDegreeData($personDegrees));
	}

    #[Route(path: '/country/{id}/otherpersondegree', name: 'geolocation_map_country_other_persondegrees', methods: ['GET'])]
    public function getOtherPersonDegreesCountryAction(Request $request, Country $country): JsonResponse|Response {
        $personDegrees = $this->personDegreeRepository->getByCountryAndType($country,'TYPE_STUDY');
        $personDegrees = array_merge($personDegrees, $this->personDegreeRepository->getByCountryAndType($country,'TYPE_TRAINING'));
        // $personDegrees = array_merge($personDegrees, $this->personDegreeRepository->getByCountryAndType($country,'TYPE_EMPLOYED'));
        // $personDegrees = array_merge($personDegrees, $this->personDegreeRepository->getByCountryAndType($country,'TYPE_CONTRACTOR'));
        return new JsonResponse($this->createArrayPersonDegreeData($personDegrees));
    }


	/**
	 * @param Company[] $companies
	 * @return array
	 */
	private function createArrayCompanyData(array $companies): array {
		$array = [];
		foreach ($companies as $company) {
			if (!$company->getMapsAddress()) {
				$company->setMapsAddress($this->createMapsAddress(
					$company->getAddressNumber(),
					$company->getAddressRoad(),
					$company->getAddressLocality(),
					$company->getCity()->getName(),
					$company->getRegion()->getName(),
					$company->getCountry()->getName()
				));
			}
            $sectorAreaName = "";
            if($company->getSectorArea())
                $sectorAreaName = $company->getSectorArea()->getName();
            $prefecture = "";
            if($company->getCity()->getPrefecture()) {
                $prefecture = $company->getCity()->getPrefecture()->getName();
            }

            $schools = $company->getSchools();
            $schoolsIds = "";
            for ($i = 0; $i<count($schools); $i++) {
                $schoolsIds .= $schools[$i]->getName();
                if($i < count($schools)-1) {
                    $schoolsIds .= ',';
                }
            }

			$array[] = [
				'type' => 'company',
				'id' => $company->getId(),
				'name' => $company->getName(),
				'phone' => $company->getPhoneStandard(),
				'email' => $company->getEmail(),
				'region' => $company->getRegion()->getName(),
                'prefecture' => $prefecture,
				'city' => $company->getCity()->getName(),
				'lat' => $company->getLatitude(),
				'lng' => $company->getLongitude(),
				'address' => $company->getMapsAddress(),
				'sector_area' => $sectorAreaName,
                'schools' => $schoolsIds
			];
		}
		return $array;
	}

	/**
	 * @param School[] $schools
	 * @return array
	 */
	private function createArraySchoolData(array $schools): array {
		$array = [];
		foreach ($schools as $school) {
			if (!$school->getMapsAddress()) {
				$school->setMapsAddress($this->createMapsAddress(
					$school->getAddressNumber(),
					$school->getAddressRoad(),
					$school->getAddressLocality(),
					$school->getCity()->getName(),
					$school->getRegion()->getName(),
					$school->getCountry()->getName()
				));
			}
			$sectorArea1 = "";
			if ($school->getSectorArea1() != null) {
				$sectorArea1 = $school->getSectorArea1()->getName();
			}
			$sectorArea2 = "";
			if ($school->getSectorArea2() != null) {
				$sectorArea2 = $school->getSectorArea2()->getName();
			}
			$sectorArea3 = "";
			if ($school->getSectorArea3() != null) {
				$sectorArea3 = $school->getSectorArea3()->getName();
			}
			$sectorArea4 = "";
			if ($school->getSectorArea4() != null) {
				$sectorArea4 = $school->getSectorArea4()->getName();
			}
            $sectorArea5 = "";
            if ($school->getSectorArea5() != null) {
                $sectorArea5 = $school->getSectorArea5()->getName();
            }
            $sectorArea6 = "";
            if ($school->getSectorArea6() != null) {
                $sectorArea6 = $school->getSectorArea6()->getName();
            }
			$activity1 = [];
			foreach ($school->getActivities1() as $activity) {
				$activity1[] = $activity->getName();
			}
			$activity2 = [];
			foreach ($school->getActivities2() as $activity) {
				$activity2[] = $activity->getName();
			}
			$activity3 = [];
			foreach ($school->getActivities3() as $activity) {
				$activity3[] = $activity->getName();
			}
			$activity4 = [];
			foreach ($school->getActivities4() as $activity) {
				$activity4[] = $activity->getName();
			}
            $activity5 = [];
            foreach ($school->getActivities5() as $activity) {
                $activity5[] = $activity->getName();
            }
            $activity6 = [];
            foreach ($school->getActivities6() as $activity) {
                $activity6[] = $activity->getName();
            }

			//sectorAreas pour infoWindows dans Maps
			$sectorAreas = $sectorArea1;
			if ($sectorArea2) {
				$sectorAreas .= ', ' . $sectorArea2;
			}
			if ($sectorArea3) {
				$sectorAreas .= ', ' . $sectorArea3;
			}
			if ($sectorArea4) {
				$sectorAreas .= ', ' . $sectorArea4;
			}
            if ($sectorArea5) {
                $sectorAreas .= ', ' . $sectorArea5;
            }
            if ($sectorArea6) {
                $sectorAreas .= ', ' . $sectorArea6;
            }

            //sectorAreas pour infoWindows dans Maps
            $activities = "";
            foreach ($activity1 as $activity) {
                $activities .= ', ' . $activity;
            }
            if (count($activity2)) {
                foreach ($activity2 as $activity) {
                    $activities .= ', ' . $activity;
                }
            }
            if (count($activity3)) {
                foreach ($activity3 as $activity) {
                    $activities .= ', ' . $activity;
                }
            }
            if (count($activity4)) {
                foreach ($activity4 as $activity) {
                    $activities .= ', ' . $activity;
                }
            }
            if (count($activity5)) {
                foreach ($activity5 as $activity) {
                    $activities .= ', ' . $activity;
                }
            }
            if (count($activity6)) {
                foreach ($activity6 as $activity) {
                    $activities .= ', ' . $activity;
                }
            }

            // $companies = $school->getCompanies();
            // $companiesIds = "";
            // for ($i = 0; $i<count($companies); $i++) {
            //     $companiesIds .= $companies[$i]->getId();
            //     if($i < count($companies)-1) {
            //         $companiesIds .= ',';
            //     }
            // }

            $prefecture = "";
            if($school->getCity()->getPrefecture()) {
                $prefecture = $school->getCity()->getPrefecture()->getName();
            }

			$array[] = [
				'type' => 'school',
				'name' => $school->getName(),
				'phone' => $school->getPhoneStandard(),
				'email' => $school->getEmail(),
				'region' => $school->getRegion()->getName(),
				'prefecture' => $prefecture,
				'city' => $school->getCity()->getName(),
				'lat' => $school->getLatitude(),
				'lng' => $school->getLongitude(),
				'address' => $school->getMapsAddress(),
				'sector_area' => $sectorAreas,
				'sector_area1' => $sectorArea1,
				'sector_area2' => $sectorArea2,
				'sector_area3' => $sectorArea3,
				'sector_area4' => $sectorArea4,
				'sector_area5' => $sectorArea5,
				'sector_area6' => $sectorArea6,
				'activity' => $activities,
				'activity1' => $activity1,
				'activity2' => $activity2,
				'activity3' => $activity3,
				'activity4' => $activity4,
				'activity5' => $activity5,
				'activity6' => $activity6,
                // 'companies' => $companiesIds,
			];
		}
		return $array;
	}

	/**
	 * @param PersonDegree[] $personDegrees
	 * @return array
	 */
	private function createArrayPersonDegreeData(array $personDegrees): array {
		$array = [];
		foreach ($personDegrees as $personDegree) {
            if ($personDegree->getAddressCity()) {
                if (!$personDegree->getMapsAddress()) {
                    $personDegree->setMapsAddress($this->createMapsAddress(
                        $personDegree->getAddressNumber(),
                        $personDegree->getAddressRoad(),
                        $personDegree->getAddressLocality(),
                        $personDegree->getAddressCity()->getName(),
                        $personDegree->getRegion()->getName(),
                        $personDegree->getCountry()->getName()
                    ));
                }
                $sectorArea = "";
                if ($personDegree->getSectorArea() != null) {
                    $sectorArea = $personDegree->getSectorArea()->getName();
                }
                $activity = "";
                if ($personDegree->getActivity() != null) {
                    $activity = $personDegree->getActivity()->getName();
                }

                $prefecture = "";
                if($personDegree->getAddressCity()->getPrefecture()) {
                    $prefecture = $personDegree->getAddressCity()->getPrefecture()->getName();
                }

                $school = "";
                if($personDegree->getSchool()) {
                    $school = $personDegree->getSchool()->getName();
                }

                $array[] = [
                    'type' => 'personDegree',
                    'school' => $school,
                    'name' => $personDegree->getName(),
                    'phone' => $personDegree->getPhoneMobile1(),
                    'email' => $personDegree->getEmail(),
                    'region' => $personDegree->getRegion()->getName(),
                    'prefecture' => $prefecture,
                    'city' => $personDegree->getAddressCity()->getName(),
                    'lat' => $personDegree->getLatitude(),
                    'lng' => $personDegree->getLongitude(),
                    'address' => $personDegree->getMapsAddress(),
                    'sector_area' => $sectorArea,
                    'activity' => $activity,
                ];
            }
        }
		return $array;
	}

	private function createMapsAddress($number, $road, $locality, $city, $region, $country): string {
		$address = "";
		if ($country) {
			if ($number) {
				$address .= $number . ",";
			}
			if ($road) {
				$address .= $road . ",";
			}
			if ($locality) {
				$address .= $locality . ",";
			}
			if ($city && ($locality != $city)) {
				$address .= $city . ",";
			}
			if (!$city && $region) {
				$address .= $region . ",";
			}
			if ($country != $region) {
				$address .= $country;
			}
		}
		return $address;
	}
}
