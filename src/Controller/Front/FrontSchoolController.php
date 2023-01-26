<?php

namespace App\Controller\Front;

use App\Entity\PersonDegree;
use App\Entity\Company;
use App\Entity\SatisfactionSchool;
use App\Entity\Country;
use App\Entity\School;
use App\Form\SchoolType;
use App\Form\SatisfactionSchoolType;
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
use App\Services\SchoolService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: 'front/school')]
#[IsGranted('ROLE_ETABLISSEMENT')]
class FrontSchoolController extends AbstractController {
	private EntityManagerInterface $em;
	private ActivityService $activityService;
	private SchoolService $schoolService;
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

	public function __construct(
		EntityManagerInterface       $em,
		ActivityService              $activityService,
		SchoolService                $schoolService,
		CompanyRepository            $companyRepository,
        UserPasswordHasherInterface  $hasher,
		SatisfactionSalaryRepository $satisfactionSalaryRepository,
		PersonDegreeRepository       $personDegreeRepository,
		UserRepository               $userRepository,
		RoleRepository               $roleRepository,
		RegionRepository             $regionRepository,
        CountryRepository            $countryRepository,
        CityRepository               $cityRepository,
        DegreeRepository             $degreeRepository,
		LegalStatusRepository        $legalStatusRepository,
		SectorAreaRepository         $sectorAreaRepository,
		ActivityRepository           $activityRepository,
		EmailService                 $emailService,
        SchoolRepository             $schoolRepository
	) {
		$this->em = $em;
		$this->activityService = $activityService;
		$this->schoolService = $schoolService;
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
	}

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
			'selectedCountry' => $selectedCountry
		]);
	}

	#[Route(path: '/', name: 'front_school_show', methods: ['GET'])]
	public function showAction(): Response {
		return $this->schoolService->checkUnCompletedAccountBefore(function () {
			$school = $this->schoolService->getSchool();
			if (!$school) {
				return $this->redirectToRoute('front_school_new');
			}

			return $this->render('school/show.html.twig', ['school' => $school]);
		});
	}

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
					return $this->redirectToRoute('user_delete_school', array('id' => $school->getId()));
				}
			}

			return $this->render('school/edit.html.twig', [
				'school' => $school,
				'edit_form' => $editForm->createView(),
				'allActivities' => $this->activityService->getAllActivities(),
				'selectedCountry' => $selectedCountry
			]);
		});
	}

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

	#[Route(path: '/persondegrees', name: 'front_school_persondegree_index', methods: ['GET'])]
	public function personDegreesIndexAction(): Response {
		return $this->schoolService->checkUnCompletedAccountBefore(function () {
			$school = $this->schoolService->getSchool();
			$schoolId = $school ? $school->getId() : null;
			$personDegrees = $this->personDegreeRepository->getAllPersonDegree(null, $schoolId);

			return $this->render('persondegree/index.html.twig', [
				'personDegrees' => $personDegrees
			]);
		});
	}


	#[Route(path: '/persondegreesEnroll', name: 'front_school_persondegrees_enroll', methods: ['GET'])]
	public function personDegreesEnrollAction(): Response {
		return $this->schoolService->checkUnCompletedAccountBefore(function () {
			$school = $this->schoolService->getSchool();
			$selectedCountry = $this->getUser()->getCountry();

			$regions = $this->regionRepository->findByCountry($selectedCountry);
			$personDegrees = $this->personDegreeRepository->getBySchoolAndByUnlocked($school, true);

			return $this->render('school/personDegreesEnroll.html.twig', [
				'personDegrees' => $personDegrees,
				'regions' =>$regions,
			]);
		});
	}

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


	#[Route(path: '/user_delete/{id}', name: 'front_school_user_delete_school', methods: ['GET', 'POST'])]
	public function deleteUserAction(School $school): RedirectResponse {
		$user = $school->getUser();

		if ($user) {
			$this->schoolService->removeRelations($user);
			$this->em->remove($user);
			$this->em->flush();

			$this->addFlash('success', 'La suppression est faite avec success');
			return $this->redirectToRoute('/logout');
		} else {
			$this->addFlash('warning', 'Impossible de supprimer le compte');
			return $this->redirectToRoute('front_school_new');
		}
	}

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
				$this->addFlash('warning', 'Impossible de supprimer le compte');
				return $this->redirectToRoute('front_school_show');
			}

			// update du numéro de téléphone du compte si différente du profil (Attention change de Login)
		} else if ($user->getPhone() != $school->getPhoneStandard()) {
			// verification de la non existance du user par ce numéro de téléphone
			$usrexist = $this->userRepository->findByPhone($school->getPhoneStandard());
			if ($usrexist) {
				$this->addFlash('danger', 'Le téléphone de connexion est déjà utilisé par un autre compte');
				return $this->redirectToRoute('front_school_edit');
			}

			// modification du numéro de telephone et sortie
			$this->addFlash('warning', 'Le téléphone de connexion votre compte va être modifié' . '|' . $user->getUsername() . '|' . $school->getPhoneStandard());
			$user->setUsername($school->getPhoneStandard());
			$user->setPhone($school->getPhoneStandard());
			$this->em->persist($user);
			$this->em->flush();

			//envoi du mail des paramètres de connexion
			if ($user->getEmail()) {
				if ($this->emailService->sendMailConfirmRegistration($user->getEmail(), $school->getName(),
					"Paramètres de votre compte InserJeune", "Etablissement", $user->getPhone())) {
					$this->addFlash('success', 'Vos paramètres de connexion sont envoyés par mail');
				} else {
					$this->addFlash('danger', 'Erreur d\'envoi de mail');
				}
			}

			// update de l'adrese email du compte si différente du profil
		} else if ($user->getEmail() != $school->getEmail()) {
			// verification de la non existance du user par cet email
			$usrexist = $this->userRepository->findByEmail($school->getEmail());
			if ($usrexist) {
				$this->addFlash('danger', "L'adresse mail: " . $school->getEmail() . " est déjà utilisé dans un autre compte");
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
					$this->addFlash('success', 'Vos paramètres de connexion sont envoyés par mail');
				} else {
					$this->addFlash('danger', 'Erreur d\'envoi de mail');
				}
			}
		}
		return $this->redirectToRoute('logout');
	}

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
	        if ($datas["selectedCountry"]) {
		        $selectedCountry = $this->countryRepository->find($datas["selectedCountry"]);
		        $personDegree->setCountry($selectedCountry);
	        }
	        if (!$selectedCountry)
		        return (["", ["erreur serveur de country"]]);

	        if ($selectedCountry)
		        foreach ($datas as $key => $value) {
			        $setProp = "set" . ucfirst($key);
			        $getProp = "get" . ucfirst($key);

			        if (($setProp == "setPhoneMobile1") || ($setProp == "setPhoneMobile2")) {
				        // number phone syntax control
				        $phoneSyntax = $this->checkPhoneSyntax($value, $selectedCountry);
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
					        $err[] = "Mauvaise syntaxe " . $setProp . " : " . $phoneSyntax;
				        }

			        } else if ($setProp == "setBirthDate") {
				        $res[$key] = $value;
				        $birthDate = new DateTime($value);
				        if ($birthDate) {
					        $personDegree->$setProp($birthDate->format('m/d/Y'));
					        $this->em->persist($personDegree);
				        } else {
					        $err[] = "No BirthDate found for Id:" . $value;
				        }

			        } else if ($setProp == "setRegion") {
				        $res[$key] = $value;
				        $region = $this->regionRepository->find($value);
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

			        $resRegister = $this->actorRegister("personDegree", $phoneNumber, $selectedCountry);
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
					// $this->emailService->sendNotificationEnrollementDegree($personDegree, $school);
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
		    if ($datas["selectedCountry"]) {
			    $selectedCountry = $this->countryRepository->find($datas["selectedCountry"]);
			    $company->setCountry($selectedCountry);
		    }
		    if (!$selectedCountry)
			    return (["", ["erreur serveur de country"]]);

		    if ($selectedCountry)
			    foreach ($datas as $key => $value) {
				    $setProp = "set" . ucfirst($key);
				    $getProp = "get" . ucfirst($key);
				    if ($setProp == "setPhoneStandard") {
					    // number phone syntax control
					    $phoneSyntax = $this->checkPhoneSyntax($value, $selectedCountry);
					    if ($phoneSyntax == "ok") {
						    $company->$setProp($value);
						    $this->em->persist($company);
						    $res[$key] = $value;
						    $phoneNumber = $value;
					    } else {
						    $err[] = "Mauvaise syntaxe " . $setProp . " : " . $phoneSyntax;
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
				    $resRegister = $this->actorRegister("company", $phoneNumber, $selectedCountry);
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
			}
		    return new JsonResponse([
			    'status' => 'ok',
			    'message' => $trans->trans('notification.enrollment_email_send_successful')
		    ]);
	    });
    }

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
			}
		    return new JsonResponse([
			    'status' => 'ok',
			    'message' => $trans->trans('notification.enrollment_email_send_successful')
		    ]);
	    });
    }

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
                // echo (strval($currentId) . " CUR(" .
                //     strval($currentLatitude) . "," . strval($currentLongitude) ."  ) ".
                //     strval($schoolId) . " -> MAX(" .
                //     strval($currentLatitude + $gap * 10) . "," . strval($currentLongitude + $gap * 10) . ") -> SCH(" .
                //     strval($schoolLatitude) . "," . strval($schoolLongitude) .')<br>');

                // Recherche de l'établissement le plus éloigné dans la zone $gap*10
                if((($schoolLatitude >= $currentLatitude ) && ($schoolLatitude <= $currentLatitude + $gap * 10)) &&
                   (($schoolLongitude >= $currentLongitude ) && ($schoolLongitude <= $currentLongitude + $gap * 10))) {
                    // echo('--->OK<br>');
                    if($newLatitude < $schoolLatitude) $newLatitude = $schoolLatitude;
                    if($newLongitude < $schoolLongitude) $newLongitude = $schoolLongitude;
                }
            }
        }
        // echo ("NEW-->" . strval($newLatitude) . "," . strval($newLongitude) .' --> ');
        // echo (strval($newLatitude+$gap) . "," . strval($newLongitude) .'<br>');
        // die();

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
    public function activityBySchoolSectorArea(int $id): JsonResponse|Response {
	    return $this->schoolService->checkUnCompletedAccountBefore(function () use ($id) {
		    $school = $this->schoolService->getSchool();

		    // find which school sectorarea (1 to 6) used by $id
		    $sectorAreaNumber = 0;
		    for ($i = 1; $i <= 6; $i++) {
			    $getSectorArea = "getSectorArea" . (string)$i;

			    if ($school->$getSectorArea()) {
				    if ($school->$getSectorArea()->getId() == $id) {
					    $sectorAreaNumber = $i;
					    $i = 7; //end of loop
				    }
			    }
		    }

		    // Find Activities used by School
		    $getActivities = "getActivities" . $sectorAreaNumber;
		    $activities = $school->$getActivities();
		    $res = [];
		    foreach ($activities as $activity) {
			    $data = array('name' => $activity->getName(), 'id' => $activity->getId());
			    $res[] = $data;
		    }

		    //dump($res);
		    return new JsonResponse($res);
	    });
    }

     public function checkPhoneSyntax(string $phoneNumber, Country $country): string {
        $res = "ok";

        //verification de l'indicatif pays
        $phoneCode = '+' . $country->getPhoneCode();
        $nationalPhone = "";
        $isValidPhone = false;

        //verification du nombre de digits téléphonique du pays
        $phoneDigit = '+' . $country->getPhoneDigit();

        if (strncmp($phoneCode, $phoneNumber, strlen($phoneCode)) === 0) {
	        $nationalPhone = substr($phoneNumber, strlen($phoneCode));
	        $isValidPhone = true;
        } else {
	        $res = "Le numéro doit commencer par " . $phoneCode . "\n";
        }

	     if ($isValidPhone == true && strlen($nationalPhone) > 0) {
		     // suppression du 0 pour le national
		     if ($nationalPhone[0] == '0') {
			     $nationalPhone = substr($nationalPhone, 1);
		     }

		     // reconstruit le numéro de téléphone sans le 0 national
		     $validPhone = $phoneCode . $nationalPhone;
		     if ($validPhone !== $phoneNumber) {
			     $res = "Suggestion pour le numéro " . $validPhone;
		     }
	     }
	     // vérification de la conformité du numéro de téléphone
	     if ($isValidPhone == true) {

		     if (strlen($nationalPhone) != $phoneDigit) {
			     $isValidPhone = false;
			     $res = "Le numéro sans l'indicatif pays doit avoir " . (int)$phoneDigit . " chiffres";
            }
		     if (!ctype_digit($nationalPhone)) {
			     $res = "mauvaise syntaxe du numéro de téléphone";
		     }
	     }
	     return ($res);
     }

    public function actorRegister(string $typePerson, string $phoneNumber, Country $country): array {
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
                     throw new NotFoundHttpException('Impossible de créer un compte');
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

             // Persistance en base
             $this->em->persist($user);

         } else {
             $err[] = "Ce numéro de téléphone est déja utilisé";
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
}
