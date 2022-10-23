<?php

namespace App\Controller\Front;

use App\Entity\Company;
use App\Entity\SatisfactionCompany;
use App\Form\CompanyType;
use App\Form\SatisfactionCompanyType;
use App\Repository\SatisfactionCompanyRepository;
use App\Repository\UserRepository;
use App\Services\CompanyService;
use App\Services\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;

#[Route(path: '/front/company')]
#[IsGranted('ROLE_ENTREPRISE')]
class FrontCompanyController extends AbstractController {
	private EntityManagerInterface $em;
	private CompanyService $companyService;
	private SatisfactionCompanyRepository $satisfactionCompanyRepository;
	private UserRepository $userRepository;
	private EmailService $emailService;

	public function __construct(
		EntityManagerInterface        $em,
		CompanyService                $companyService,
		SatisfactionCompanyRepository $satisfactionCompanyRepository,
		UserRepository                $userRepository,
		EmailService                  $emailService
	) {
		$this->em = $em;
		$this->companyService = $companyService;
		$this->satisfactionCompanyRepository = $satisfactionCompanyRepository;
		$this->userRepository = $userRepository;
		$this->emailService = $emailService;
	}

	#[Route(path: '/new', name: 'front_company_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$company = new Company();
		/** @var User $user */
		$user = $this->getUser();
		$company->setEmail($user->getEmail());
		$company->setPhoneStandard($user->getPhone());
		$company->setCountry($user->getCountry());
		$company->setLocationMode(true);

		// set country dans la company
		$selectedCountry = $this->getUser()->getCountry();

		$form = $this->createForm(CompanyType::class, $company);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$company->setCreatedDate(new \DateTime());
			$company->setUpdatedDate(new \DateTime());
			$agreeRgpd = $form->get('agreeRgpd')->getData();

