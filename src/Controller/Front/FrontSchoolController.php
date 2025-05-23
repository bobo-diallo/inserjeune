<?php

namespace App\Controller\Front;

use App\Entity\Degree;
use App\Entity\PersonDegree;
use App\Entity\Company;
use App\Entity\Region;
use App\Entity\Country;
use App\Entity\School;
use App\Entity\SectorArea;
use App\Form\SchoolType;
use App\Repository\CompanyRepository;
use App\Repository\SchoolRepository;
use App\Repository\PersonDegreeRepository;
use App\Repository\SatisfactionSalaryRepository;
use App\Repository\UserRepository;
use App\Repository\RoleRepository;
use App\Repository\CountryRepository;
use App\Repository\RegionRepository;
use App\Repository\CityRepository;
use App\Repository\DegreeRepository;
use App\Repository\LegalStatusRepository;
use App\Repository\SectorAreaRepository;
use App\Repository\ActivityRepository;
use App\Services\ActivityService;
use App\Services\EmailService;
use App\Services\EnrollmentTemplateService;
use App\Services\PersonDegreeDatatableService;
use App\Services\SchoolService;
use App\Services\PersonDegreeService;
use App\Tools\Utils;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\NamedRange;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use ReflectionClass;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: 'front/school')]
#[Security("is_granted('ROLE_ETABLISSEMENT') or 
            is_granted('ROLE_PRINCIPAL')")]

class FrontSchoolController extends AbstractController {
	private EntityManagerInterface $em;
	private ActivityService $activityService;
	private SchoolService $schoolService;
    private PersonDegreeService $degreeService;
	private CompanyRepository $companyRepository;
	private SatisfactionSalaryRepository $satisfactionSalaryRepository;
	private PersonDegreeRepository $personDegreeRepository;
	private UserRepository $userRepository;
	private RoleRepository $roleRepository;
	private RegionRepository $regionRepository;
	private CountryRepository $countryRepository;
	private CityRepository $cityRepository;
	private DegreeRepository $degreeRepository;
	private LegalStatusRepository $legalStatusRepository;
	private SectorAreaRepository $sectorAreaRepository;
	private ActivityRepository $activityRepository;
    private UserPasswordHasherInterface $hasher;
	private EmailService $emailService;
    private SchoolRepository $schoolRepository;
	private TokenStorageInterface $tokenStorage;
	private TranslatorInterface $translator;

	public function __construct(
		EntityManagerInterface       $em,
		ActivityService              $activityService,
		SchoolService                $schoolService,
        PersonDegreeService          $degreeService,
		CompanyRepository            $companyRepository,
		UserPasswordHasherInterface  $hasher,
		SatisfactionSalaryRepository $satisfactionSalaryRepository,
		PersonDegreeRepository       $personDegreeRepository,
		UserRepository        $userRepository,
		RoleRepository        $roleRepository,
		RegionRepository      $regionRepository,
		CountryRepository     $countryRepository,
		CityRepository        $cityRepository,
		DegreeRepository      $degreeRepository,
		LegalStatusRepository $legalStatusRepository,
		SectorAreaRepository  $sectorAreaRepository,
		ActivityRepository    $activityRepository,
		EmailService          $emailService,
		SchoolRepository      $schoolRepository,
		TokenStorageInterface $tokenStorage,
		TranslatorInterface $translator
	) {
		$this->em = $em;
		$this->activityService = $activityService;
		$this->schoolService = $schoolService;
        $this->degreeService = $degreeService;
		$this->companyRepository = $companyRepository;
        $this->hasher = $hasher;
		$this->satisfactionSalaryRepository = $satisfactionSalaryRepository;
		$this->personDegreeRepository = $personDegreeRepository;
		$this->userRepository = $userRepository;
		$this->roleRepository = $roleRepository;
		$this->regionRepository = $regionRepository;
		$this->countryRepository = $countryRepository;
		$this->cityRepository = $cityRepository;
		$this->degreeRepository = $degreeRepository;
		$this->legalStatusRepository = $legalStatusRepository;
		$this->sectorAreaRepository = $sectorAreaRepository;
		$this->activityRepository = $activityRepository;
		$this->emailService = $emailService;
        $this->schoolRepository = $schoolRepository;
		$this->tokenStorage = $tokenStorage;
		$this->translator = $translator;
	}
    #[IsGranted('ROLE_ETABLISSEMENT')]
	#[Route(path: '/new', name: 'front_school_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$school = new School();
		/** @var User $user */
		$user = $this->getUser();
		$school->setEmail($user->getEmail());
		$school->setPhoneStandard($user->getPhone());
		$school->setCountry($user->getCountry());
		$school->setLocationMode(true);

		$form = $this->createForm(SchoolType::class, $school);
		$form->handleRequest($request);

