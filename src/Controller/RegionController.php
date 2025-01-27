<?php

namespace App\Controller;

use App\Entity\Region;
use App\Form\RegionType;
use App\Model\FlashBag\FlashBag;
use App\Repository\CountryRepository;
use App\Repository\RegionRepository;
use App\Repository\CurrencyRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/region')]
// #[IsGranted('ROLE_ADMIN')]
#[Security("is_granted('ROLE_ADMIN') or 
            is_granted('ROLE_ADMIN_PAYS') or
            is_granted('ROLE_DIRECTEUR')")]
class RegionController extends AbstractController {
	private EntityManagerInterface $em;
	private RegionRepository $regionRepository;
	private CurrencyRepository $currencyRepository;
	private TranslatorInterface $translator;

	public function __construct(
		EntityManagerInterface $em,
		RegionRepository       $regionRepository,
        CurrencyRepository       $currencyRepository,
		TranslatorInterface $translator
	) {
		$this->em = $em;
		$this->regionRepository = $regionRepository;
		$this->currencyRepository = $currencyRepository;
		$this->translator = $translator;
	}

	#[Route(path: '/', name: 'region_index', methods: ['GET'])]
	public function indexAction(): Response {
        //adaptation dbta: mise à jour des currency_id pour les regions importées
        $regions = $this->regionRepository->findAll();
        if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'true') {
            foreach ($regions as $region) {
                if(!$region->getCurrency()) {
                    $region->setCurrency($region->getCountry()->getCurrency());
                    if ($region->getCurrency()) {
                        $this->em->persist($region);
                        $this->em->flush();
                    }
                }
            }
        }
        if ($this->getUser()->hasRole('ROLE_ADMIN_PAYS')) {
            $userCountry = $this->getUser()->getCountry();
            $regions =  $this->regionRepository->findByCountry($userCountry->getId());
        } else if ($this->getUser()->hasRole('ROLE_ADMIN_REGIONS')) {
            $regions =  $this->getUser()->getAdminRegions();
        }

		return $this->render('region/index.html.twig', array(
			'regions' => $regions,
		));
	}

	#[Route(path: '/generate', name: 'region_generate_template', methods: ['GET'])]
	public function generateExcelTemplate(CountryRepository $countryRepository): Response
	{
		$countries = $countryRepository->getCountriesByName();

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();

		$sheet->setCellValue('A1', $this->translator->trans('menu.country'));
		$sheet->setCellValue('B1', $this->translator->trans('menu.region'));

		for ($row = 2; $row <= 100; $row++) {
			$countryValidation = $sheet->getCell('A' . $row)->getDataValidation();
			$countryValidation->setType(DataValidation::TYPE_LIST);
			$countryValidation->setErrorStyle(DataValidation::STYLE_INFORMATION);
			$countryValidation->setAllowBlank(false);
			$countryValidation->setShowDropDown(true);
			$countryValidation->setFormula1(sprintf('"%s"', implode(',', $countries)));
			$sheet->getCell('A' . $row)->setDataValidation($countryValidation);
		}

		$response = new StreamedResponse(function() use ($spreadsheet) {
			$writer = new Xlsx($spreadsheet);
			$writer->save('php://output');
		});

		$response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		$response->headers->set('Content-Disposition', 'attachment;filename="template_regions.xlsx"');
		$response->headers->set('Cache-Control', 'max-age=0');

		return $response;
	}

	#[Route(path: '/import', name: 'region_import', methods: ['POST'])]
	public function import(
		Request $request,
		CountryRepository $countryRepository,
		RegionRepository $regionRepository,
		EntityManagerInterface $entityManager
	): Response
	{
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

			$countryName = $data[0];
			$regionName = $data[1];

			$country = $countryRepository->findOneBy(['name' => $countryName]);

			if (!$country) {
				$this->addFlash(FlashBag::TYPE_WARNING, "Country $countryName not exist.");
				continue;
			}

			$existingRegion = $regionRepository->findOneBy(['name' => $regionName, 'country' => $country]);

			if (!$existingRegion) {
				$region = new Region();
				$region->setName($regionName);
				$region->setCountry($country);
				$region->setValid(true);
				$region->setPhoneDigit(0);
				$region->setPhoneDigit(0);
				$entityManager->persist($region);
			}
		}

		$entityManager->flush();

		$this->addFlash(FlashBag::TYPE_SUCCESS, 'Regions imported successfully.');
		return $this->redirectToRoute('region_index');
	}

    #[IsGranted('ROLE_ADMIN')]
	#[Route(path: '/new', name: 'region_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$region = new Region();
        $form = $this->createForm(RegionType::class, $region);
        if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'true') {
            $form = $this->createForm(RegionType::class, $region);
        }
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->em->persist($region);
			$this->em->flush();

			return $this->redirectToRoute('region_show', ['id' => $region->getId()]);
		}

		return $this->render('region/new.html.twig', [
			'region' => $region,
			'form' => $form->createView(),
		]);
	}
    #[IsGranted('ROLE_ADMIN')]
	#[Route(path: '/{id}', name: 'region_show', methods: ['GET'])]
	public function showAction(Region $region): Response {
		return $this->render('region/show.html.twig', [
			'region' => $region
		]);
	}
    #[IsGranted('ROLE_ADMIN')]
	#[Route(path: '/{id}/edit', name: 'region_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request, Region $region): RedirectResponse|Response {

        $editForm = $this->createForm(RegionType::class, $region);
        if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'true') {
            $editForm = $this->createForm(RegionType::class, $region);
        }
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$this->em->flush();

			return $this->redirectToRoute('region_show', array('id' => $region->getId()));
		}

		return $this->render('region/edit.html.twig', [
			'region' => $region,
			'edit_form' => $editForm->createView(),
		]);
	}
    #[IsGranted('ROLE_ADMIN')]
	#[Route(path: '/delete/{id}', name: 'region_delete', methods: ['GET'])]
	public function deleteAction(Request $request, ?Region $region): RedirectResponse {
		if (array_key_exists('HTTP_REFERER', $request->server->all())) {
			if ($region) {
				$this->em->remove($region);
				$this->em->flush();
				$this->addFlash('success', $this->translator->trans('flashbag.the_deletion_is_done_successfully'));
			} else {
				$this->addFlash('warning', $this->translator->trans('flashbag.unable_to_delete_city'));
				return $this->redirect($request->server->all()['HTTP_REFERER']);
			}
		}
		return $this->redirectToRoute('region_index');
	}

}
