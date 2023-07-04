<?php

namespace App\Controller\Front;

use App\Entity\JobOffer;
use App\Form\JobOfferType;
use App\Repository\JobOfferRepository;
use App\Services\FileUploader;
use App\Services\SchoolService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/front/school/jobOffer')]
#[IsGranted('ROLE_ETABLISSEMENT')]
class FrontSchoolJobOfferController extends AbstractController {
	private EntityManagerInterface $em;
	private SchoolService $schoolService;
	private JobOfferRepository $jobOfferRepository;
	private FileUploader $fileUploader;
	private TranslatorInterface $translator;

	public function __construct(
		EntityManagerInterface $em,
		SchoolService         $schoolService,
		JobOfferRepository     $jobOfferRepository,
		FileUploader $fileUploader,
		TranslatorInterface $translator
	) {
		$this->em = $em;
		$this->schoolService = $schoolService;
		$this->jobOfferRepository = $jobOfferRepository;
		$this->fileUploader = $fileUploader;
		$this->translator = $translator;
	}

	#[Route(path: '/', name: 'front_school_jobOffer_index', methods: ['GET'])]
	public function indexAction(): Response {
		return $this->schoolService->checkUnCompletedAccountBefore(function () {
			$school = $this->schoolService->getSchool();
			$jobOffers = $this->jobOfferRepository->findBySchool($school);
			$othersJobOffers = $this->jobOfferRepository->othersDifferentOfSchool($school);

			return $this->render('frontSchoolJobOffer/index.html.twig', [
				'jobOffers' => $jobOffers,
				'othersJobs' => $othersJobOffers,
			]);
		});
	}

	#[Route(path: '/new', name: 'front_school_jobOffer_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		return $this->schoolService->checkUnCompletedAccountBefore(function () use ($request) {
			$school = $this->schoolService->getSchool();

			$jobOffer = new JobOffer();
			$jobOffer->setSchool($school);
			$form = $this->createForm(JobOfferType::class, $jobOffer);
			$form->handleRequest($request);

			if ($form->isSubmitted() && $form->isValid()) {
				$offerDescription = $form->get('file')->getData();
				if ($offerDescription) {
					$offerDescriptionFileName = $this->fileUploader->upload($offerDescription);
					$jobOffer->setFilename($offerDescriptionFileName);
				}

				$jobOffer->setSchool($school);
				$this->em->persist($jobOffer);
				$this->em->flush();

				return $this->redirectToRoute('front_school_jobOffer_show', ['id' => $jobOffer->getId()]);
			}

			return $this->render('frontSchoolJobOffer/new.html.twig', [
				'school' => $school,
				'form' => $form->createView(),
				'jobOffer' => $jobOffer

			]);
		});
	}

	#[Route(path: '/{id}', name: 'front_school_jobOffer_show', methods: ['GET'])]
	public function showAction(JobOffer $jobOffer): Response {
		return $this->schoolService->checkUnCompletedAccountBefore(function () use ($jobOffer) {
			return $this->render('frontSchoolJobOffer/show.html.twig', [
				'jobOffer' => $jobOffer,
				'school' => $this->schoolService->getSchool()
			]);
		});
	}

	#[Route(path: '/{id}/edit', name: 'front_school_jobOffer_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request, JobOffer $jobOffer): RedirectResponse|Response {
		return $this->schoolService->checkUnCompletedAccountBefore(function () use ($request, $jobOffer) {
			$school = $this->schoolService->getSchool();

			if ($jobOffer->getSchool() !== $school)
				throw new NotFoundHttpException('Aucune offre trouvée');

			$editForm = $this->createForm(JobOfferType::class, $jobOffer);
			$editForm->handleRequest($request);

			if ($editForm->isSubmitted() && $editForm->isValid()) {
				$offerDescription = $editForm->get('file')->getData();
				if ($offerDescription) {
					$offerDescriptionFileName = $this->fileUploader->upload($offerDescription, $jobOffer->getFilename());
					$jobOffer->setFilename($offerDescriptionFileName);
				}

				$jobOffer->setSchool($school);
				$jobOffer->setUpdatedDate(new \DateTime());
				$this->em->flush();

				return $this->redirectToRoute('front_school_jobOffer_show', ['id' => $jobOffer->getId()]);
			}

			return $this->render('frontSchoolJobOffer/edit.html.twig', [
				'school' => $this->schoolService->getSchool(),
				'edit_form' => $editForm->createView(),
				'jobOffer' => $jobOffer
			]);
		});
	}

	#[Route(path: '/delete/{id}', name: 'front_school_jobOffer_delete', methods: ['GET'])]
	public function deleteAction(Request $request, ?JobOffer $jobOffer): RedirectResponse {
		return $this->schoolService->checkUnCompletedAccountBefore(function () use ($request, $jobOffer) {
			if ($jobOffer->getSchool() !== $this->schoolService->getSchool())
				throw new NotFoundHttpException('Aucune offre trouvée');

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
			return $this->redirectToRoute('front_school_jobOffer_index');
		});
	}

}
