<?php

namespace App\Controller;

use App\Entity\City;
use App\Form\CityType;
use App\Model\FlashBag\FlashBag;
use App\Repository\CountryRepository;
use App\Repository\RegionRepository;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\NamedRange;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
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

	#[Route(path: '/generate', name: 'city_generate_template', methods: ['GET'])]
	public function generateVilleTemplate(CountryRepository $countryRepository, RegionRepository $regionRepository): Response {
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$dataSheet = $spreadsheet->createSheet();
		$dataSheet->setTitle('Data');

		$sheet->setCellValue('A1', $this->translator->trans('menu.country'));
		$sheet->setCellValue('B1', $this->translator->trans('menu.region'));
		$sheet->setCellValue('C1', $this->translator->trans('menu.city'));

		$this->populateCountriesAndRegions($countryRepository, $regionRepository, $spreadsheet, $dataSheet);

		$this->applyDataValidations($sheet);

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

	private function populateCountriesAndRegions(
		CountryRepository $countryRepository,
		RegionRepository $regionRepository,
		Spreadsheet $spreadsheet,
		Worksheet $dataSheet
	): void {
		$countries = $countryRepository->findAll();

		$row = 1;
		$regionStartRow = 1;
		$countryMappings = [];

		foreach ($countries as $country) {
			$name = $country->getName();

			// Générer un nom sûr pour Excel (sans accents, espaces ou caractères spéciaux)
			$safeName = preg_replace('/[^A-Za-z0-9]/', '_', $name);
			$safeName = ltrim($safeName, '0123456789'); // S'assurer qu'il ne commence pas par un chiffre

			// Sauvegarder la correspondance entre nom original et nom sécurisé
			$countryMappings[$name] = $safeName;

			// Ajouter le nom original dans la colonne A
			$dataSheet->setCellValue('A' . $row, $name);

			$regionNames = $regionRepository->getRegionNamesByCountry((int) $country->getId());

			if (!empty($regionNames)) {
				$regionEndRow = $regionStartRow + count($regionNames) - 1;

				foreach ($regionNames as $index => $regionName) {
					$dataSheet->setCellValue('B' . ($regionStartRow + $index), $regionName);
				}

				// Créer une plage nommée avec le nom sécurisé
				if (ctype_alpha(substr($safeName, 0, 1))) { // Vérifie qu'il commence par une lettre
					$spreadsheet->addNamedRange(
						new NamedRange($safeName, $dataSheet, 'B' . $regionStartRow . ':B' . $regionEndRow)
					);
				}

				$regionStartRow = $regionEndRow + 1;
			}

			$row++;
		}

		// Ajouter une feuille cachée pour stocker les correspondances
		$this->createCountryMappingSheet($spreadsheet, $countryMappings);
	}

	private function createCountryMappingSheet(Spreadsheet $spreadsheet, array $countryMappings): void {
		$mappingSheet = new Worksheet($spreadsheet, 'Mappings');
		$spreadsheet->addSheet($mappingSheet);
		$mappingSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN); // Cache la feuille pour éviter les erreurs utilisateur

		$row = 1;
		foreach ($countryMappings as $originalName => $safeName) {
			$mappingSheet->setCellValue('A' . $row, $originalName);
			$mappingSheet->setCellValue('B' . $row, $safeName);
			$row++;
		}
	}

	private function applyDataValidations(Worksheet $sheet): void {
		$countryRange = 'Data!$A$1:$A$100'; // Ajuster selon le nombre de pays
		for ($i = 2; $i <= 100; $i++) {
			// Validation des pays
			$countryCell = 'A' . $i;

			$validation = $sheet->getCell($countryCell)->getDataValidation();
			$validation->setType(DataValidation::TYPE_LIST);
			$validation->setErrorStyle(DataValidation::STYLE_STOP);
			$validation->setAllowBlank(false);
			$validation->setShowDropDown(true);
			$validation->setFormula1($countryRange);
			$validation->setErrorTitle($this->translator->trans('clean.error'));
			$validation->setError('The value entered is not valid.');
			$validation->setPromptTitle('Choisir dans la liste');
			$validation->setPrompt('Veuillez choisir une valeur dans la liste.');
			$sheet->getCell($countryCell)->setDataValidation(clone $validation);

			// Validation des régions en fonction du pays
			$regionCell = 'B' . $i;

			$validation = $sheet->getCell($regionCell)->getDataValidation();
			$validation->setType(DataValidation::TYPE_LIST);
			$validation->setErrorStyle(DataValidation::STYLE_STOP);
			$validation->setAllowBlank(false);
			$validation->setShowDropDown(true);
			// Correction : Utilisation de RECHERCHEV pour trouver le nom sécurisé
			$validation->setFormula1('INDIRECT(VLOOKUP(' . $countryCell . ', Mappings!$A$1:$B$100, 2, FALSE))');
			$validation->setErrorTitle($this->translator->trans('clean.error'));
			$validation->setError('The value entered is not valid.');
			$validation->setPromptTitle('Choisir dans la liste');
			$validation->setPrompt('Veuillez choisir une valeur dans la liste.');
			$sheet->getCell($regionCell)->setDataValidation(clone $validation);
		}
	}

}
