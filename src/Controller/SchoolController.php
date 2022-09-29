<?php

namespace App\Controller;

use App\Entity\School;
use App\Form\SchoolType;
use App\Repository\SchoolRepository;
use App\Repository\UserRepository;
use App\Services\ActivityService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/school')]
#[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_LEGISLATEUR')")]
class SchoolController extends AbstractController {
	private EntityManagerInterface $em;
	private SchoolRepository $schoolRepository;
	private ActivityService $activityService;
	private UserRepository $userRepository;

	public function __construct(
		EntityManagerInterface $em,
		SchoolRepository       $schoolRepository,
		ActivityService        $activityService,
		UserRepository         $userRepository
	) {
		$this->em = $em;
		$this->schoolRepository = $schoolRepository;
		$this->activityService = $activityService;
		$this->userRepository = $userRepository;
	}

	#[Route(path: '/', name: 'school_index', methods: ['GET'])]
	public function indexAction(): Response {
		$userCountry = $this->getUser()->getCountry();

		$schools = ($userCountry) ?
			$this->schoolRepository->findByCountry($userCountry) :
			$this->schoolRepository->findAll();

		return $this->render('school/index.html.twig', [
			'schools' => $schools
		]);
	}

	#[Route(path: '/new', name: 'school_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$school = new School();
		$form = $this->createForm(SchoolType::class, $school);
		$form->handleRequest($request);
		$selectedCountry = $school->getCountry();

		if ($form->isSubmitted() && $form->isValid()) {
			$school->setCreatedDate(new \DateTime());
			$school->setUpdatedDate(new \DateTime());
			$dnsServer = $this->getParameter('dnsServer');
			$school->setUpdatedDate(new \DateTime());

			if (php_uname('n') != $dnsServer) {
				$school->setClientUpdateDate(new \DateTime());
			}

			$this->em->persist($school);
			$this->em->flush();

			return $this->redirectToRoute('school_show', ['id' => $school->getId()]);
		}

		return $this->render('school/new.html.twig', [
			'school' => $school,
			'form' => $form->createView(),
			'allActivities' => $this->activityService->getAllActivities(),
			'selectedCountry' => $selectedCountry
		]);
	}

	#[Route(path: '/{id}', name: 'school_show', methods: ['GET'])]
	public function showAction(School $school): Response {
		return $this->render('school/show.html.twig', [
			'school' => $school
		]);
	}

	#[IsGranted('ROLE_ADMIN')]
	#[Route(path: '/{id}/edit', name: 'school_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request, School $school): Response {
		$createdDate = $school->getCreatedDate();
		$editForm = $this->createForm(SchoolType::class, $school);
		$editForm->handleRequest($request);
		$selectedCountry = $school->getCountry();

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$school->setCreatedDate($createdDate);
			if ($school->getCreatedDate() == null) {
				if ($school->getUpdatedDate()) {
					$school->setCreatedDate($school->getUpdatedDate());
				} else {
					$school->setCreatedDate(new \DateTime());
				}
			}
			$school->setUpdatedDate(new \DateTime());
			$dnsServer = $this->getParameter('dnsServer');

			if (php_uname('n') != $dnsServer) {
				$school->setClientUpdateDate(new \DateTime());
			}

			$currentUser = $this->userRepository->getFromCompany($school->getId());
			if (count($currentUser) > 0) {
				$school->setUser($currentUser[0]);
			}
			$school->setMapsAddress(null);

			$this->em->persist($school);
			$this->em->flush();

			return $this->redirectToRoute('school_show', ['id' => $school->getId()]);
		}

		return $this->render('school/edit.html.twig', [
			'school' => $school,
			'edit_form' => $editForm->createView(),
			'allActivities' => $this->activityService->getAllActivities(),
			'selectedCountry' => $selectedCountry
		]);
	}

	#[Route(path: '/delete/{id}', name: 'school_delete', methods: ['GET'])]
	public function deleteElementAction(Request $request, ?School $school): RedirectResponse {
		if (array_key_exists('HTTP_REFERER', $request->server->all())) {
			if ($school) {
				$this->em->remove($school);
				$this->em->flush();
				$this->addFlash('success', 'La suppression est faite avec success');
			} else {
				$this->addFlash('warning', 'Impossible de suppression de l\'école');
				return $this->redirect($request->server->all()['HTTP_REFERER']);
			}
		}
		return $this->redirectToRoute('school_index');
	}
}
