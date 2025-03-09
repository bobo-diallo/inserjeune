<?php

namespace App\Controller;

use App\Entity\City;
use App\Form\CityType;
use App\Model\FlashBag\FlashBag;
use App\Repository\CountryRepository;
use App\Repository\RegionRepository;
use App\Repository\CityRepository;
use App\Services\EnrollmentTemplateService;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/city')]
// #[IsGranted('ROLE_ADMIN')]
#[Security("is_granted('ROLE_ADMIN') or 
            is_granted('ROLE_ADMIN_PAYS') or
            is_granted('ROLE_ADMIN_REGIONS') or
            is_granted('ROLE_ADMIN_VILLES') or
            is_granted('ROLE_DIRECTEUR')")]
class CityController extends AbstractController {
	private EntityManagerInterface $manager;
	private CityRepository $cityRepository;
	private RegionRepository $regionRepository;
	private TranslatorInterface $translator;

	public function __construct(
		EntityManagerInterface $manager,
		CityRepository $cityRepository,
		RegionRepository $regionRepository,
		TranslatorInterface $translator,
	) {
		$this->manager = $manager;
		$this->cityRepository = $cityRepository;
		$this->regionRepository = $regionRepository;
		$this->translator = $translator;
	}

	#[Route('/', name: 'city_index', methods: ['GET'])]
	public function indexAction(): Response {
		$cities = $this->cityRepository->findAll();

		if ($this->getUser()->hasRole('ROLE_ADMIN_PAYS')) {
			$cities = [];
			$userCountry = $this->getUser()->getCountry();
			$regions = $this->regionRepository->findByCountry($userCountry->getId());
			foreach ($regions as $region) {
				$cities = array_merge($cities, $this->cityRepository->findByRegion($region));
			}
		} else if ($this->getUser()->hasRole('ROLE_ADMIN_REGIONS')) {
			$cities = [];
			$regions = $this->getUser()->getAdminRegions();
			foreach ($regions as $region) {
				$cities = array_merge($cities, $this->cityRepository->findByRegion($region));
			}
		} else if ($this->getUser()->hasRole('ROLE_ADMIN_VILLES')) {
			$cities = $this->getUser()->getAdminCities();
		}

		return $this->render('city/index.html.twig', array(
			'cities' => $cities,
		));
	}

	/**
	 * @throws Exception
	 */
	#[Route(path: '/generate', name: 'city_generate_template', methods: ['GET'])]
	public function generateVilleTemplate(
		CountryRepository $countryRepository,
		RegionRepository $regionRepository,
		EnrollmentTemplateService $enrollmentTemplateService
	): Response {
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$dataSheet = $spreadsheet->createSheet();
		$dataSheet->setTitle($worksheetName = 'Data');

		$sheet->setCellValue('A1', $this->translator->trans('menu.country'));
		$sheet->setCellValue('B1', $this->translator->trans('menu.region'));
		$sheet->setCellValue('C1', $this->translator->trans('menu.city'));

		$enrollmentTemplateService->createColumnMappings(
			$spreadsheet,
			$regionRepository,
			$countryRepository,
			'A',
			'B',
			$worksheetName
		);

		$response = new StreamedResponse(function () use ($spreadsheet) {
			$writer = new Xlsx($spreadsheet);
			$writer->save('php://output');
		});

		$response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		$response->headers->set('Content-Disposition', 'attachment;filename="template_cities.xlsx"');
		$response->headers->set('Cache-Control', 'max-age=0');

		return $response;
	}

	#[Route(path: '/import', name: 'city_import', methods: ['POST'])]
	public function import(
		Request $request,
		CityRepository $cityRepository,
		RegionRepository $regionRepository,
		EntityManagerInterface $entityManager
	): Response {
		$file = $request->files->get('importFile');

		if (!$file) {
			$this->addFlash(FlashBag::TYPE_WARNING, 'No file uploaded.');
			return $this->redirectToRoute('region_index');
		}

		$spreadsheet = IOFactory::load($file->getPathname());
		$worksheet = $spreadsheet->getActiveSheet();

		foreach ($worksheet->getRowIterator(2) as $row) {
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false);

			$data = [];
			foreach ($cellIterator as $cell) {
				$data[] = $cell->getValue();
			}

			$regionName = $data[1];
			$cityName = $data[2];

			$region = $regionRepository->findOneBy(['name' => $regionName]);

			if (!$region) {
				$this->addFlash(FlashBag::TYPE_WARNING, "Region $regionName not exist.");
				continue;
			}

			$existingCity = $cityRepository->findOneBy(['name' => $cityName, 'region' => $region]);

			if (!$existingCity) {
				$city = new City();
				$city->setName($cityName);
				$city->setRegion($region);
				$city->setIsCapital(false);
				$entityManager->persist($city);
			}
		}

		$entityManager->flush();

		$this->addFlash(FlashBag::TYPE_SUCCESS, 'Cities imported successfully.');
		return $this->redirectToRoute('city_index');
	}

	#[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_ADMIN_PAYS')")]
	#[Route('/new', name: 'city_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$city = new City();
		$form = $this->createForm(CityType::class, $city);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->manager->persist($city);
			$this->manager->flush();

			return $this->redirectToRoute('city_show', array('id' => $city->getId()));
		}

		return $this->render('city/new.html.twig', array(
			'city' => $city,
			'form' => $form->createView(),
		));
	}

	#[Security("is_granted('ROLE_ADMIN') or  is_granted('ROLE_ADMIN_PAYS')")]
	#[Route('/{id}', name: 'city_show', methods: ['GET'])]
	public function showAction(City $city): Response {
		return $this->render('city/show.html.twig', array(
			'city' => $city,
		));
	}

	#[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_ADMIN_PAYS')")]
	#[Route('/{id}/edit', name: 'city_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request, City $city): RedirectResponse|Response {
		$editForm = $this->createForm(CityType::class, $city);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$this->manager->flush();

			return $this->redirectToRoute('city_show', ['id' => $city->getId()]);
		}

		return $this->render('city/edit.html.twig', array(
			'city' => $city,
			'edit_form' => $editForm->createView(),
		));
	}

	#[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_ADMIN_PAYS')")]
	#[Route('/delete/{id}', name: 'city_delete', methods: ['GET'])]
	public function deleteAction(Request $request, ?City $city): RedirectResponse {
		if (array_key_exists('HTTP_REFERER', $request->server->all())) {
			if ($city) {
				$this->manager->remove($city);
				$this->manager->flush();
				$this->addFlash('success', $this->translator->trans('flashbag.the_deletion_is_done_successfully'));
			} else {
				$this->addFlash('warning', $this->translator->trans('flashbag.unable_to_delete_the_country'));
				return $this->redirect($request->server->all()['HTTP_REFERER']);
			}
		}
		return $this->redirectToRoute('city_index');
	}

}
