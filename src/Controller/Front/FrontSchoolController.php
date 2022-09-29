<?php

namespace App\Controller\Front;

use App\Entity\PersonDegree;
use App\Entity\Company;
use App\Entity\SatisfactionSchool;
use App\Entity\School;
use App\Form\SchoolType;
use App\Form\SatisfactionSchoolType;
use App\Repository\CompanyRepository;
use App\Repository\PersonDegreeRepository;
use App\Repository\SatisfactionSalaryRepository;
use App\Repository\UserRepository;
use App\Services\ActivityService;
use App\Services\SchoolService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;

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

	public function __construct(
		EntityManagerInterface       $em,
		ActivityService              $activityService,
		SchoolService                $schoolService,
		CompanyRepository            $companyRepository,
		SatisfactionSalaryRepository $satisfactionSalaryRepository,
		PersonDegreeRepository       $personDegreeRepository,
		UserRepository               $userRepository
	) {
		$this->em = $em;
		$this->activityService = $activityService;
		$this->schoolService = $schoolService;
		$this->companyRepository = $companyRepository;
		$this->satisfactionSalaryRepository = $satisfactionSalaryRepository;
		$this->personDegreeRepository = $personDegreeRepository;
		$this->userRepository = $userRepository;
	}

	#[Route(path: '/new', name: 'front_school_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$school = new School();
		/** @var User $user */
		$user = $this->getUser();
		$school->setEmail($user->getEmail());
		$school->setPhoneStandard($user->getPhone());
		$school->setCountry($user->getCountry());

		$form = $this->createForm(SchoolType::class, $school);
		$form->handleRequest($request);

		$selectedCountry = $this->getUser()->getCountry();

		if ($form->isSubmitted() && $form->isValid()) {
			$agreeRgpd = $form->get('agreeRgpd')->getData();
			if ($agreeRgpd) {
				$school->setCreatedDate(new \DateTime());
				$school->setUpdatedDate(new \DateTime());
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
		$school = $this->schoolService->getSchool();
		if (!$school) {
			return $this->redirectToRoute('front_school_new');
		}

		return $this->render('school/show.html.twig', ['school' => $school]);
	}

	#[Route(path: '/edit', name: 'front_school_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request): RedirectResponse|Response {
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
						$school->setCreatedDate(new \DateTime());
					}
				}

				$school->setUpdatedDate(new \DateTime());
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
	}

	#[Route(path: '/companies', name: 'front_school_company_index', methods: ['GET'])]
	public function companiesIndexAction(): Response {
		$companies = $this
			->companyRepository
			->getBySchool($this->schoolService->getSchool());

		return $this->render('company/index.html.twig', [
			'companies' => $companies
		]);
	}

	#[Route(path: '/all_companies', name: 'front_school_all_company_index', methods: ['GET'])]
	public function allCompaniesIndexAction(): Response {
		$school = $this->schoolService->getSchool();

		$allCompanies = $this->companyRepository->findByCountry($school->getCountry());
		$selectedCompanies = $this->companyRepository->getBySchool($school);

		return $this->render('company/index.html.twig', [
			'companies' => $allCompanies,
			'selectedCompanies' => $selectedCompanies,
		]);
	}

	#[Route(path: '/persondegrees_companies', name: 'front_school_persondegrees_company_index', methods: ['GET'])]
	public function persondegreesCompaniesIndexAction(): Response {
		$school = $this->schoolService->getSchool();

		$allCompanies = $this->companyRepository->findByCountry($school->getCountry());
		$satisfactionSalaries = $this->satisfactionSalaryRepository->getByCountryAndPersonDegreeSchool($school->getCountry(), $school);

		// creation des entreprises trouvées dans les satisfactions
		$employers = array();
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
	}

	#[Route(path: '/persondegrees', name: 'front_school_persondegree_index', methods: ['GET'])]
	public function personDegreesIndexAction(): Response {
		$school = $this->schoolService->getSchool();
		$personDegrees = $this->personDegreeRepository->findBySchool($school);

		return $this->render('persondegree/index.html.twig', [
			'personDegrees' => $personDegrees
		]);
	}

	private function notifSatisfaction(string $message = "Merci d'avoir répondu à l'enquête.") {
		$this->addFlash('success', $message);
	}

	#[Route(path: '/user_delete/{id}', name: 'user_delete_school', methods: ['GET', 'POST'])]
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
	}

	#[Route(path: '/companies/{id}/updateCompany', name: 'front_school_update_company', methods: ['GET'])]
	public function updateCompanyAction(Company $company, Request $request): JsonResponse {
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
	}

	#[Route(path: '/check_logout', name: 'check_logout_school', methods: ['GET', 'POST'])]
	public function check_logout(): RedirectResponse {
		$school = $this->schoolService->getSchool();
		$user = $this->getUser();

		// suppression du compte si le profil school n'existe pas
		if (!$school) {
			if ($user) {
				$this->schoolService->removeRelations($user);
				$this->em->remove($user);
				$this->em->flush();
				$this->addFlash('success', 'Le compte a été supprimé');
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
				if ($this->get('app.email')->sendMailConfirmRegistration($user->getEmail(), $school->getName(),
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

			//envoi du mail des paramètres de connexion
			if ($user->getEmail()) {
				if ($this->get('app.email')->sendMailConfirmRegistration($user->getEmail(), $school->getName(),
					"Paramètres de votre compte InserJeune", "Etablissement", $user->getPhone())) {
					$this->addFlash('success', 'Vos paramètres de connexion sont envoyés par mail');
				} else {
					$this->addFlash('danger', 'Erreur d\'envoi de mail');
				}
			}
		}
		return $this->redirect('/logout');
	}
}