			if ($agreeRgpd) {
				$company->setUser($user);
				$company->setPhoneStandard($user->getPhone());
				$this->em->persist($company);
				$this->em->flush();

				return $this->redirectToRoute('front_company_satisfactioncompany_new');
			}
		}

		return $this->render('company/new.html.twig', [
			'company' => $company,
			'form' => $form->createView(),
			'selectedCountry' => $selectedCountry
		]);
	}

	#[Route(path: '/', name: 'front_company_show', methods: ['GET'])]
	public function showAction(): Response {
		return $this->companyService->checkUnCompletedAccountBefore(function () {
			$company = $this->companyService->getCompany();
			if (!$company) return $this->redirectToRoute('front_company_new');

			return $this->render('company/show.html.twig', ['company' => $company]);
		});
	}

	#[Route(path: '/edit', name: 'front_company_edit', methods: ['GET', 'POST', 'PUT'])]
	public function editAction(Request $request): RedirectResponse|Response {
		return $this->companyService->checkUnCompletedAccountBefore(function () use ($request) {
			$company = $this->companyService->getCompany();
			$createdDate = $company->getCreatedDate();

			if (!$company) {
				return $this->redirectToRoute('front_company_new');
			}
			$selectedCountry = $this->getUser()->getCountry();

			$editForm = $this->createForm(CompanyType::class, $company);
			$editForm->handleRequest($request);

			if ($editForm->isSubmitted() && $editForm->isValid()) {
				$company->setCreatedDate($createdDate);
				if ($company->getCreatedDate() == null) {
					if ($company->getUpdatedDate()) {
						$company->setCreatedDate($company->getUpdatedDate());
					} else {
						$company->setCreatedDate(new \DateTime());
					}
				}
				$company->setUpdatedDate(new \DateTime());

				$agreeRgpd = $editForm->get('agreeRgpd')->getData();
				if ($agreeRgpd) {
					// remove autorization to edit for School during Enrollment
					if($company->isUnlocked()) {
						$company->setUnlocked(false);
					}
					$company->setUser($this->getUser());
					$this->em->flush();

					return $this->redirectToRoute('front_company_show');
				} else {
					return $this->redirectToRoute('user_delete_company', ['id' => $company->getId()]);
				}
			}

			return $this->render('company/edit.html.twig', [
				'company' => $company,
				'edit_form' => $editForm->createView(),
				'selectedCountry' => $selectedCountry
			]);
		});
	}

	#[Route(path: '/satisfactioncompany', name: 'front_company_satisfactioncompany_index', methods: ['GET'])]
	public function indexSatisfactionCompanyAction(): Response {
		return $this->companyService->checkUnCompletedAccountBefore(function () {
			$satisfactionCompanies = $this->satisfactionCompanyRepository
				->findBy([
					'company' => $this->companyService->getCompany()
				]);

			return $this->render('satisfactioncompany/index.html.twig', ['satisfactionCompanies' => $satisfactionCompanies]);
		});
	}

	#[Route(path: '/satisfactioncompany/new', name: 'front_company_satisfactioncompany_new', methods: ['GET', 'POST'])]
	public function newSatisfactionCompanyAction(Request $request): RedirectResponse|Response {
		return $this->companyService->checkUnCompletedAccountBefore(function () use ($request) {
			$satisfactionCompany = new SatisfactionCompany();
			$company = $this->companyService->getCompany();
			$satisfactionCompany->setCompany($company);

			$form = $this->createForm(SatisfactionCompanyType::class, $satisfactionCompany);
			$form->handleRequest($request);

			if ($form->isSubmitted() && $form->isValid()) {
				$satisfactionCompany->setCreatedDate(new \DateTime());
				$satisfactionCompany->setUpdatedDate(new \DateTime());
				$satisfactionCompany->setCompany($company);

				$this->em->persist($satisfactionCompany);
				$this->em->flush();

				$this->notifSatisfaction();
				return $this->redirectToRoute('front_company_satisfactioncompany_show', ['id' => $satisfactionCompany->getId()]);
			}
			return $this->render('satisfactioncompany/new.html.twig', [
				'satisfactionCompany' => $satisfactionCompany,
				'form' => $form->createView(),
				'company' => $company
			]);
		});
	}

	#[Route(path: '/satisfactioncompany/{id}', name: 'front_company_satisfactioncompany_show', methods: ['GET'])]
	public function showSatisfactionCompanyAction(SatisfactionCompany $satisfactionCompany): Response {
		return $this->companyService->checkUnCompletedAccountBefore(function () use ($satisfactionCompany) {
			$company = $this->companyService->getCompany();
			if (!$company) return $this->redirectToRoute('front_company_new');

			return $this->render('satisfactioncompany/show.html.twig', [
				'company' => $company,
				'satisfactionCompany' => $satisfactionCompany,
			]);
		});
	}

	#[Route(path: '/satisfactionCompany/{id}/edit', name: 'front_company_satisfactioncompany_edit', methods: ['GET', 'POST'])]
	public function editSatisfactionCompanyAction(Request $request, SatisfactionCompany $satisfactionCompany): RedirectResponse|Response {
		return $this->companyService->checkUnCompletedAccountBefore(function () use ($request, $satisfactionCompany) {
			$createdDate = $satisfactionCompany->getCreatedDate();
			$editForm = $this->createForm(SatisfactionCompanyType::class, $satisfactionCompany);
			$editForm->handleRequest($request);

			if ($editForm->isSubmitted() && $editForm->isValid()) {
				$satisfactionCompany->setCreatedDate($createdDate);
				if ($satisfactionCompany->getCreatedDate() == null) {
					if ($satisfactionCompany->getUpdatedDate()) {
						$satisfactionCompany->setCreatedDate($satisfactionCompany->getUpdatedDate());
					} else {
						$satisfactionCompany->setCreatedDate(new \DateTime());
					}
				}

				$satisfactionCompany->setUpdatedDate(new \DateTime());
				$satisfactionCompany->setCompany($this->companyService->getCompany());
				$this->em->flush();

				return $this->redirectToRoute('front_company_satisfactioncompany_show', ['id' => $satisfactionCompany->getId()]);
			}

			return $this->render('satisfactioncompany/edit.html.twig', [
				'satisfactionCompany' => $satisfactionCompany,
				'edit_form' => $editForm->createView(),
			]);
		});
	}

	/**
	 */
	private function notifSatisfaction() {
		$this->addFlash('success', "Merci d'avoir répondu à l'enquête.");
	}

	#[Route(path: '/user_delete/{id}', name: 'user_delete_company', methods: ['GET'])]
	public function deleteUserAction(Company $company): RedirectResponse {
		$user = $company->getUser();

		if ($user) {
			$this->companyService->removeRelations($user);
			$this->em->remove($user);
			$this->em->flush();

			$this->addFlash('success', 'La suppression est faite avec success');
			return $this->redirectToRoute('/logout');
		} else {
			$this->addFlash('warning', 'Impossible de supprimer le compte');
			return $this->redirectToRoute('front_company_new');
		}
	}

	#[Route(path: '/check_logout', name: 'check_logout_company', methods: ['GET'])]
	public function check_logout(): RedirectResponse {
		$company = $this->companyService->getCompany();
		$user = $this->getUser();

		if (!$company) {
			if ($user) {
				$this->companyService->removeRelations($user);
				$this->em->remove($user);
				$this->em->flush();
				$this->addFlash('success', 'Le compte a été supprimé');
			} else {
				$this->addFlash('warning', 'Impossible de supprimer le compte');
				return $this->redirectToRoute('front_company_show');
			}

			// update du numéro de téléphone du compte si différente du profil (Attention change de Login)
		} else if ($user->getPhone() != $company->getPhoneStandard()) {
			// verification de la non existance du user par ce numéro de téléphone
			$usrexist = $this->userRepository->findByPhone($company->getPhoneStandard());
			if ($usrexist) {
				$this->addFlash('danger', 'Le téléphone de connexion est déjà utilisé par un autre compte');
				return $this->redirectToRoute('front_company_edit');
			}

			// modification du numéro de telephone et sortie
			$this->addFlash('warning', 'Le téléphone de connexion votre compte va être modifié' . '|' . $user->getUsername() . '|' . $company->getPhoneStandard());
			$user->setUsername($company->getPhoneStandard());
			$user->setPhone($company->getPhoneStandard());

			$this->em->persist($user);
			$this->em->flush();

			// Envoi du mail des paramètres de connexion
			if ($user->getEmail()) {
				if ($this->get('app.email')->sendMailConfirmRegistration($user->getEmail(), $company->getName(),
					"Paramètres de votre compte InserJeune", "Entreprise", $user->getPhone())) {
					$this->addFlash('success', 'Vos paramètres de connexion sont envoyés par mail');
				} else {
					$this->addFlash('danger', 'Erreur d\'envoi de mail');
				}
			}

			// update de l'adrese email du compte si différente du profil
		} else if ($user->getEmail() != $company->getEmail()) {
			// verification de la non existance du user par cet email
			$usrexist = $this->userRepository->findByEmail($company->getEmail());
			if ($usrexist) {
				$this->addFlash('danger', "L'adresse mail: " . $company->getEmail() . " est déjà utilisé dans un autre compte");
				return $this->redirectToRoute('front_company_edit');
			}
			// modification de l'email et sortie
			$user->setEmail($company->getEmail());

			$this->em->persist($user);
			$this->em->flush();

			// Envoi du mail des paramètres de connexion
			if ($user->getEmail()) {
				if ($this->emailService->sendMailConfirmRegistration($user->getEmail(), $company->getName(),
					"Paramètres de votre compte InserJeune", "Entreprise", $user->getPhone())) {
					$this->addFlash('success', 'Vos paramètres de connexion sont envoyés par mail');
				} else {
					$this->addFlash('danger', 'Erreur d\'envoi de mail');
				}
			}
		}

		return $this->redirectToRoute('logout');
	}
}
