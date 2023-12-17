<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\JobOffer;
use App\Entity\School;
use App\Form\JobOfferType;
use App\Repository\JobOfferRepository;
use App\Repository\JobAppliedRepository;
use App\Repository\SchoolRepository;
use App\Services\SchoolService;
use App\Services\CompanyService;
use App\Services\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/jobOffer')]
class JobOfferController extends AbstractController {
	private EntityManagerInterface $em;
	private JobOfferRepository $jobOfferRepository;
    private JobAppliedRepository $jobAppliedRepository;
    private SchoolRepository $schoolRepository;
	private CompanyService $companyService;
    private SchoolService $schoolService;
	private FileUploader $fileUploader;
	private TranslatorInterface $translator;

	public function __construct(
		EntityManagerInterface $em,
		JobOfferRepository     $jobOfferRepository,
        JobAppliedRepository    $jobAppliedRepository,
        SchoolRepository        $schoolRepository,
		CompanyService $companyService,
        SchoolService $schoolService,
		FileUploader $fileUploader,
		TranslatorInterface $translator
	) {
		$this->em = $em;
		$this->jobOfferRepository = $jobOfferRepository;
        $this->jobAppliedRepository = $jobAppliedRepository;
        $this->schoolRepository = $schoolRepository;
		$this->companyService = $companyService;
        $this->schoolService = $schoolService;
		$this->fileUploader = $fileUploader;
		$this->translator = $translator;
	}

	#[IsGranted('ROLE_USER')]
	#[Route(path: '/', name: 'jobOffer_index', methods: ['GET'])]
	public function indexAction(): Response {
        $company = null;
        $school = null;

        $jobOffers = $this->jobOfferRepository->getAllJobOffer();
        $jobApplieds = $this->jobAppliedRepository->getAll();
        $othersJobOffers = null;

        if ($this->getUser()->hasRole('ROLE_DIPLOME')) {
            $jobApplieds = $this->jobAppliedRepository->getByUserPersonDegree($this->getUser()->getId());
        }

        if ($this->getUser()->hasRole('ROLE_ENTREPRISE')) {
            $company = $this->companyService->getCompany();
            $jobOffers = $this->jobOfferRepository->findBy(['company' => $company], ['id' => 'DESC']);
            $othersJobOffers = $this->jobOfferRepository->othersDifferentOfId($company->getId());
        }

        if (($this->getUser()->hasRole('ROLE_ETABLISSEMENT')) || ($this->getUser()->hasRole('ROLE_PRINCIPAL'))) {
            $school = $this->schoolService->getSchool();
            //only for Principal role
            if (!$school) {
                $school = $this->schoolRepository->find($this->getUser()->getPrincipalSchool());
            }
            if($school) {
                $jobOffers = $this->jobOfferRepository->findBySchool($school);
                $othersJobOffers = $this->jobOfferRepository->othersDifferentOfId($school->getId());
            }
        }

        // test if account is created for company and school
        if($company==null && $school==null) {
             if ($this->getUser()->hasRole('ROLE_ENTREPRISE')) {
                 return $this->redirectToRoute('front_company_new');
             } elseif ( $this->getUser()->hasRole('ROLE_ETABLISSEMENT')) {
                 return $this->redirectToRoute('front_school_new');
             }
        }

		return $this->render('jobOffer/index.html.twig', [
			'jobOffers' => $jobOffers,
            'othersJobs' => $othersJobOffers,
            'jobApplieds' => $jobApplieds,
		]);
	}

    // #[IsGranted('ROLE_ADMIN')]
    #[Route(path: '/jobApplied', name: 'job_applied_index', methods: ['GET'])]
    public function jobAppliedAction(): Response {
        $jobApplieds = $this->jobAppliedRepository->getAll();
        $school = null;
        $company = null;

        if (($this->getUser()->hasRole('ROLE_ETABLISSEMENT')) || ($this->getUser()->hasRole('ROLE_PRINCIPAL'))) {
            $school = $this->schoolService->getSchool();
            // only for Principal role
            if (!$school) {
                $school = $this->schoolRepository->find($this->getUser()->getPrincipalSchool());
            }
            $jobApplieds = $this->jobAppliedRepository->getByUserSchool($school->getUser()->getId());
        }
        if ($this->getUser()->hasRole('ROLE_ENTREPRISE')) {
            $company = $this->companyService->getCompany();
            $jobApplieds = $this->jobAppliedRepository->getByUserCompany($company->getUser()->getId());
        }
        if ($this->getUser()->hasRole('ROLE_DIPLOME')) {
            $jobApplieds = $this->jobAppliedRepository->getByUserPersonDegree($this->getUser()->getId());
        }

        // test if account is created for company and school
        if($company==null && $school==null) {
            if ($this->getUser()->hasRole('ROLE_ENTREPRISE')) {
                return $this->redirectToRoute('front_company_new');
            } elseif ( $this->getUser()->hasRole('ROLE_ETABLISSEMENT')) {
                return $this->redirectToRoute('front_school_new');
            }
        }

        return $this->render('jobOffer/jobApplied.html.twig', [
            'jobApplieds' => $jobApplieds
        ]);
    }

