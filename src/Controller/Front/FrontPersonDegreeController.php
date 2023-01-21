<?php

namespace App\Controller\Front;

use App\Entity\Activity;
use App\Entity\Candidate;
use App\Entity\Degree;
use App\Entity\JobOffer;
use App\Entity\PersonDegree;
use App\Form\CandidateType;
use App\Form\PersonDegreeType;
use App\Entity\School;
use App\Repository\JobOfferRepository;
use App\Repository\UserRepository;
use App\Services\ActivityService;
use App\Services\CompanyService;
use App\Services\EmailService;
use App\Services\FileUploader;
use App\Services\PersonDegreeService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route(path: 'front/persondegree')]
#[IsGranted('ROLE_DIPLOME')]
class FrontPersonDegreeController extends AbstractController {
	private EntityManagerInterface $em;
	private ActivityService $activityService;
	private PersonDegreeService $personDegreeService;
	private JobOfferRepository $jobOfferRepository;
	private EmailService $emailService;
	private UserRepository $userRepository;
	private CompanyService $companyService;
	private FileUploader $fileUploader;

	public function __construct(
		EntityManagerInterface $em,
		ActivityService        $activityService,
		PersonDegreeService    $personDegreeService,
		JobOfferRepository     $jobOfferRepository,
		EmailService           $emailService,
		UserRepository         $userRepository,
		CompanyService $companyService,
		FileUploader $fileUploader
	) {
		$this->em = $em;
		$this->activityService = $activityService;
		$this->personDegreeService = $personDegreeService;
		$this->jobOfferRepository = $jobOfferRepository;
		$this->emailService = $emailService;
		$this->userRepository = $userRepository;
		$this->companyService = $companyService;
		$this->fileUploader = $fileUploader;
	}

	#[Route(path: '/new', name: 'front_persondegree_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$personDegree = new Persondegree();
		/** @var User $user */
		$user = $this->getUser();
		$personDegree->setPhoneMobile1($user->getPhone());
		$personDegree->setCountry($user->getCountry());
		$personDegree->setLocationMode(true);

		$selectedCountry = $this->getUser()->getCountry();

