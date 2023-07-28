<?php

namespace App\Controller;

use App\Repository\CountryRepository;
use App\Repository\JobOfferRepository;
use App\Repository\RegionRepository;
use App\Services\DashboardService;
use App\Services\CompanyService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/dashboard')]
#[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_LEGISLATEUR') or is_granted('ROLE_ETABLISSEMENT') or is_granted('ROLE_ENTREPRISE') or is_granted('ROLE_DIPLOME')")]
class DashboardController extends AbstractController {
	private EntityManagerInterface $em;
	private CountryRepository $countryRepository;
	private JobOfferRepository $jobOfferRepository;
	private RegionRepository $regionRepository;
	private DashboardService $dashboardService;
    private CompanyService $companyService;
	private TranslatorInterface $translator;

	public function __construct(
		EntityManagerInterface $em,
		CountryRepository      $countryRepository,
		JobOfferRepository     $jobOfferRepository,
		RegionRepository       $regionRepository,
		DashboardService       $dashboardService,
        CompanyService         $companyService,
		TranslatorInterface $translator
	) {
		$this->em = $em;
		$this->countryRepository = $countryRepository;
		$this->jobOfferRepository = $jobOfferRepository;
		$this->regionRepository = $regionRepository;
		$this->dashboardService = $dashboardService;
        $this->companyService = $companyService;
		$this->translator = $translator;
	}

	#[Route(path: '/', name: 'dashboard_index', methods: ['GET', 'POST'])]
	public function indexAction(Request $request): Response {
        if ($this->getUser()->getCompany()) {
            if(!$this->companyService->checkSatisfaction($this->getUser()->getCompany()))
                return $this->redirectToRoute('front_company_satisfactioncompany_new');
        }
		return $this->dashboardService->checkAccountBefore(function () use ($request) {
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

			// Récupération de la date de référence pour l'intervalle de temps, par défaut = date courante
			$referenceDateTxt =  $request->request->get('referenceDate');
			$session = $request->getSession();

			if (!$referenceDateTxt && $session->has('referenceDate')) {
				$referenceDateTxt = $session->get('referenceDate');
			}

			if (!$referenceDateTxt) {
				$referenceDateTxt = (new DateTime())->format('Y-m-d');
			}
			$session->set('referenceDate', $referenceDateTxt);
			$referenceDate = (new \DateTime($referenceDateTxt))->format('Y-m-d');

			// Récupération de l'intervalle de temps sélectionné par le formulaire et renvoie la date du premier jour  */
			$selectedDuration = $request->request->get('selectDuration');
			if ($selectedDuration) {
				$session->set('selectDuration', $selectedDuration);
			}

			// Sinon récupération par la variable de session, sinon paramétrage à 3 mois
			if (!$selectedDuration) {
				$selectedDuration = ($session->has('selectDuration')) ? $session->get('selectDuration') : '2 ans';
			}

			$idSelectCountry = $request->request->get('selectCountry');
			if ($idSelectCountry) {
				$session->set('pays', $idSelectCountry);
			} else {
				if ($session->has('pays')) {
					$idSelectCountry = $session->get('pays');
				}
			}

			if (!$idSelectCountry) {
				foreach ($validCountries as $country) {
					if ($country->isValid()) {
						$idSelectCountry = $country->getId();
					}
				}
			}

			$idSelectRegion = $request->request->get('selectRegion');
			if ($idSelectRegion) {
				$session->set('region', $idSelectRegion);
			}

			return $this->render('Dashboard/index.html.twig', [
				'countries' => $validCountries,
				'regions' => $validRegions,
				'idSelectedCountry' => $idSelectCountry,
				'idSelectedRegion' => $idSelectRegion,
				'selectedDuration'  => $selectedDuration,
				'referenceDate' => $referenceDate,
			]);
		});
	}

	#[Route(path: '/export_pdf', name: 'export_pdf_index', methods: ['GET'])]
	public function exportPdfAction(): Response {
		return $this->redirectToRoute('dashboard_index');
	}
}
