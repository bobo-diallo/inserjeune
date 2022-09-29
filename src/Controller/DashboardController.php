<?php

namespace App\Controller;

use App\Repository\CountryRepository;
use App\Repository\JobOfferRepository;
use App\Repository\RegionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/dashboard')]
#[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_LEGISLATEUR')")]
class DashboardController extends AbstractController {
	private EntityManagerInterface $em;
	private CountryRepository $countryRepository;
	private JobOfferRepository $jobOfferRepository;
	private RegionRepository $regionRepository;

	public function __construct(
		EntityManagerInterface $em,
		CountryRepository      $countryRepository,
		JobOfferRepository     $jobOfferRepository,
		RegionRepository       $regionRepository
	) {
		$this->em = $em;
		$this->countryRepository = $countryRepository;
		$this->jobOfferRepository = $jobOfferRepository;
		$this->regionRepository = $regionRepository;
	}

	#[Route(path: '/', name: 'dashboard_index', methods: ['GET'])]
	public function indexAction(Request $request): Response {
		$validCountries = $this->countryRepository->findBy(['valid' => 'true']);

		if (!$validCountries) {
			$validCountries = $this->countryRepository->findAll();
		} else {
			$userCountry = $this->getUser()->getCountry();
			if ($userCountry)
				$validCountries = $this->countryRepository->findById($userCountry);
		}
		$validRegions = [];
		foreach ($validCountries as $validCountry) {
			$regions = $this->regionRepository->findBy(['country' => $validCountry]);
			$validRegions = array_merge($validRegions, $regions);
		}

		$idSelectCountry = $request->request->get('selectCountry');
		if ($idSelectCountry) {
			$request->getSession()->set('pays', $idSelectCountry);
		}

		if (!$idSelectCountry) {
			if ($request->getSession()->has('pays')) {
				$idSelectCountry = $request->getSession()->get('pays');
			}
		}

		if (!$idSelectCountry) {
			foreach ($validCountries as $country)
				if ($country->isValid())
					$idSelectCountry = $country->getId();
		}

		$idSelectRegion = $request->request->get('selectRegion');
		if ($idSelectRegion) {
			$request->getSession()->set('region', $idSelectRegion);
		}

		return $this->render('Dashboard/index.html.twig', [
			'countries' => $validCountries,
			'regions' => $validRegions,
			'idSelectedCountry' => $idSelectCountry,
			'idSelectedRegion' => $idSelectRegion,
		]);

		// if (!$idSelectCountry) {
		// 	return $this->render('Dashboard/index.html.twig', [
		// 		'idSelectedRegion' => $idSelectRegion
		// 	]);
		//
		// } else {
		// 	return $this->render('Dashboard/index.html.twig', [
		// 		'countries' => $validCountries,
		// 		'regions' => $validRegions,
		// 		'idSelectedCountry' => $idSelectCountry,
		// 		'idSelectedRegion' => $idSelectRegion,
		// 	]);
		// }
	}

	#[Route(path: '/maps', name: 'dasboard_map', methods: ['GET'])]
	#[IsGranted('ROLE_ADMIN')]
	public function mapAction(Request $request): Response {
		return $this->render('Dashboard/maps.html.twig');
	}

	#[Route(path: '/degree', name: 'dasboard_degree', methods: ['GET'])]
	public function dashboardDegreeAction(Request $request): Response {
		return $this->render('Dashboard/dashboardDegree.html.twig', [
			'jobOffers' => $this->jobOfferRepository->findAll()
		]);
	}

	#[Route(path: '/company', name: 'dasboard_company', methods: ['GET'])]
	public function dashboardCompanyAction(Request $request): Response {
		return $this->render('Dashboard/index.html.twig');
	}
}