		$selectedCountry = $this->getUser()->getCountry();
        //adaptation for DBTA
        $selectedRegion = null;
        if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'true') {
            $selectedRegion = $this->getUser()->getRegion();
            $school->setRegion($selectedRegion);
        }

		if ($form->isSubmitted() && $form->isValid()) {
			$agreeRgpd = $form->get('agreeRgpd')->getData();
			if ($agreeRgpd) {
				$school->setCreatedDate(new DateTime());
				$school->setUpdatedDate(new DateTime());
				$school->setUser($user);
				$school->setPhoneStandard($user->getPhone());

				$this->em->persist($school);
				$this->em->flush();

				return $this->redirectToRoute('front_school_show');
			}
		}

		return $this->render('school/new.html.twig', [
			'school' => $school,
			'form' => $form->createView(),
			'allActivities' => $this->activityService->getAllActivities(),
			'selectedCountry' => $selectedCountry,
			'selectedRegion' => $selectedRegion
		]);
	}

	#[Route(path: '/', name: 'front_school_show', methods: ['GET'])]
	public function showAction(): Response {
		return $this->schoolService->checkUnCompletedAccountBefore(function () {
			$school = $this->schoolService->getSchool();
            if(!$school) {
                if($this->getUser()->getPrincipalSchool()) {
                    $school = $this->schoolRepository->find($this->getUser()->getPrincipalSchool());
                }
            }
			if (!$school) {
				return $this->redirectToRoute('front_school_new');
			}

			return $this->render('school/show.html.twig', ['school' => $school]);
		});
	}
    #[IsGranted('ROLE_ETABLISSEMENT')]
	#[Route(path: '/edit', name: 'front_school_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request): RedirectResponse|Response {
		return $this->schoolService->checkUnCompletedAccountBefore(function () use ($request) {
			$school = $this->schoolService->getSchool();
			if (!$school) {
				return $this->redirectToRoute('front_school_new');
			}

			$createdDate = $school->getCreatedDate();
			$editForm = $this->createForm(SchoolType::class, $school);
			$editForm->handleRequest($request);

			$selectedCountry = $this->getUser()->getCountry();
            //adaptation for DBTA
            $selectedRegion = null;
            if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'true') {
                $selectedRegion = $this->getUser()->getRegion();
            }

			if ($editForm->isSubmitted() && $editForm->isValid()) {
				$agreeRgpd = $editForm->get('agreeRgpd')->getData();
				if ($agreeRgpd) {
					$school->setCreatedDate($createdDate);

					if ($school->getCreatedDate() == null) {
						if ($school->getUpdatedDate()) {
							$school->setCreatedDate($school->getUpdatedDate());
						} else {
							$school->setCreatedDate(new DateTime());
						}
					}

					$school->setUpdatedDate(new DateTime());
					$school->setUser($this->getUser());
					if (!$this->getUser()->getApiToken()) {
						$this->getUser()->setApiToken(bin2hex(random_bytes(32)));
					}
					$this->em->flush();

					return $this->redirectToRoute('front_school_show');
				} else if (count($school->getPersonDegrees()) == 0) {
					return $this->redirectToRoute('front_school_user_delete_school', array('id' => $school->getId()));
				}
			}

			return $this->render('school/edit.html.twig', [
				'school' => $school,
				'edit_form' => $editForm->createView(),
				'allActivities' => $this->activityService->getAllActivities(),
				'selectedCountry' => $selectedCountry,
				'selectedRegion' => $selectedRegion
			]);
		});
	}
    #[IsGranted('ROLE_ETABLISSEMENT')]
	#[Route(path: '/companies', name: 'front_school_company_index', methods: ['GET'])]
	public function companiesIndexAction(): Response {
		return $this->schoolService->checkUnCompletedAccountBefore(function () {
			$companies = $this
				->companyRepository
				->getBySchool($this->schoolService->getSchool());

			return $this->render('company/index.html.twig', [
				'companies' => $companies
			]);
		});
	}
    #[IsGranted('ROLE_ETABLISSEMENT')]
	#[Route(path: '/all_companies', name: 'front_school_all_company_index', methods: ['GET'])]
	public function allCompaniesIndexAction(): Response {
		return $this->schoolService->checkUnCompletedAccountBefore(function () {
			$school = $this->schoolService->getSchool();

			$allCompanies = $this->companyRepository->findByCountry($school->getCountry());
			$selectedCompanies = $this->companyRepository->getBySchool($school);

			return $this->render('company/index.html.twig', [
				'companies' => $allCompanies,
				'selectedCompanies' => $selectedCompanies,
			]);
		});
	}
    #[IsGranted('ROLE_ETABLISSEMENT')]
	#[Route(path: '/persondegrees_companies', name: 'front_school_persondegrees_company_index', methods: ['GET'])]
	public function persondegreesCompaniesIndexAction(): Response {
		return $this->schoolService->checkUnCompletedAccountBefore(function () {
			$school = $this->schoolService->getSchool();

			$allCompanies = $this->companyRepository->findByCountry($school->getCountry());
			$satisfactionSalaries = $this->satisfactionSalaryRepository->getByCountryAndPersonDegreeSchool($school->getCountry(), $school);

			// creation des entreprises trouvées dans les satisfactions
			$employers = [];
			foreach ($satisfactionSalaries as $satisfactionSalary) {
				$newEmployer = array();
				$newEmployer["name"] = $satisfactionSalary->getCompanyName();
				$newEmployer["city"] = $satisfactionSalary->getCompanyCity();
				$newEmployer["phone"] = $satisfactionSalary->getCompanyPhone();
				$newEmployer["NbPersonDegrees"] = 1;

				// compte les employés de l'entreprise
				$employerExist = false;
				for ($i = 0; $i < count($employers); $i++) {
					if (($employers[$i]["name"] == $satisfactionSalary->getCompanyName()) &&
						($employers[$i]["city"] == $satisfactionSalary->getCompanyCity()) &&
						($employers[$i]["phone"] == $satisfactionSalary->getCompanyPhone())) {
						$employers[$i]["NbPersonDegrees"]++;
						$employerExist = true;
					}
				}

				if (!$employerExist) {
					$employers[] = $newEmployer;
				}
			}

			return $this->render('school/employers.html.twig', [
				'companies' => $allCompanies,
				'employers' => $employers,
			]);
		});
	}
    // #[IsGranted('ROLE_ETABLISSEMENT')]
	// #[Route(path: '/persondegrees', name: 'front_school_persondegree_index', methods: ['GET'])]
	// public function personDegreesIndexAction(Request $request): Response {
	// 	return $this->schoolService->checkUnCompletedAccountBefore(function () use ($request) {
	// 		$school = $this->schoolService->getSchool();
	// 		$schoolId = $school?->getId();
	// 		$personDegrees = $this->personDegreeRepository->getAllPersonDegree(
	// 			null,
	// 			null,
	// 			$schoolId
	// 		);
    //         $types = $this->degreeService->getTypes();
	//
	// 		return $this->render('persondegree/index.html.twig', [
	// 			'personDegrees' => $personDegrees,
    //             'types' => $types
	// 		]);
	// 	});
	// }

	#[IsGranted('ROLE_ETABLISSEMENT')]
	#[Route(path: '/persondegrees', name: 'front_school_persondegree_index', methods: ['GET', 'POST'])]
	public function indexDatatableAction(Request $request, PersonDegreeDatatableService $datatableService): Response {
		return $this->schoolService->checkUnCompletedAccountBefore(function () use ($request, $datatableService) {
			/** @var User $currentUser */
			$currentUser = $this->getUser();
			$school = $this->schoolService->getSchool();
			$schoolId = $school?->getId();

			$table = $datatableService->generateDatatable($request, $currentUser, $schoolId);
			$table->handleRequest($request);

			if ($table->isCallback()) {
				return $table->getResponse();
			}

			return $this->render('persondegree/index_datatable.html.twig', [
				'datatable' => $table,
				'types' => $this->degreeService->getTypes()
			]);
		});
	}

	#[IsGranted('ROLE_ETABLISSEMENT')]
	#[Route(path: '/persondegreesEnroll', name: 'front_school_persondegrees_enroll', methods: ['GET'])]
	public function personDegreesEnrollAction(): Response {
		return $this->schoolService->checkUnCompletedAccountBefore(function () {
			$school = $this->schoolService->getSchool();
			$selectedCountry = $this->getUser()->getCountry();

			$regions = $this->regionRepository->findByCountry($selectedCountry);
			$personDegrees = $this->personDegreeRepository->getBySchoolAndByUnlocked($school, true);

			return $this->render('school/personDegreesEnroll.html.twig', [
				'personDegrees' => $personDegrees,
				'regions' => $regions,
			]);
		});
	}

	#[IsGranted('ROLE_ETABLISSEMENT')]
	#[Route(path: '/persondegreesEnroll/generate', name: 'front_school_enroll_generate_template', methods: ['GET'])]
	public function generatePersonDegreeEnrolmentTemplate(
		RegionRepository $regionRepository,
		CityRepository $cityRepository,
		ActivityRepository $activityRepository,
		SectorAreaRepository $sectorAreaRepository,
		EnrollmentTemplateService $enrollmentTemplateService
	): Response {
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$dataSheet = $spreadsheet->createSheet();
		$dataSheet->setTitle($worksheetName = 'Data');

		/** @var User $currentUser */
		$currentUser = $this->getUser();
		/** @var School $school */
		$school = $currentUser->getSchool();

		$sheet->setCellValue('A1', $this->translator->trans('js.import_csv_registration'));
		$sheet->setCellValue('B1', $this->translator->trans('js.import_csv_first_name'));
		$sheet->setCellValue('C1', $this->translator->trans('js.import_csv_name'));
		$sheet->setCellValue('D1', $this->translator->trans('js.import_csv_birthday'));
		$sheet->setCellValue('E1', $this->translator->trans('js.import_csv_gender'));
		$sheet->setCellValue('F1', $this->translator->trans('js.import_csv_region'));
		$sheet->setCellValue('G1', $this->translator->trans('js.import_csv_city'));
		$sheet->setCellValue('H1', $this->translator->trans('js.import_csv_mobile_phone'));
		$sheet->setCellValue('I1', $this->translator->trans('js.import_csv_parent_mobile_phone'));
		$sheet->setCellValue('J1', $this->translator->trans('js.import_csv_email'));
		$sheet->setCellValue('K1', $this->translator->trans('js.import_csv_diploma'));
		$sheet->setCellValue('L1', $this->translator->trans('js.import_csv_sector'));
		$sheet->setCellValue('M1', $this->translator->trans('js.import_csv_subsector'));

		$this->setTextColumnFormat($sheet, 'H');
		$this->setTextColumnFormat($sheet, 'I');
		$this->setTextColumnFormat($sheet, 'J');

		$degrees = $school->getDegrees()->map(function (Degree $degree) {
			return $this->translator->trans($degree->getName());
		})->toArray();

		// Region & city
		$enrollmentTemplateService->createColumnMappings(
			$spreadsheet,
			$cityRepository,
			$regionRepository,
			'F',
			'G',
			$worksheetName
		);

		// Sector & activity
		$enrollmentTemplateService->createColumnMappings(
			$spreadsheet,
			$activityRepository,
			$sectorAreaRepository,
			'L',
			'M',
			$worksheetName
		);

		$this->_columnTemplateExcelValidation($sheet, $dataSheet, 'E', [$this->translator->trans('menu.a_man'), $this->translator->trans('menu.a_woman')]);
		$this->_columnTemplateExcelValidation($sheet, $dataSheet, 'K', $degrees);


		$response = new StreamedResponse(function () use ($spreadsheet) {
			$writer = new Xlsx($spreadsheet);
			$writer->save('php://output');
		});

		$response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		$response->headers->set('Content-Disposition', 'attachment;filename="person_degree_enrollment_template.xlsx"');
		$response->headers->set('Cache-Control', 'max-age=0');

		return $response;
	}

	private function setTextColumnFormat(Worksheet $sheet, string $column, int $startRow = 2, int $endRow = 100): void {
		for ($row = $startRow; $row <= $endRow; $row++) {
			$cell = $column . $row;
			$sheet->getStyle($cell)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
			$sheet->setCellValueExplicit($cell, '', DataType::TYPE_STRING);
		}
	}

	/**
	 * @throws Exception
	 */
	#[IsGranted('ROLE_ETABLISSEMENT')]
	#[Route(path: '/companiesEnroll/generate', name: 'front_school_company_enroll_generate_template', methods: ['GET'])]
	public function generateCompanyEnrolmentTemplate(
		CityRepository $cityRepository,
		LegalStatusRepository $legalStatusRepository,
		RegionRepository $regionRepository,
		SectorAreaRepository $sectorAreaRepository,
		EnrollmentTemplateService $enrollmentTemplateService
	): Response {
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$dataSheet = $spreadsheet->createSheet();
		$dataSheet->setTitle($worksheetName = 'Data');

		/** @var User $currentUser */
		$currentUser = $this->getUser();
		/** @var School $school */
		$school = $currentUser->getSchool();

		$sheet->setCellValue('A1', $this->translator->trans('js.import_csv_name'));
		$sheet->setCellValue('B1', $this->translator->trans('js.import_csv_region'));
		$sheet->setCellValue('C1', $this->translator->trans('js.import_csv_city'));
		$sheet->setCellValue('D1', $this->translator->trans('js.import_csv_phone'));
		$sheet->setCellValue('E1', $this->translator->trans('js.import_csv_email'));
		$sheet->setCellValue('F1', $this->translator->trans('js.import_csv_sector'));
		$sheet->setCellValue('G1', $this->translator->trans('js.import_csv_legal_status'));

		// Region & city
		$enrollmentTemplateService->createColumnMappings(
			$spreadsheet,
			$cityRepository,
			$regionRepository,
			'B',
			'C',
			$worksheetName
		);

		$this->_columnTemplateExcelValidation($sheet, $dataSheet, 'F', $sectorAreaRepository->getNames());
		$this->_columnTemplateExcelValidation($sheet, $dataSheet, 'G', $legalStatusRepository->getNames());


		$response = new StreamedResponse(function () use ($spreadsheet) {
			$writer = new Xlsx($spreadsheet);
			$writer->save('php://output');
		});

		$response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		$response->headers->set('Content-Disposition', 'attachment;filename="person_degree_enrollment_template.xlsx"');
		$response->headers->set('Cache-Control', 'max-age=0');

		return $response;
	}

	private function _columnTemplateExcelValidation(
		Worksheet $sheet,
		Worksheet $dataSheet,
		string $columnLetter,
		$values
	): void {
		$row = 1;
		$count = count($values);

		// Add list option on DATA sheet
		foreach ($values as $value) {
			$dataSheet->setCellValue($columnLetter . $row, $value);
			$row++;
		}

		// add options
		$range = sprintf('Data!$%s$1:$%s$%s', $columnLetter, $columnLetter, $count);
		for ($i = 2; $i <= 200; $i++) {
			$cell = $columnLetter . $i;

			$validation = $sheet->getCell($cell)->getDataValidation();
			$validation->setType(DataValidation::TYPE_LIST);
			$validation->setErrorStyle(DataValidation::STYLE_STOP);
			$validation->setAllowBlank(false);
			$validation->setShowDropDown(true);
			$validation->setFormula1($range);
			$validation->setErrorTitle($this->translator->trans('clean.error'));
			$validation->setError('The value entered is not valid.');
			$validation->setPromptTitle('Choisir dans la liste');
			$validation->setPrompt('Veuillez choisir une valeur dans la liste.');
			$sheet->getCell($cell)->setDataValidation(clone $validation);
		}
	}

    #[IsGranted('ROLE_ETABLISSEMENT')]
	#[Route(path: '/companiesEnroll', name: 'front_school_companies_enroll', methods: ['GET'])]
	public function companiesEnrollAction(): Response {
		return $this->schoolService->checkUnCompletedAccountBefore(function () {
			$school = $this->schoolService->getSchool();
			$selectedCountry = $this->getUser()->getCountry();

			$regions = $this->regionRepository->findByCountry($selectedCountry);
			$legalStatus = $this->legalStatusRepository->findAll();
			$sectorAreas = $this->sectorAreaRepository->findAll();
			$companies = $this->companyRepository->getBySchool($school);

			$companiesUnlocked = [];
			foreach ($companies as $company)
				if ($company->isUnlocked()) {
					$companiesUnlocked[] = $company;
				}

			return $this->render('school/companiesEnroll.html.twig', [
				'companies' => $companiesUnlocked,
				'selectedCountry' => $selectedCountry,
				'regions' =>$regions,
				'sectorAreas' =>$sectorAreas,
				'legalStatus' =>$legalStatus,
			]);
		});
	}

    #[IsGranted('ROLE_ETABLISSEMENT')]
	#[Route(path: '/user_delete/{id}', name: 'front_school_user_delete_school', methods: ['GET', 'POST'])]
	public function deleteUserAction(School $school): RedirectResponse {
		$user = $school->getUser();

        // delete Principals users
        $principals = $this->userRepository->findByPrincipalSchool($school->getId());
        foreach ($principals as $principal) {
            $this->em->remove($principal);
        }

		if ($user) {
			$this->schoolService->removeRelations($user);
			$this->tokenStorage->setToken(null);
			$this->em->remove($user);
			$this->em->flush();

			$this->addFlash('success', $this->translator->trans('flashbag.the_deletion_is_done_successfully'));
			return $this->redirectToRoute('logout');
		} else {
			$this->addFlash('warning', $this->translator->trans('flashbag.unable_to_delete_account'));
			return $this->redirectToRoute('front_school_new');
		}
	}
    #[IsGranted('ROLE_ETABLISSEMENT')]
	#[Route(path: '/persondegrees/{id}/checkPersonDegree', name: 'front_school_persondegree_check', methods: ['GET'])]
	public function personDegreesCheckAction(PersonDegree $personDegree, Request $request): JsonResponse {
		// récupération de la variable en get (si en post, utiliser : $request->request->get('checkSchool');)
		return $this->schoolService->checkUnCompletedAccountBefore(function () use ($personDegree, $request) {
			$checkSchool = boolval($request->query->get('checkSchool'));

			$personDegree->setCheckSchool($checkSchool);
			$this->em->persist($personDegree);
			$this->em->flush();

			// renvoi de la réponse
			$responsePersonDegree = [
				'id' => $personDegree->getId(),
				'name' => $personDegree->getName(),
				'check' => $personDegree->isCheckSchool(),
				'checkSchool' => $checkSchool
			];

			return new JsonResponse([$responsePersonDegree]);
		});
	}
    #[IsGranted('ROLE_ETABLISSEMENT')]
    #[Route(path: '/{id}/personDegreeDelete/', name: 'front_school_person_degree_delete', methods: ['GET'])]
    public function personDegreeDeleteAction(Request $request,int $id): JsonResponse {
        return $this->schoolService->checkUnCompletedAccountBefore(function () use ($request, $id) {
	        $res = [];
	        $err = [];
	        $personDegree = $this->personDegreeRepository->find($id);
	        if(!$personDegree)
		        $err[] = 'Diplômé inexistant en Base ';
	        else {
		        $user = $personDegree->getUser();

		        if($user->getPersonDegree()) {
			        foreach ($user->getPersonDegree() as $diplome) {
				        $this->em->remove($diplome);
			        }
		        }
		        $this->em->remove($user);
		        $this->em->flush();
	        }

	        return new JsonResponse([$res, $err]);
        });
    }
    #[IsGranted('ROLE_ETABLISSEMENT')]
    #[Route(path: '/{id}/companyDelete/', name: 'front_school_company_delete', methods: ['GET'])]
    public function companyDeleteAction(Request $request,int $id): JsonResponse {
        return $this->schoolService->checkUnCompletedAccountBefore(function () use ($request, $id){
	        $res = [];
	        $err = [];

	        $company = $this->companyRepository->find($id);
	        if(!$company)
		        $err[] = "Entreprise inexistante en Base ";
	        else {
		        $user = $company->getUser();

		        if($user->getPersonDegree()) {
			        foreach ($user->getCompany() as $entreprise) {
				        $this->em->remove($entreprise);
			        }
		        }
		        $this->em->remove($user);
		        $this->em->flush();
	        }

	        return new JsonResponse([$res, $err]);
        });
    }
    #[IsGranted('ROLE_ETABLISSEMENT')]
	#[Route(path: '/companies/{id}/updateCompany', name: 'front_school_update_company', methods: ['GET'])]
	public function updateCompanyAction(Company $company, Request $request): JsonResponse {
		return $this->schoolService->checkUnCompletedAccountBefore(function () use ($company, $request) {
			// récupération de la variable en get (si en post, utiliser : $request->request->get('isCompany');)
			$isCompany = boolval($request->query->get('isCompany'));
			$school = $this->schoolService->getSchool();

			if ($isCompany) {
				$school->addCompany($company);
				$this->em->persist($school);
				$this->em->flush();
			} else {
				$school->removeCompany($company);
				$this->em->persist($school);
				$this->em->flush();
			}

			// renvoi de la réponse
			$responseSchool = [
				'id' => $company->getId(),
				'name' => $company->getName(),
				'isCompany' => $isCompany
			];

			return new JsonResponse([$responseSchool]);
		});
	}
    #[IsGranted('ROLE_ETABLISSEMENT')]
	#[Route(path: '/check_logout', name: 'check_logout_school', methods: ['GET', 'POST'])]
	public function check_logout(TokenStorageInterface $tokenStorage): RedirectResponse {
		$school = $this->schoolService->getSchool();
		$user = $this->getUser();

		// suppression du compte si le profil school n'existe pas
		if (!$school) {
			if ($user) {
				$tokenStorage->setToken(null);
				$this->schoolService->removeRelations($user);
				$this->em->remove($user);
				$this->em->flush();
			} else {
				$this->addFlash('warning', $this->translator->trans('flashbag.unable_to_delete_account'));
				return $this->redirectToRoute('front_school_show');
			}

			// update du numéro de téléphone du compte si différente du profil (Attention change de Login)
		} else if ($user->getPhone() != $school->getPhoneStandard()) {
			// verification de la non existance du user par ce numéro de téléphone
			$usrexist = $this->userRepository->findByPhone($school->getPhoneStandard());
			if ($usrexist) {
				$this->addFlash('danger', $this->translator->trans('flashbag.the_login_phone_is_already_used_by_another_account'));
				return $this->redirectToRoute('front_school_edit');
			}

			// modification du numéro de telephone et sortie
			$this->addFlash('warning', $this->translator->trans('flashbag.the_login_phone_for_your_account_will_be_changed') . '|' . $user->getUsername() . '|' . $school->getPhoneStandard());
			$user->setUsername($school->getPhoneStandard());
			$user->setPhone($school->getPhoneStandard());
			$this->em->persist($user);
			$this->em->flush();

			//envoi du mail des paramètres de connexion
			if ($user->getEmail()) {
				if ($this->emailService->sendMailConfirmRegistration($user->getEmail(), $school->getName(),
					"Paramètres de votre compte InserJeune", "Etablissement", $user->getPhone())) {
					$this->addFlash('success', $this->translator->trans('flashbag.your_connection_parameters_are_sent_by_email'));
				} else {
					$this->addFlash('danger', $this->translator->trans('flashbag.error_sending_email'));
				}
			}

			// update de l'adrese email du compte si différente du profil
		} else if ($user->getEmail() != $school->getEmail()) {
			// verification de la non existance du user par cet email
			$usrexist = $this->userRepository->findByEmail($school->getEmail());
			if ($usrexist) {
				$this->addFlash('danger', $this->translator->trans('flashbag.the_email_address_is_already_used_in_another_account', ['{email}' => $school->getEmail()]));
				return $this->redirectToRoute('front_school_edit');
			}

			// modification e l'email et sortie
			$user->setEmail($school->getEmail());
			$this->em->persist($user);
			$this->em->flush();

			// envoi du mail des paramètres de connexion
			if ($user->getEmail()) {
				if ($this->emailService->sendMailConfirmRegistration($user->getEmail(), $school->getName(),
					"Paramètres de votre compte InserJeune", "Etablissement", $user->getPhone())) {
					$this->addFlash('success', $this->translator->trans('flashbag.your_connection_parameters_are_sent_by_email'));
				} else {
					$this->addFlash('danger', $this->translator->trans('flashbag.error_sending_email'));
				}
			}
		}
		return $this->redirectToRoute('logout');
	}
    #[IsGranted('ROLE_ETABLISSEMENT')]
	#[Route(path: '/{id}/enrollPersonDegreeUpdate/', name: 'front_school_enroll_person_degree_update', methods: ['GET', 'POST'])]
	public function enrollPersonDegreeUpdateAction(Request $request, int $id): JsonResponse|Response {
        return $this->schoolService->checkUnCompletedAccountBefore(function () use ($request, $id) {
	        $school = $this->schoolService->getSchool();
	        $datas = $request->query->all();
	        $phoneNumber = "";
	        $res = [];
	        $err = [];

	        $personDegree = $this->personDegreeRepository->find($id);
	        if (!$personDegree) {
		        $personDegree = new PersonDegree();
	        }
	        $actorClass = new ReflectionClass($personDegree);

	        //verification de l'indicatif pays
	        $selectedCountry = null;
            $selectedRegion = null;
	        if ($datas["selectedCountry"]) {
		        $selectedCountry = $this->countryRepository->find($datas["selectedCountry"]);

                if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY']=="true") {
                    if($datas["selectedRegion"]) {
                        $selectedRegion = $this->regionRepository->find($datas["selectedRegion"]);
                        $personDegree->setRegion($selectedRegion);
                    }
                } else {
                    $selectedRegion = $this->regionRepository->find($datas["Region"]);
                    $personDegree->setRegion($selectedRegion);
                }

                $personDegree->setCountry($selectedCountry);
	        }

	        if (!$selectedCountry)
                return new JsonResponse (["", ["erreur serveur de country"]]);

            if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY']=="true") {
                if (!$selectedRegion)
                    return new JsonResponse(["", ["erreur serveur de region (country DBTA)"]]);
            }

            foreach ($datas as $key => $value) {
                $setProp = "set" . ucfirst($key);

                if (($setProp == "setPhoneMobile1") || ($setProp == "setPhoneMobile2")) {
                    // number phone syntax control
                    $phoneSyntax = $this->checkPhoneSyntax($value, $selectedCountry, $selectedRegion);
                    if (($setProp == "setPhoneMobile2") && ($value == ""))
                        $phoneSyntax = "ok";
                    if ($phoneSyntax == "ok") {
                        $personDegree->$setProp($value);
                        $this->em->persist($personDegree);
                        $res[$key] = $value;
                        if ($setProp == "setPhoneMobile1") {
                            $phoneNumber = $value;
                        }
                    } else {
                        // $err[] = "Mauvaise syntaxe numéro " . $this->getLitteralPhoneNameForPersonDegree($key) . " : " . $phoneSyntax;
                        $err[] = $this->getLitteralPhoneNameForPersonDegree($key) . " : " . $phoneSyntax;
                    }

                } else if ($setProp == "setBirthDate") {
                    $res[$key] = $value;
	                $birthDate = Utils::parseFlexibleDate($value);
                    if ($birthDate) {
                        $personDegree->$setProp($birthDate->format(Utils::FORMAT_FR));
                        $this->em->persist($personDegree);
                    } else {
                        $err[] = "No BirthDate found for Id:" . $value;
                    }

                } else if ($setProp == "setRegion") {
                    $res[$key] = $value;
                    $region = $this->regionRepository->find(intval($value));
                    if ($region) {
                        $personDegree->$setProp($region);
                        $this->em->persist($personDegree);
                    } else {
                        $err[] = "No Region found for Id:" . $value;
                    }

                } else if ($setProp == "setAddressCity") {
                    $res[$key] = $value;
                    $city = $this->cityRepository->find($value);
                    if ($city) {
                        $personDegree->$setProp($city);
                        $this->em->persist($personDegree);
                    } else {
                        $err[] = "No City found for Id:" . $value;
                    }

                } else if ($setProp == "setDegree") {
                    $res[$key] = $value;
                    $degree = $this->degreeRepository->find($value);
                    if ($degree) {
                        $personDegree->$setProp($degree);
                        $this->em->persist($personDegree);
                    } else {
                        $err[] = 'No Degree found for Id:' . $value;
                    }

                } else if ($setProp == "setSectorArea") {
                    $res[$key] = $value;
                    $sectorArea = $this->sectorAreaRepository->find($value);
                    if ($sectorArea) {
                        $personDegree->$setProp($sectorArea);
                        $this->em->persist($personDegree);
                    } else {
                        $err[] = 'No SectorArea found for Id:' . $value;
                    }

                } else if ($setProp == "setActivity") {
                    $res[$key] = $value;
                    $activity = $this->activityRepository->find($value);
                    if ($activity) {
                        $personDegree->$setProp($activity);
                        $this->em->persist($personDegree);
                    } else {
                        $err[] = 'No Activity found for Id:' . $value;
                    }

                } else if ($setProp == "setSelectedCountry") {
                } else if ($setProp == "setSelectedRegion") {
                } else if ($setProp == "setId") {
                } else {
                    if ($actorClass->hasMethod($setProp)) {
                        $personDegree->$setProp($value);
                        $this->em->persist($personDegree);
                        $res[$key] = $value;

                    } else {
                        $err[] = 'Bad property:' . $setProp;
                    }
                }
            }


	        if (count($err) == 0) {
		        /* verification si le user existe */
		        /* ------------------------------ */
		        $user = $personDegree->getUser();
		        if (!$user) {

			        $resRegister = $this->actorRegister("personDegree", $phoneNumber, $selectedCountry, $selectedRegion);
			        if (count($resRegister[2]) > 0)
				        $err[] = 'Erreur User: ' . $resRegister[2][0];

			        if ($resRegister[1] != "") {
				        $personDegree->setCreatedDate(new DateTime());
				        $personDegree->setUpdatedDate(new DateTime());
				        $personDegree->setType("TYPE_TRAINING");
				        $personDegree->setSchool($school);
						$personDegree->setCountry($school->getCountry());
				        $personDegree->setUser($resRegister[0]);
				        $personDegree->setTemporaryPasswd($resRegister[1]);
				        $res = ["id" => $personDegree->getId(), "userId" => $resRegister[0]->getId(), "pwd" => $resRegister[1], "err" => $resRegister[2]];
			        } else {
				        $err[] = 'phoneNumber:' . $resRegister[1];
			        }
		        }
		        if (count($err) == 0) {
			        $this->em->flush();
					// Notify student
					// $this->emailService->sendNotificationEnrollmentDegree($personDegree, $school);
			        $res = [
						"id" => $personDegree->getId(),
				        "userId" => $personDegree->getUser()->getId(),
				        "pwd" => $personDegree->getTemporaryPasswd()
			        ];
		        }
	        }
	        return new JsonResponse([$res, $err]);
        });
	}

	private function getLitteralPhoneNameForPersonDegree($name) {
		return match ($name) {
			"phoneMobile1" => $this->translator->trans('menu.cell_phone'),
			"phoneMobile2" => $this->translator->trans('menu.parent_cell_phone'),
			default => $name,
		};
	}
    #[IsGranted('ROLE_ETABLISSEMENT')]
    #[Route(path: '/{id}/enrollCompanyUpdate/', name: 'front_school_enroll_company_update', methods: ['GET'])]
    public function enrollCompanyUpdateAction(Request $request, int $id): JsonResponse|Response {
	    return $this->schoolService->checkUnCompletedAccountBefore(function () use ($request, $id) {
		    $school = $this->schoolService->getSchool();
		    $datas = $request->query->all();
		    $phoneNumber = "";
		    $res = [];
		    $err = [];

		    $company = $this->companyRepository->find($id);
		    if (!$company)
			    $company = new Company();
		    $actorClass = new ReflectionClass($company);

		    //verification de l'indicatif pays
		    $selectedCountry = null;
            $selectedRegion = null;
		    if ($datas["selectedCountry"]) {
			    $selectedCountry = $this->countryRepository->find($datas["selectedCountry"]);

                if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY']=="true") {
                    $selectedRegion = $this->regionRepository->find($datas["Region"]);
                }

                $company->setCountry($selectedCountry);
		    }
		    if (!$selectedCountry)
			    return (["", ["erreur serveur de country"]]);

            if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY']=="true") {
                if($datas["selectedRegion"]) {
                    $selectedRegion = $this->regionRepository->find($datas["selectedRegion"]);
                    $company->setRegion($selectedRegion);
                }

                if (!$selectedRegion)
                    return (["", ["erreur serveur de region (country DBTA)"]]);
            } else {
                $selectedRegion = $this->regionRepository->find($datas["Region"]);
                $company->setRegion($selectedRegion);
            }

            foreach ($datas as $key => $value) {
                $setProp = "set" . ucfirst($key);

                if ($setProp == "setPhoneStandard") {
                    // number phone syntax control
                    $phoneSyntax = $this->checkPhoneSyntax($value, $selectedCountry, $selectedRegion);
                    if ($phoneSyntax == "ok") {
                        $company->$setProp($value);
                        $this->em->persist($company);
                        $res[$key] = $value;
                        $phoneNumber = $value;
                    } else {
                        $err[] = "bad_syntax" .  $key . " : "  . $phoneSyntax;
                    }

                } else if ($setProp == "setRegion") {
                    $res[$key] = $value;
                    $region = $this->regionRepository->find($value);
                    if ($region) {
                        $company->$setProp($region);
                        $this->em->persist($company);
                    } else {
                        $err[] = "No Region found for Id:" . $value;
                    }

                } else if ($setProp == "setCity") {
                    $res[$key] = $value;
                    $city = $this->cityRepository->find($value);
                    if ($city) {
                        $company->$setProp($city);
                        $this->em->persist($company);
                    } else {
                        $err[] = "No City found for Id:" . $value;
                    }

                } else if ($setProp == "setSectorArea") {
                    $res[$key] = $value;
                    $sectorArea = $this->sectorAreaRepository->find($value);
                    if ($sectorArea) {
                        $company->$setProp($sectorArea);
                        $this->em->persist($company);
                    } else {
                        $err[] = "No SectorArea found for Id:" . $value;
                    }

                } else if ($setProp == "setLegalStatus") {
                    $res[$key] = $value;
                    $legalStatus = $this->legalStatusRepository->find($value);
                    if ($legalStatus) {
                        $company->$setProp($legalStatus);
                        $this->em->persist($company);
                    } else {
                        $err[] = "No LegalStatus found for Id:" . $value;
                    }

                } else if ($setProp == "setSelectedCountry") {
                } else if ($setProp == "setId") {
                } else {
                    if ($actorClass->hasMethod($setProp)) {
                        $company->$setProp($value);
                        $this->em->persist($company);
                        $res[$key] = $value;

                    } else {
                        $err[] = "Bad property:" . $setProp;
                    }
                }
            }

		    if (count($err) == 0) {
			    /* verification si le user existe */
			    /* ------------------------------ */
			    $user = $company->getUser();

			    if (!$user) {
				    $resRegister = $this->actorRegister("company", $phoneNumber, $selectedCountry, $selectedRegion);
				    if (count($resRegister[2]) > 0)
					    $err[] = "Erreur User: " . $resRegister[2][0];
				    if ($resRegister[1] != "") {
					    $company->setCreatedDate(new DateTime());
					    $company->setUpdatedDate(new DateTime());
					    $company->setUser($resRegister[0]);
					    $company->setTemporaryPasswd($resRegister[1]);
					    $school->addCompany($company);
					    $res = ["id" => $company->getId(), "userId" => $resRegister[0]->getId(), "pwd" => $resRegister[1], "err" => $resRegister[2]];
				    } else {
					    $err[] = "phoneNumber:" . $resRegister[1];
				    }
			    }
			    if (count($err) == 0) {
				    $this->em->flush();
				    $res = ["id" => $company->getId(), "userId" => $company->getUser()->getId(), "pwd" => $company->getTemporaryPasswd()];
			    }
		    }
		    return new JsonResponse([$res, $err]);
	    });
    }
    #[IsGranted('ROLE_ETABLISSEMENT')]
    #[Route(path: '/sendNotificationEnrollmentDegree', name: 'front_school_send_email_enroll_persondegree', methods: ['POST'])]
    public function sendNotificationEnrollmentDegreeAction(Request $request, TranslatorInterface $trans): JsonResponse|Response {
	    return $this->schoolService->checkUnCompletedAccountBefore(function () use ($request, $trans) {
			$personDegreeIds = $request->get('persondegree_ids');
		    $school = $this->schoolService->getSchool();

			if (count($personDegreeIds)) {
				$personDegrees = $this->personDegreeRepository->getPersonDegreeWithIds($personDegreeIds);
				foreach ($personDegrees as $personDegree) {
					$this->emailService->sendNotificationEnrollmentDegree($personDegree, $school);
				}

                return new JsonResponse([
                    'status' => 'ok',
                    'message' => $trans->trans('js.emails_sent_successfully')
                ]);
			}
		    return new JsonResponse([
			    'status' => 'nok',
			    'message' => $trans->trans('js.error_while_sending_emails')
		    ]);
	    });
    }
    #[IsGranted('ROLE_ETABLISSEMENT')]
    #[Route(path: '/sendNotificationEnrollmentCompanies', name: 'front_school_send_email_enroll_companies', methods: ['POST'])]
    public function sendNotificationEnrollmentCompaniesAction(Request $request, TranslatorInterface $trans): JsonResponse|Response {
	    return $this->schoolService->checkUnCompletedAccountBefore(function () use ($request, $trans) {
			$companyIds = $request->get('company_ids');
		    $school = $this->schoolService->getSchool();

			if (count($companyIds)) {
				$companies = $this->companyRepository->getCompaniesWithIds($companyIds);
				foreach ($companies as $personDegree) {
					$this->emailService->sendNotificationEnrollmentCompany($personDegree, $school);
				}
                return new JsonResponse([
                    'status' => 'ok',
                    'message' => $trans->trans('js.emails_sent_successfully')
                ]);
			}
		    return new JsonResponse([
			    'status' => 'nok',
			    'message' => $trans->trans('js.error_while_sending_emails')
		    ]);
	    });
    }
    #[IsGranted('ROLE_ETABLISSEMENT')]
    #[Route(path: '/getSchoolsByCoordinates', name: 'get_schools_by_coordinates', methods: ['GET'])]
    public function getSchoolsByCoordinates(Request $request): JsonResponse|Response {
        $currentLatitude = floatval($request->get('latitude'));
        $currentLongitude = floatval($request->get('longitude'));
        $gap = floatval($request->get('gap'));
        $currentSchool = $this->schoolService->getSchool();
        $currentId = $currentSchool->getId();
        $newLatitude = null;
        $newLongitude = null;

        // recherche en base les coordonnées des établissements de la ville
        $coordinates = $this->schoolRepository->getSchoolsByCityForCoordinates($currentSchool->getCity());

        foreach ($coordinates as $coordinate) {
            $schoolId = intval($coordinate['id']);
            $schoolLatitude = floatval($coordinate['latitude']);
            $schoolLongitude = floatval($coordinate['longitude']);

            if($schoolId != $currentId) {
                if((($schoolLatitude >= $currentLatitude ) && ($schoolLatitude <= $currentLatitude + $gap * 10)) &&
                   (($schoolLongitude >= $currentLongitude ) && ($schoolLongitude <= $currentLongitude + $gap * 10))) {
                    if($newLatitude < $schoolLatitude) $newLatitude = $schoolLatitude;
                    if($newLongitude < $schoolLongitude) $newLongitude = $schoolLongitude;
                }
            }
        }

        if(($newLatitude == null) || ($newLongitude == null)) {
            $newCoordinates = ['latitude'=>$currentLatitude, 'longitude'=>$currentLongitude];
        } else {
            $newLongitude += $gap;
            $newCoordinates = ['latitude' => $newLatitude, 'longitude' => $newLongitude];
        }

        $result = ['school_id'=> $currentId, 'coordinates' => $newCoordinates];
        return new JsonResponse($result);
    }

	#[Route(path: '/{id}/cityByRegion/', name: 'front_school_city_by_region', methods: ['GET'])]
	public function cityByRegionAction(int $id): JsonResponse|Response {
		return $this->schoolService->checkUnCompletedAccountBefore(function () use ($id) {
			$cities = $this->cityRepository->getByRegionId($id);
			return new JsonResponse($cities);
		});
	}

    #[Route(path: '/{id}/activityBySchoolSectorArea/', name: 'front_school_activity_by_school_sector_area', methods: ['GET'])]
    public function activityBySchoolSectorArea(int $id, ActivityRepository $activityRepository): JsonResponse|Response {
	    return $this->schoolService->checkUnCompletedAccountBefore(function () use ($id, $activityRepository) {
		    return new JsonResponse($activityRepository->getActivitiesOfSector($id));
	    });
    }

     public function checkPhoneSyntax(string $phoneNumber, Country $country, ?Region $region): string {
         $res = "ok";

         //verification de l'indicatif pays
         $phoneCode = '+' . $country->getPhoneCode();
         //Adaptation DBTA
         if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY']=="true") {
             if ($region) {
                 $phoneCode = '+' . $region->getPhoneCode();
             }
         }
         $nationalPhone = "";
         $isValidPhone = false;

         //verification du nombre de digits téléphonique du pays
         $phoneDigit = '+' . $country->getPhoneDigit();
         if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY']=="true") {
             if ($region) {
                 $phoneDigit = '+' . $region->getPhoneDigit();
             }
         }

         if (strncmp($phoneCode, $phoneNumber, strlen($phoneCode)) === 0) {
             $nationalPhone = substr($phoneNumber, strlen($phoneCode));
             $isValidPhone = true;
         } else {
             // $res = "Le numéro doit commencer par " . $phoneCode . "\n";
             $res = $this->translator->trans("menu.number_must_begin_by") . " " . $phoneCode . "(" . $phoneNumber . ")". "\n";
         }

         if ($isValidPhone == true && strlen($nationalPhone) > 0) {
             // suppression du 0 pour le national
             if ($nationalPhone[0] == '0') {
                 $nationalPhone = substr($nationalPhone, 1);
             }

             // reconstruit le numéro de téléphone sans le 0 national
             $validPhone = $phoneCode . $nationalPhone;
             if ($validPhone !== $phoneNumber) {
                 $res = $this->translator->trans("menu.suggestion_for_the_number") . " " . $validPhone;
             }
         }
         // vérification de la conformité du numéro de téléphone
         if ($isValidPhone == true) {

             if (strlen($nationalPhone) != $phoneDigit) {
                 $isValidPhone = false;
                 $res = $this->translator->trans("menu.the_number_without_the_country_code_must_have") . " " . (int)$phoneDigit . " chiffres";
             }
             if (!ctype_digit($nationalPhone)) {
                 $res = $this->translator->trans("menu.wrong_phone_number_syntax");
             }
         }
         return ($res);
     }

    public function actorRegister(string $typePerson, string $phoneNumber, Country $country, Region $region): array {
        $existUser = $this->userRepository->findOneByPhone($phoneNumber);
        $user = new User();
        $err = [];

        //Création du mot de passe temporaire
        $comb = 'abcdefghijklmnopqrstuvwxyz1234567890';
        $password = "";
        $combLen = strlen($comb) - 1;
        for ($i = 0; $i < 6; $i++) {
            $n = rand(0, $combLen);
            $password .= $comb[$n];
        }

         // Mise a jour du Role
         if (!$existUser) {
             switch ($typePerson) {
                 case "company":
                     {
                         $role = $this->roleRepository->findOneBy(["role" => "ROLE_ENTREPRISE"]);
                         $user->addProfil($role);
                     }
                     break;
                 case "personDegree":
                     {
                         $role = $this->roleRepository->findOneBy(["role" => "ROLE_DIPLOME"]);
                         $user->addProfil($role);
                     }
                     break;
                 default:
                     throw new NotFoundHttpException($this->translator->trans('flashbag.unable_to_create_an_account'));
             }

             // Supprime les espaces du numéro de téléphone
             $user->setPhone(preg_replace('/\s/', '', $phoneNumber));

             // Encode le password
             $user->setPassword($this->hasher->hashPassword($user, $password));

             // synchronise le username avec le phone
             $user->setUsername($phoneNumber);

             // créé l'adresse mail fictive
             $user->setEmail($phoneNumber . "@domaine.extension");

             $user->setCountry($country);

             //adaptation DBTA
             if($region){
                 $user->setRegion($region);
             }

             // Persistance en base
             $this->em->persist($user);

         } else {
             $err[] = $this->translator->trans('flashbag.this_phone_number_is_already_in_use');
         }
         return ([$user, $password, $err]);
    }

    #[Route(path: '/changePersonDegreePhoneMobile1', name: 'change_person_degree_phonemobile1', methods: ['GET'])]
    public function changePersonDegreePhoneMobile1(Request$request): JsonResponse|Response {
        $personDegreeId = $request->query->get('id');
        $newPhoneMobile1 = $request->query->get('phoneMobile1');
        $result = "";

        if((!$this->userRepository->findByPhone($newPhoneMobile1))&&
            (!$this->personDegreeRepository->findByPhoneMobile1($newPhoneMobile1))) {
            $personDegree = $this->personDegreeRepository->find($personDegreeId);
            if($personDegree) {
                $user = $this->userRepository->find($personDegree->getUser()->getId());
                $user->setPhone($newPhoneMobile1);
                $user->setUsername($newPhoneMobile1);
                $user->setUsernameCanonical($newPhoneMobile1);
                $this->em->persist($user);

                $personDegree->setPhoneMobile1($newPhoneMobile1);
                $this->em->persist($personDegree);

                $this->em->flush();

                $result = 'OK';
            }
        } else {
            $result = 'N° de téléphone déjà utilisé';
        }
        return new JsonResponse($result);
    }
    #[Route(path: '/changePersonDegreeEmail', name: 'change_person_degree_email', methods: ['GET'])]
    public function changePersonDegreeEmail(Request$request): JsonResponse|Response {
        $personDegreeId = $request->query->get('id');
        $newEmail = $request->query->get('email');
        $result = "";

        if((!$this->userRepository->findByEmail($newEmail))&&
           (!$this->personDegreeRepository->findByEmail($newEmail))) {
               $personDegree = $this->personDegreeRepository->find($personDegreeId);
               if($personDegree) {
                   $user = $this->userRepository->find($personDegree->getUser()->getId());
                   $user->setEmail($newEmail);
                   $this->em->persist($user);

                   $personDegree->setEmail($newEmail);
                   $this->em->persist($personDegree);

                   $this->em->flush();

                   $result = 'OK';
               }
        } else {
            $result = 'Email déjà utilisé';
        }

        return new JsonResponse($result);
    }

    #[Route(path: '/changePersonDegreeType', name: 'change_person_degree_type', methods: ['GET'])]
    public function changePersonDegreeType(Request$request): JsonResponse|Response {
        $personDegreeId = $request->query->get('id');
        $newType = $request->query->get('type');
        $month = $request->query->get('month');
        $year = $request->query->get('year');
        $result = "";

        $personDegree = $this->personDegreeRepository->find($personDegreeId);
        if($personDegree) {
            $personDegree->setType($newType);
            if($month) $personDegree->setLastDegreeMonth(intval($month));
            if($year) $personDegree->setLastDegreeYear(intval($year));
            $this->em->persist($personDegree);
            $this->em->flush();

            $result = 'OK';
        } else {
            $result = 'js.unknown_error_server';
        }

        return new JsonResponse($result);
    }
}