	#[IsGranted('ROLE_USER')]
	#[Route(path: '/new', name: 'jobOffer_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
        $school = null;
        $company = null;
		$jobOffer = new JobOffer();
		$form = $this->createForm(JobOfferType::class, $jobOffer);
		$form->handleRequest($request);

        if ($this->getUser()->hasRole('ROLE_ENTREPRISE')) {
            $company = $this->companyService->getCompany();
            $jobOffer->setCompany($company);
        // } elseif ($this->getUser()->hasRole('ROLE_PRINCIPAL')) {
        //     $school = $this->getUser()->getPrincipalSchool();
        //     $jobOffer->setSchool($school);
        } elseif ($this->getUser()->hasRole('ROLE_ETABLISSEMENT')) {
            $school = $this->schoolService->getSchool();
            $jobOffer->setSchool($school);
        }

        // test if account is created for company and school
        if($company==null && $school==null) {
            if ($this->getUser()->hasRole('ROLE_ENTREPRISE')) {
                return $this->redirectToRoute('front_company_new');
            } elseif ( $this->getUser()->hasRole('ROLE_ETABLISSEMENT')) {
                return $this->redirectToRoute('front_school_new');
            }
        }

		if ($form->isSubmitted() && $form->isValid()) {
			$offerDescription = $form->get('file')->getData();
			if ($offerDescription) {
				$offerDescriptionFileName = $this->fileUploader->upload($offerDescription);
				$jobOffer->setFilename($offerDescriptionFileName);
			}
			$this->em->persist($jobOffer);
			$this->em->flush();

			return $this->redirectToRoute('jobOffer_show', ['id' => $jobOffer->getId()]);
		}

		return $this->render('jobOffer/new.html.twig', [
            'school' => $school,
            'company' => $company,
			'jobOffer' => $jobOffer,
			'form' => $form->createView(),
		]);
	}

	#[Route(path: '/{id}', name: 'jobOffer_show', methods: ['GET'])]
	public function showAction(JobOffer $jobOffer): Response {
		$this->companyService->markJobOfferAsView($jobOffer->getId());
		return $this->render('jobOffer/show.html.twig', [
			'jobOffer' => $jobOffer,
		]);
	}

	#[IsGranted('ROLE_USER')]
	#[Route(path: '/{id}/edit', name: 'jobOffer_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request, JobOffer $jobOffer): RedirectResponse|Response {
        $school = null;
        $company = null;

        if ($this->getUser()->hasRole('ROLE_ENTREPRISE')) {
            $company = $this->companyService->getCompany();
            if ($jobOffer->getCompany() !== $company)
                throw new NotFoundHttpException('Aucune offre trouvée');
        } elseif ($this->getUser()->hasRole('ROLE_PRINCIPAL'))  {
            $school = $this->getUser()->getPrincipalSchool();
            if ($jobOffer->getSchool() !== $school)
                throw new NotFoundHttpException('Aucune offre trouvée');
        } elseif ($this->getUser()->hasRole('ROLE_ETABLISSEMENT')) {
            $school = $this->schoolService->getSchool();
            if ($jobOffer->getSchool() !== $school)
                throw new NotFoundHttpException('Aucune offre trouvée');
        }

        $editForm = $this->createForm(JobOfferType::class, $jobOffer);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$offerDescription = $editForm->get('file')->getData();
			if ($offerDescription) {
				$offerDescriptionFileName = $this->fileUploader->upload($offerDescription, $jobOffer->getFilename());
				$jobOffer->setFilename($offerDescriptionFileName);
			}

            if ($this->getUser()->hasRole('ROLE_ENTREPRISE')) {
                $jobOffer->setCompany($company);
            } elseif ($this->getUser()->hasRole('ROLE_PRINCIPAL') || $this->getUser()->hasRole('ROLE_ETABLISSEMENT')) {
                $jobOffer->setSchool($school);
            }
			$jobOffer->setUpdatedDate(new \DateTime());
			$this->em->flush();

			return $this->redirectToRoute('jobOffer_show', ['id' => $jobOffer->getId()]);
		}
		return $this->render('jobOffer/edit.html.twig', [
            'school' => $school,
            'company' => $company,
			'jobOffer' => $jobOffer,
			'edit_form' => $editForm->createView()
		]);
	}

    #[Security("is_granted('ROLE_ADMIN') or 
            is_granted('ROLE_ETABLISSEMENT') or 
            is_granted('ROLE_ENTREPRISE')")]
	#[Route(path: '/delete/{id}', name: 'jobOffer_delete', methods: ['GET'])]
	public function deleteAction(Request $request, ?JobOffer $jobOffer): RedirectResponse {
		if (array_key_exists('HTTP_REFERER', $request->server->all())) {
			if ($jobOffer) {
				$this->fileUploader->removeOldFile($jobOffer->getFilename());
				$this->em->remove($jobOffer);
				$this->em->flush();
				$this->addFlash('success', $this->translator->trans('flashbag.the_deletion_is_done_successfully'));
			} else {
				$this->addFlash('warning', $this->translator->trans('flashbag.unable_to_delete_offer'));
				return $this->redirect($request->server->all()['HTTP_REFERER']);
			}
		}
		return $this->redirectToRoute('jobOffer_index');
	}
}
