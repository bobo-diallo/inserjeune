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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Tests\Globals\IntlGlobalsTest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/geolocation')]
class GeoLocationController extends AbstractController {

	private EntityManagerInterface $em;
	private CompanyRepository $companyRepository;
	private SchoolRepository $schoolRepository;
	private PersonDegreeRepository $personDegreeRepository;

	public function __construct(
		EntityManagerInterface $em,
		CompanyRepository      $companyRepository,
		SchoolRepository       $schoolRepository,
		PersonDegreeRepository $personDegreeRepository
	) {
		$this->em = $em;
		$this->companyRepository = $companyRepository;
		$this->schoolRepository = $schoolRepository;
		$this->personDegreeRepository = $personDegreeRepository;
	}

	#[Route(path: '/', name: 'geolocation', methods: ['GET', 'POST'])]
	public function indexAction(Request $request): Response {
		$geoLocation = new GeoLocation();
		$selectedCountry = $this->getUser()->getCountry();

		if ($selectedCountry) {
			$form = $this->createForm(GeoLocationType::class, $geoLocation, ['selectedCountry' => $selectedCountry->getId()]);
		} else {
			$form = $this->createForm(GeoLocationType::class, $geoLocation, ['selectedCountry' => ""]);
		}
		$form->handleRequest($request);

		return $this->render('GeoLocation/maps.html.twig', [
			'form' => $form->createView(),
			'selectedCountry' => $selectedCountry
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
	 * Affichage des entreprises par region
	 */
	#[Route(path: '/region/{id}/school', name: 'geolocation_map_region_schools', methods: ['GET'])]
	public function getSchoolsRegionAction(Request $request, Region $region): JsonResponse|Response {
		$schools = $this->schoolRepository->getNameByRegion($region);
		return new JsonResponse($this->createArraySchoolData($schools));
	}

	/**
	 * Affichage des entreprise par pays
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

	/**
	 * Affichage des diplômes par pays
	 */
	#[Route(path: '/country/{id}/persondegree', name: 'geolocation_map_country_persondegrees', methods: ['GET'])]
	public function getPersonDegreesCountryAction(Request $request, Country $country): JsonResponse|Response {
		$personDegrees = $this->personDegreeRepository->getNameByCountry($country);
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
			$array[] = [
				'type' => 'company',
				'name' => $company->getName(),
				'phone' => $company->getPhoneStandard(),
				'email' => $company->getEmail(),
				'region' => $company->getRegion()->getName(),
				'city' => $company->getCity()->getName(),
				'lat' => $company->getLatitude(),
				'lng' => $company->getLongitude(),
				'address' => $company->getMapsAddress(),
				'sector_area' => $company->getSectorArea()->getName()
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

			//sectorAreas pour infoWindows dans Maps
			$sectorAreas = $sectorArea1;
			if (sizeof($sectorArea2) > 0) {
				$sectorAreas .= ', ' . $sectorArea2;
			}
			if ($sectorArea3) {
				$sectorAreas .= ', ' . $sectorArea3;
			}
			if ($sectorArea4) {
				$sectorAreas .= ', ' . $sectorArea4;
			}

			$array[] = [
				'type' => 'school',
				'name' => $school->getName(),
				'phone' => $school->getPhoneStandard(),
				'email' => $school->getEmail(),
				'region' => $school->getRegion()->getName(),
				'city' => $school->getCity()->getName(),
				'lat' => $school->getLatitude(),
				'lng' => $school->getLongitude(),
				'address' => $school->getMapsAddress(),
				'sector_area' => $sectorAreas,
				'sector_area1' => $sectorArea1,
				'sector_area2' => $sectorArea2,
				'sector_area3' => $sectorArea3,
				'sector_area4' => $sectorArea4,
				'activity1' => $activity1,
				'activity2' => $activity2,
				'activity3' => $activity3,
				'activity4' => $activity4,
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

			$array[] = [
				'type' => 'personDegree',
				'name' => $personDegree->getName(),
				'phone' => $personDegree->getPhoneMobile1(),
				'email' => $personDegree->getEmail(),
				'region' => $personDegree->getRegion()->getName(),
				'city' => $personDegree->getAddressCity()->getName(),
				'lat' => $personDegree->getLatitude(),
				'lng' => $personDegree->getLongitude(),
				'address' => $personDegree->getMapsAddress(),
				'sector_area' => $sectorArea,
				'activity' => $activity,
			];
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
