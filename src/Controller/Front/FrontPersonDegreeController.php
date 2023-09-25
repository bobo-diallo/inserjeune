<?php

namespace App\Controller\Front;

use App\Entity\Activity;
use App\Entity\Candidate;
use App\Entity\Degree;
use App\Entity\JobOffer;
use App\Entity\JobApplied;
use App\Entity\PersonDegree;
use App\Form\CandidateType;
use App\Form\PersonDegreeType;
use App\Entity\School;
use App\Repository\JobOfferRepository;
use App\Repository\JobAppliedRepository;
use App\Repository\UserRepository;
use App\Repository\PersonDegreeRepository;
use App\Repository\CountryRepository;
use App\Repository\RegionRepository;
use App\Services\ActivityService;
use App\Services\CompanyService;
use App\Services\EmailService;
use App\Services\FileUploader;
use App\Services\PersonDegreeService;
use App\Tools\Utils;
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
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: 'front/persondegree')]
#[IsGranted('ROLE_DIPLOME')]
class FrontPersonDegreeController extends AbstractController {
	private EntityManagerInterface $em;
	private ActivityService $activityService;
	private PersonDegreeService $personDegreeService;
	private JobOfferRepository $jobOfferRepository;
    private JobAppliedRepository $jobAppliedRepository;
	private EmailService $emailService;
	private UserRepository $userRepository;
	private CompanyService $companyService;
	private FileUploader $fileUploader;
    private PersonDegreeRepository $personDegreeRepository;
	private TokenStorageInterface $tokenStorage;
    private CountryRepository $countryRepository;
    private RegionRepository $regionRepository;
	private TranslatorInterface $translator;

	public function __construct(
        EntityManagerInterface $em,
        ActivityService        $activityService,
        PersonDegreeService    $personDegreeService,
        JobOfferRepository     $jobOfferRepository,
        JobAppliedRepository   $jobAppliedRepository,
        EmailService           $emailService,
        UserRepository         $userRepository,
        CompanyService         $companyService,
        FileUploader           $fileUploader,
        PersonDegreeRepository $personDegreeRepository,
        TokenStorageInterface  $tokenStorage,
        CountryRepository      $countryRepository,
        RegionRepository       $regionRepository,
        TranslatorInterface    $translator
	) {
		$this->em = $em;
		$this->activityService = $activityService;
		$this->personDegreeService = $personDegreeService;
		$this->jobOfferRepository = $jobOfferRepository;
        $this->jobAppliedRepository = $jobAppliedRepository;
		$this->emailService = $emailService;
		$this->userRepository = $userRepository;
		$this->companyService = $companyService;
		$this->fileUploader = $fileUploader;
        $this->personDegreeRepository = $personDegreeRepository;
		$this->tokenStorage = $tokenStorage;
        $this->countryRepository = $countryRepository;
        $this->regionRepository = $regionRepository;
		$this->translator = $translator;
	}

	#[Route(path: '/new', name: 'front_persondegree_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$personDegree = new Persondegree();

		/** @var User $user */
		$user = $this->getUser();

        $personDegree->setPhoneMobile1($user->getPhone());
		$personDegree->setCountry($user->getCountry());
		$personDegree->setLocationMode(true);
        $residenceCountryPhoneCode = null;

        $selectedCountry = $this->getUser()->getCountry();
        if($this->getUser()->getResidenceCountry()) {
            $residenceCountryPhoneCode = $this->getUser()->getResidenceCountry()->getPhoneCode();
        }

        $personDegree->setDiaspora($user->isDiaspora());
        $personDegree->setResidenceCountry($user->getResidenceCountry());

        //adaptation dbta
        $selectedRegion = null;
        if ($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'true') {
            if($user->getCountry()?->getId() != $user->getRegion()->getCountry()?->getId()) {
                $user->setCountry($user->getRegion()->getCountry());
                $this->em->persist($user);
                $this->em->flush();
            }
            $personDegree->setRegion($user->getRegion());
            $personDegree->setCountry($user->getRegion()->getCountry());
            $selectedRegion = $personDegree->getRegion();
        }

		$form = $this->createForm(PersonDegreeType::class, $personDegree, [
            'selectedCountry' => $selectedCountry->getId()
            ]);
		$form->handleRequest($request);

        $otherCountries = $this->countryRepository->getNameAndIndicatif($selectedCountry->getId());
        if ($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'true') {
            $otherCountries = $this->regionRepository->getNameAndIndicatif($selectedCountry->getId());
        }