		$form = $this->createForm(PersonDegreeType::class, $personDegree, ['selectedCountry' => $selectedCountry->getId()]);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$agreeRgpd = $form->get('agreeRgpd')->getData();
			if ($agreeRgpd) {
				$user->setEmail($personDegree->getEmail());
				$personDegree->setUser($user);
				$personDegree->setCreatedDate(new \DateTime());
				$personDegree->setUpdatedDate(new \DateTime());
				$personDegree->setPhoneMobile1($user->getPhone());

				$this->em->persist($personDegree);
				$this->em->flush();

				return $this->redirectToRoute('front_persondegree_satisfaction_new');
			}
		}

		return $this->render('persondegree/new.html.twig', [
			'personDegree' => $personDegree,
			'form' => $form->createView(),
			'allActivities' => $this->activityService->getAllActivities(),
			'selectedCountry' => $selectedCountry
		]);
	}

	#[Route(path: '/', name: 'front_persondegree_show', methods: ['GET'])]
	public function showAction(): Response {
		return $this->personDegreeService->checkUnCompletedAccountBefore(function () {
			$personDegree = $this->personDegreeService->getPersonDegree();
			if (!$personDegree) return $this->redirectToRoute('front_persondegree_new');

			return $this->render('persondegree/show.html.twig', [
				'personDegree' => $personDegree,
			]);
		});
	}

	#[Route(path: '/edit', name: 'front_persondegree_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request): RedirectResponse|Response {
		return $this->personDegreeService->checkUnCompletedAccountBefore(function () use ($request) {
			$personDegree = $this->personDegreeService->getPersonDegree();
			if (!$personDegree) return $this->redirectToRoute('front_persondegree_new');
			$createdDate = $personDegree->getCreatedDate();

			$selectedCountry = $this->getUser()->getCountry();
			if (!$selectedCountry)
				$selectedCountry = $personDegree->getCountry();

			$editForm = $this->createForm(PersonDegreeType::class, $personDegree, ['selectedCountry' => $selectedCountry->getId()]);
			$editForm->handleRequest($request);

			if ($editForm->isSubmitted() && $editForm->isValid()) {
				$agreeRgpd = $editForm->get('agreeRgpd')->getData();
				if ($agreeRgpd) {
					// remove autorization to edit for School during Enrollment
					if($personDegree->isUnlocked()) {
						$personDegree->setUnlocked(false);
					}
					$personDegree->setUser($this->getUser());

					// Patch if no createdDate found
					$personDegree->setCreatedDate($createdDate);
					if ($personDegree->getCreatedDate() == null) {
						if ($personDegree->getUpdatedDate()) {
							$personDegree->setCreatedDate($personDegree->getUpdatedDate());
						} else {
							$personDegree->setCreatedDate(new \DateTime());
						}
					}// end patch
					$personDegree->setUpdatedDate(new \DateTime());

					$dnsServer = $this->getParameter('dnsServer');
					if (php_uname('n') != $dnsServer)
						$personDegree->setClientUpdateDate(new \DateTime());

					$this->em->flush();

					return $this->redirectToRoute('front_persondegree_satisfaction_new');
				} else {
					return $this->redirectToRoute('user_delete_persondegree', ['id' => $personDegree->getId()]);
				}
			}

			return $this->render('persondegree/edit.html.twig', [
				'personDegree' => $personDegree,
				'edit_form' => $editForm->createView(),
				'allActivities' => $this->activityService->getAllActivities(),
				'selectedCountry' => $selectedCountry
			]);
		});
	}

	#[Route(path: '/jobOffers', name: 'front_persondegree_joboffers', methods: ['GET'])]
	public function listJobOffersAction(): Response {
		if (!$this->personDegreeService->getPersonDegree()) {
			return $this->redirectToRoute('front_persondegree_new');
		} else {
			return $this->redirectToRoute('jobOffer_index');
		}
	}


	#[Route(path: '/{id}/job_offer', name: 'front_persondegree_jobOffer_show', methods: ['GET'])]
	public function showJobOfferAction(JobOffer $jobOffer): Response {
		return $this->render('jobOffer/show.html.twig', [
			'jobOffer' => $jobOffer
		]);
	}

	#[Route(path: '/{id}/candidate', name: 'front_persondegree_candidate', methods: ['GET', 'POST'])]
	public function candidateAction(Request $request, JobOffer $jobOffer, MailerInterface $mailer): Response {
		return $this->personDegreeService->checkUnCompletedAccountBefore(function () use ($request, $jobOffer, $mailer) {
			$this->companyService->markJobOfferAsView($jobOffer->getId());

			$candidate = new Candidate();
			$form = $this->createForm(CandidateType::class, $candidate);
			$form->handleRequest($request);

			if ($form->isSubmitted() && $form->isValid()) {
				// Save cv/coverLetter
				$cvFile = $form->get('cv')->getData();
				$coverLetterFile = $form->get('coverLetter')->getData();
				if ($cvFile) {
					$cvFileName = $this->fileUploader->upload($cvFile);
					$candidate->setCvFilename($cvFileName);
				}

				if ($coverLetterFile) {
					$coverLetterFileName = $this->fileUploader->upload($coverLetterFile);
					$candidate->setCoverLetterFilename($coverLetterFileName);
				}

				$personDegree = $this->personDegreeService->getPersonDegree();
				$candidate->setCandidateName(preg_replace('/ /', '_', strtolower($personDegree->getFirstname() . '_' . $personDegree->getLastname())));

				if ($this->emailService->sendCandidateMail($candidate, $jobOffer)) {
					$this->addFlash('success', 'Votre candididature est envoyée avec success.');
                    $jobOffer->addCandidateSended($personDegree->getUser()->getId());
                    $this->em->persist($jobOffer);
                    $this->em->flush();
				} else {
					$this->addFlash('warning', 'Erreur envoi candidature');
				}

				return $this->redirectToRoute('jobOffer_index');
			}
			return $this->render('frontPersondegree/candidate.html.twig', [
				'form' => $form->createView(),
				'jobOffer' => $jobOffer
			]);
		});
	}

	/**
	 * @param string $type
	 * @param string $message
	 */
	private function notifSatisfaction($type = 'success', $message = "Merci d'avoir répondu à l'enquête.") {
		$this->addFlash($type, $message);
	}

	#[Route(path: '/user_delete/{id}', name: 'user_delete_persondegree', methods: ['GET'])]
	public function deleteUserAction(PersonDegree $personDegree): RedirectResponse {
		$user = $personDegree->getUser();

		if ($user) {
			$this->personDegreeService->removeRelations($user);
			$this->em->remove($user);
			$this->em->flush();
			$this->addFlash('success', 'La suppression est faite avec success');
			return $this->redirectToRoute('/logout');
		} else {
			$this->addFlash('warning', 'Impossible de supprimer le compte');
			return $this->redirectToRoute('front_persondegree_new');
		}
	}

	#[Route(path: '/filters/{id}/school', name: 'front_persondegree_filters_school', methods: ['GET'])]
	public function getFiltersBySchoolAction(School $school): JsonResponse {
		$listDegree = [];
		/** @var Degree $degree */
		foreach ($school->getDegrees() as $degree) {
			$listDegree[] = ['id' => $degree->getId(), 'name' => $degree->getName()];
		}

		$listSectorArea = [];
		$listSectorArea[] = ['id' => $school->getSectorArea1()->getId(), 'name' => $school->getSectorArea1()->getName()];
		if ($school->getSectorArea2())
			$listSectorArea[] = ['id' => $school->getSectorArea2()->getId(), 'name' => $school->getSectorArea2()->getName()];
		if ($school->getSectorArea3())
			$listSectorArea[] = ['id' => $school->getSectorArea3()->getId(), 'name' => $school->getSectorArea3()->getName()];
		if ($school->getSectorArea4())
			$listSectorArea[] = ['id' => $school->getSectorArea4()->getId(), 'name' => $school->getSectorArea4()->getName()];
		if ($school->getSectorArea5())
			$listSectorArea[] = ['id' => $school->getSectorArea5()->getId(), 'name' => $school->getSectorArea5()->getName()];
		if ($school->getSectorArea6())
			$listSectorArea[] = ['id' => $school->getSectorArea6()->getId(), 'name' => $school->getSectorArea6()->getName()];


		$listActivity = [];
		/** @var Activity $activity */
		foreach ($school->getActivities1() as $activity) {
			$listActivity[] = ['id' => $activity->getId(), 'name' => $activity->getName()];
		}
		if ($school->getActivities2()) {
			/** @var Activity $activity */
			foreach ($school->getActivities2() as $activity) {
				$listActivity[] = ['id' => $activity->getId(), 'name' => $activity->getName()];
			}
		}
		if ($school->getActivities3()) {
			/** @var Activity $activity */
			foreach ($school->getActivities3() as $activity) {
				$listActivities[] = ['id' => $activity->getId(), 'name' => $activity->getName()];
			}
		}
		if ($school->getActivities4()) {
			/** @var Activity $activity */
			foreach ($school->getActivities4() as $activity) {
				$listActivity[] = ['id' => $activity->getId(), 'name' => $activity->getName()];
			}
		}
		if ($school->getActivities5()) {
			/** @var Activity $activity */
			foreach ($school->getActivities5() as $activity) {
				$listActivity[] = ['id' => $activity->getId(), 'name' => $activity->getName()];
			}
		}
		if ($school->getActivities6()) {
			/** @var Activity $activity */
			foreach ($school->getActivities6() as $activity) {
				$listActivity[] = ['id' => $activity->getId(), 'name' => $activity->getName()];
			}
		}
		return new JsonResponse([$listDegree, $listSectorArea, $listActivity]);
	}

	#[Route(path: '/check_logout', name: 'check_logout_persondegree', methods: ['GET'])]
	public function check_logout(TokenStorageInterface $tokenStorage): RedirectResponse {
		$personDegree = $this->personDegreeService->getPersonDegree();
		$user = $this->getUser();

		if (!$personDegree) {
			if ($user) {
				$this->personDegreeService->removeRelations($user);
				$tokenStorage->setToken(null);
				$this->em->remove($user);
				$this->em->flush();
				return $this->redirectToRoute('logout');
			} else {
				$this->addFlash('warning', 'Impossible de supprimer le compte');
				return $this->redirectToRoute('front_persondegree_show');
			}

			// update du numéro de téléphone du compte si différente du profil (Attention change de Login)
		} else if ($user->getPhone() != $personDegree->getPhoneMobile1()) {

			// verification de la non existance du user par ce numéro de téléphone
			$usrexist = $this->userRepository->findByPhone($personDegree->getPhoneMobile1());
			if ($usrexist) {
				$this->addFlash('danger', 'Le téléphone de connexion est déjà utilisé par un autre compte');
				return $this->redirectToRoute('front_persondegree_edit');
			}

			// modification du numéro de telephone et sortie
			$this->addFlash('warning', 'Le téléphone de connexion votre compte va être modifié' . '|' . $user->getUsername() . '|' . $personDegree->getPhoneMobile1());
			$user->setUsername($personDegree->getPhoneMobile1());
			$user->setPhone($personDegree->getPhoneMobile1());
			$this->em->persist($user);
			$this->em->flush();

			//envoi du mail des paramètres de connexion
			if ($user->getEmail()) {
				if ($this->emailService->sendMailConfirmRegistration($user->getEmail(), $personDegree->getFirstname(),
					"Paramètres de votre compte InserJeune", "Diplômé", $user->getPhone())) {
					$this->addFlash('success', 'Vos paramètres de connexion sont envoyés par mail');
				} else {
					$this->addFlash('danger', 'Erreur d\'envoi de mail');
				}
			}

			// update de l'adrese email du compte si différente du profil
		} else if ($user->getEmail() != $personDegree->getEmail()) {
			// verification de la non existance du user par cet email
			$usrexist = $this->userRepository->findByEmail($personDegree->getEmail());
			if ($usrexist) {
				$this->addFlash('danger', "L'adresse mail: " . $personDegree->getEmail() . " est déjà utilisé dans un autre compte");
				return $this->redirectToRoute('front_persondegree_edit');
			}

			// modification de l'email et sortie
			if (!$personDegree->getEmail()) {
				//n'affiche rien car sortie de session !!
				$this->addFlash('danger', "Pensez à créer une adresse email valide");
			} else {
				$user->setEmail($personDegree->getEmail());
				$this->em->persist($user);
				$this->em->flush();
			}

			//envoi du mail des paramètres de connexion
			if ($user->getEmail()) {
				if ($this->emailService->sendMailConfirmRegistration($user->getEmail(), $personDegree->getFirstname(),
					"Paramètres de votre compte InserJeune", "Diplômé", $user->getPhone())) {
					$this->addFlash('success', 'Vos paramètres de connexion sont envoyés par mail');
				} else {
					$this->addFlash('danger', 'Erreur d\'envoi de mail');
				}
			}
		}
		return $this->redirectToRoute('logout');
	}
}