		if ($form->isSubmitted() && $form->isValid()) {
			$agreeRgpd = $form->get('agreeRgpd')->getData();
			if ($agreeRgpd) {
				$user->setEmail($personDegree->getEmail());
                $user->setDiaspora($personDegree->isDiaspora());
                $user->setResidenceCountry($personDegree->getResidenceCountry());

				$personDegree->setUser($user);
				$personDegree->setCreatedDate(new \DateTime());
				$personDegree->setUpdatedDate(new \DateTime());
				$personDegree->setPhoneMobile1($user->getPhone());
                $personDegree->setUnlocked(false);

                $this->em->persist($user);
				$this->em->persist($personDegree);
				$this->em->flush();

				return $this->redirectToRoute('front_persondegree_satisfaction_new');
			}
		}

		return $this->render('persondegree/new.html.twig', [
			'personDegree' => $personDegree,
			'form' => $form->createView(),
			'allActivities' => $this->activityService->getAllActivities(),
			'selectedCountry' => $selectedCountry,
			'selectedRegion' => $selectedRegion,
			'residenceCountryPhoneCode' => $residenceCountryPhoneCode,
            'otherCountries' => $otherCountries,
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

            $user = $this->getUser();

            //adaptation for DBTA
            $selectedRegion = null;
            if ($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'true') {
                if ($user->getCountry()->getId() != $user->getRegion()->getCountry()->getId()) {
                    $user->setCountry($user->getRegion()->getCountry());
                    $this->em->persist($user);
                    $this->em->flush();
                }
                $selectedRegion = $user->getRegion();
            }

			if (!$personDegree) {
				return $this->redirectToRoute('front_persondegree_new');
			}

			$createdDate = $personDegree->getCreatedDate();
			$selectedCountry = $user->getCountry();

			if (!$selectedCountry) {
				$selectedCountry = $personDegree->getCountry();
			}

            $otherCountries = $this->countryRepository->getNameAndIndicatif($selectedCountry->getId());
			$residenceCountryPhoneCode = null;
			if ($user->getResidenceCountry()) {
				$residenceCountryPhoneCode = $user->getResidenceCountry()->getPhoneCode();
			}
            if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'true') {
                $otherCountries = $this->regionRepository->getNameAndIndicatif($selectedCountry->getId());
                $residenceCountryPhoneCode = $user->getResidenceRegion()?->getPhoneCode();
            }

			$editForm = $this->createForm(PersonDegreeType::class, $personDegree, ['selectedCountry' => $selectedCountry->getId()]);
			$editForm->handleRequest($request);

			if ($editForm->isSubmitted() && $editForm->isValid()) {
				$agreeRgpd = $editForm->get('agreeRgpd')->getData();
				if ($agreeRgpd) {
                    //update diaspora informations
                    $residenceCountryPhoneCode = $personDegree->getUser()->getResidenceCountry()?->getPhoneCode();
                    // if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'true') {
                    //     $residenceCountryPhoneCode = $personDegree->getUser()->getResidenceRegion()?->getPhoneCode();
                    // }

                    $user->setDiaspora($personDegree->isDiaspora());
                    $user->setResidenceCountry($personDegree->getResidenceCountry());
                    $user->setResidenceRegion($personDegree->getResidenceRegion());
                    $this->em->persist($user);

					// remove autorization to edit for School during Enrollment
					if($personDegree->isUnlocked()) {
						$personDegree->setUnlocked(false);
					}
					$personDegree->setUser($user);

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
                    if ((php_uname('n') != $dnsServer)&&(php_uname('n') != null))
						$personDegree->setClientUpdateDate(new \DateTime());

                    $this->em->persist($personDegree);
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
				'selectedCountry' => $selectedCountry,
				'selectedRegion' => $selectedRegion,
                'residenceCountryPhoneCode' => $residenceCountryPhoneCode,
                'otherCountries' => $otherCountries
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

                $personDegreeEmail = null;
                if ($personDegree->getEmail()) {
                    $personDegreeEmail = $personDegree->getEmail();
                }

                // creation du journal
                $jobApplied = $this->jobAppliedRepository->findOneByDateAndOfferAndPersonDegree($jobOffer, $personDegree->getUser());

                if(!$jobApplied) {
                    $jobApplied = new JobApplied();
                } else {
                    if ($jobApplied->isSended()) {
                        $this->addFlash('warning', $this->translator->trans('flashbag.already_sending_application'));
                    } else {
                        $this->addFlash('warning', $this->translator->trans('flashbag.last_application_failed_try_again'));
                    }
                }
                // $jobApplied->setIdOffer($jobOffer);
                $jobApplied->setIdOffer($jobOffer->getId());
                // $jobApplied->setIdUser($personDegree->getUser());
                $jobApplied->setIdUser($personDegree->getUser()->getId());
                $jobApplied->setIdCity($jobOffer->getCity()->getId());
                $jobApplied->setAppliedDate(new \DateTime());
                $candidateName = $personDegree->getLastname()." ". $personDegree->getFirstname()." (". $personDegree->getId().") " . $personDegree->getPhoneMobile1();
                $jobSender = null;
                $senderType = "null";
                if ($jobOffer->getCompany()) {
                    $jobSender = $jobOffer->getCompany()->getName() . ' (' . $jobOffer->getCompany()->getId() . ')';
                    $senderType = "company";
                }
                if ($jobOffer->getSchool()) {
                    $jobSender = $jobOffer->getSchool()->getName() . ' (' . $jobOffer->getSchool()->getId() . ')';
                    $senderType = "school";
                }

                $resumed =
                    "%tag_strong%Application date: %tag_end_strong%" . $jobApplied->getAppliedDate()->format(Utils::FORMAT_FR)."  By ". "%tag_strong%candidate: %tag_end_strong%" . $candidateName . "%tag_br%" .
                    "%tag_strong%Offer%tag_end_strong% (" . $jobOffer->getId() . ") %tag_strong%update date: %tag_end_strong%" . $jobOffer->getUpdatedDate()->format(Utils::FORMAT_FR). "%tag_br%".
                    "%tag_strong%From: %tag_end_strong%" . $senderType . " " . $jobSender . "%tag_br%".
                    "    %tag_strong%contract: %tag_end_strong%" . $jobOffer->getContract()."%tag_br%".
                    "    %tag_strong%localization: %tag_end_strong%" .$jobOffer->getCity() ."%tag_br%" .
                    "%tag_strong%Description: %tag_end_strong%";

                $contractDescription = $this->suppressHtmlTags($jobOffer->getDescription());
                if(strlen($resumed) + strlen($contractDescription) > 700 ){
                    $contractDescription = substr($contractDescription,0, 700 - strlen($resumed)) . "... ";
                }

				if ($this->emailService->sendCandidateMail($candidate, $jobOffer, $personDegreeEmail)) {
					$this->addFlash('success', $this->translator->trans('flashbag.your_application_is_sent_successfully'));
                    $jobOffer->addCandidateSended($personDegree->getUser()->getId());
                    $this->em->persist($jobOffer);
                    $this->em->flush();
                    $jobApplied->setIsSended(true);
				} else {
					$this->addFlash('warning', $this->translator->trans('flashbag.error_sending_application'));
				}
                $sent = "%tag_strong%Error sent, %tag_end_strong% ";
                if($jobApplied->isSended()) {
                    $sent = "%tag_strong%Sent OK, %tag_end_strong% ";
                }

                $jobApplied->setResumedApplied("%tag_p%" . $sent . $resumed . $contractDescription . "%tag_end_p%");
// var_dump($jobApplied);die();
                $this->em->persist($jobApplied);
                $this->em->flush();

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
	 * @param ?string $message
	 */
	private function notifSatisfaction($type = 'success', $message = null) {
		if (!$message) {
			$message = $this->translator->trans('flashbag.thank_you_for_responding_to_the_survey');
		}
		$this->addFlash($type, $message);
	}

	#[Route(path: '/user_delete/{id}', name: 'user_delete_persondegree', methods: ['GET'])]
	public function deleteUserAction(PersonDegree $personDegree): RedirectResponse {
		$user = $personDegree->getUser();

		if ($user) {
			$this->personDegreeService->removeRelations($user);
			$this->tokenStorage->setToken(null);
			$this->em->remove($user);
			$this->em->flush();
			$this->addFlash('success', $this->translator->trans('flashbag.the_deletion_is_done_successfully'));
			return $this->redirectToRoute('logout');
		} else {
			$this->addFlash('warning', $this->translator->trans('flashbag.unable_to_delete_account'));
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
				$this->addFlash('warning', $this->translator->trans('flashbag.unable_to_delete_account'));
				return $this->redirectToRoute('front_persondegree_show');
			}

			// update du numéro de téléphone du compte si différente du profil (Attention change de Login)
		} else if ($user->getPhone() != $personDegree->getPhoneMobile1()) {

			// verification de la non existance du user par ce numéro de téléphone
			$usrexist = $this->userRepository->findByPhone($personDegree->getPhoneMobile1());
			if ($usrexist) {
				$this->addFlash('danger', $this->translator->trans('flashbag.the_login_phone_is_already_used_by_another_account'));
				return $this->redirectToRoute('front_persondegree_edit');
			}

			// modification du numéro de telephone et sortie
			$this->addFlash('warning', $this->translator->trans('flashbag.the_login_phone_for_your_account_will_be_changed') . '|' . $user->getUsername() . '|' . $personDegree->getPhoneMobile1());
			$user->setUsername($personDegree->getPhoneMobile1());
			$user->setPhone($personDegree->getPhoneMobile1());
			$this->em->persist($user);
			$this->em->flush();

			//envoi du mail des paramètres de connexion
			if ($user->getEmail()) {
				if ($this->emailService->sendMailConfirmRegistration($user->getEmail(), $personDegree->getFirstname(),
					"Paramètres de votre compte InserJeune", "Diplômé", $user->getPhone())) {
					$this->addFlash('success', $this->translator->trans('flashbag.your_connection_parameters_are_sent_by_email'));
				} else {
					$this->addFlash('danger', $this->translator->trans('flashbag.error_sending_email'));
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
				$this->addFlash('danger', $this->translator->trans('flashbag.remember_to_create_a_valid_email_address'));
			} else {
				$user->setEmail($personDegree->getEmail());
				$this->em->persist($user);
				$this->em->flush();
			}

			//envoi du mail des paramètres de connexion
			if ($user->getEmail()) {
				if ($this->emailService->sendMailConfirmRegistration($user->getEmail(), $personDegree->getFirstname(),
					"Paramètres de votre compte InserJeune", "Diplômé", $user->getPhone())) {
					$this->addFlash('success', $this->translator->trans('flashbag.your_connection_parameters_are_sent_by_email'));
				} else {
					$this->addFlash('danger', $this->translator->trans('flashbag.error_sending_email'));
				}
			}
		}
		return $this->redirectToRoute('logout');
	}

    #[Route(path: '/getPersondegreesByCoordinates', name: 'get_persondegrees_by_coordinates', methods: ['GET'])]
    public function getPersondegreesByCoordinates(Request $request): JsonResponse|Response {
        $currentLatitude = floatval($request->get('latitude'));
        $currentLongitude = floatval($request->get('longitude'));
        $gap = floatval($request->get('gap'));
        $currentPersondegree = $this->personDegreeService->getPersonDegree();
        $currentId = $currentPersondegree?->getId();
        $newLatitude = $currentLatitude;
        $newLongitude = $currentLongitude;

        // printf(" current pos = %.7f; %.7f\n", $newLatitude, $newLongitude );

        // recherche en base les coordonnées des diplômés de la ville
        $coordinates = $this->personDegreeRepository->getPersondegreesByCityForCoordinates($currentPersondegree->getAddressCity());

        //boucle sur 300 gaps de longitude
        $maxDuplicateLongitude = 300;
        $maxDuplicateLatitude = 16;
        for ($j = 0; $j < $maxDuplicateLongitude; $j++) {
            // printf("\n---$j = %d----------------------------------------------------\n",$j);

            // boucle sur 16 gaps de latitude (s'il existe un acteur dans les 20 gaps, on passe à la longitude supérieure)
            $actorExist = [];
            for ($i = 1; $i < $maxDuplicateLatitude; $i++) {
                // printf("-------$i = %d-------------------------------------------------\n",$i);
                // echo("current =" .$currentId. ": " .$currentLatitude. ";" .$currentLongitude); printf("\n");

                $actorExist[$i] = "free";
                for ($k = 0; $k < count($coordinates); $k++) {
                    $actorId = intval($coordinates[$k]['id']);

                    if ($actorId != $currentId) {
                        $actorLatitude = floatval($coordinates[$k]['latitude']);
                        $actorLongitude = floatval($coordinates[$k]['longitude']);

                        if(($actorLatitude > $currentLatitude + $gap * ($i-1)) &&
                            ($actorLatitude <= $currentLatitude + $gap * $i) &&
                            ($actorLongitude >= $currentLongitude + $gap * ($j)) &&
                            ($actorLongitude <= $currentLongitude + $gap * ($j+1))) {
                            // printf("actor =%s : %.6f; %.6f\n", $actorId, $actorLatitude, $actorLongitude );
                            $actorExist[$i] = "used";
                        }
                    }
                }
            }
            if (in_array("free", $actorExist)) {
                // debugg : affiche les cases libres
                // var_dump("");
                // for ($i = 1; $i < count($actorExist)+1; $i++) {
                //     echo $actorExist[$i] . " | ";
                // }
                // var_dump("");
                for ($i = 1; $i < count($actorExist)+1; $i++) {
                    if($actorExist[$i] == "free") {
                        // printf('%.7f;%.7f',$gap * $i , $gap * $j);
                        $newLatitude = $currentLatitude + $gap * $i;
                        $newLongitude = $currentLongitude + $gap * $j;
                        $i = count($actorExist);
                    }
                }
                // printf(" new pos = %.7f; %.7f\n", $newLatitude, $newLongitude );
                $j = $maxDuplicateLongitude;
            }
        }

        $newCoordinates = ['latitude' => $newLatitude, 'longitude' => $newLongitude];
        $result = ['personDegree_id'=> $currentId, 'coordinates' => $newCoordinates];

        return new JsonResponse($result);
    }
    private function suppressHtmlTags (string $str) : String {
        $inputs = explode('<',$str);
        $result = "";
        foreach ($inputs as $input) {
            $inputExplodes = explode('>', $input);
            if (count($inputExplodes)>1) {
                $result .= $inputExplodes[1];
            }
        }
        return $result;
    }
}
