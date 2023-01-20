<?php

namespace App\Controller;

use App\Entity\PersonDegree;
use App\Entity\School;
use App\Form\PersonDegreeType;
use App\Repository\PersonDegreeRepository;
use App\Repository\UserRepository;
use App\Services\ActivityService;
use App\Services\PersonDegreeService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route(path: '/persondegree')]
#[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_LEGISLATEUR')")]
class PersonDegreeController extends AbstractController {
	private EntityManagerInterface $em;
	private PersonDegreeRepository $personDegreeRepository;
	private ActivityService $activityService;
	private UserRepository $userRepository;
    private PersonDegreeService $personDegreeService;

	public function __construct(
		EntityManagerInterface $em,
		PersonDegreeRepository $personDegreeRepository,
		ActivityService        $activityService,
		UserRepository         $userRepository,
		PersonDegreeService    $personDegreeService
	) {
		$this->em = $em;
		$this->personDegreeRepository = $personDegreeRepository;
		$this->activityService = $activityService;
		$this->userRepository = $userRepository;
        $this->personDegreeService = $personDegreeService;
	}

	#[Route(path: '/', name: 'persondegree_index', methods: ['GET'])]
	public function indexAction(): Response {
		$userCountry = $this->getUser()->getCountry();
		$countryId = $userCountry ? $userCountry->getId() : null;

		return $this->render('persondegree/index.html.twig', [
			'personDegrees' => $this->personDegreeRepository->getAllPersonDegree($countryId)
		]);
	}

	#[Route(path: '/new', name: 'persondegree_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$personDegree = new Persondegree();
		$personDegree->setLocationMode(true);
		$selectedCountry = $personDegree->getCountry();

		$form = $this->createForm(PersonDegreeType::class, $personDegree, ['selectedCountry' => $selectedCountry->getId()]);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$personDegree->setCreatedDate(new \DateTime());
			$personDegree->setUpdatedDate(new \DateTime());

			$dnsServer = $this->getParameter('dnsServer');
			if (php_uname('n') != $dnsServer)
				$personDegree->setClientUpdateDate(new \DateTime());

			$this->em->persist($personDegree);
			$this->em->flush();

			return $this->redirectToRoute('persondegree_show', ['id' => $personDegree->getId()]);
		}

		return $this->render('persondegree/new.html.twig', [
			'personDegree' => $personDegree,
			'form' => $form->createView(),
			'allActivities' => $this->activityService->getAllActivities(),
			'selectedCountry' => $selectedCountry
		]);
	}

	#[Route(path: '/{id}', name: 'persondegree_show', methods: ['GET'])]
	public function showAction(PersonDegree $personDegree): Response {
		return $this->render('persondegree/show.html.twig', [
			'personDegree' => $personDegree
		]);
	}

	#[IsGranted('ROLE_ADMIN')]
	#[Route(path: '/{id}/edit', name: 'persondegree_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request, PersonDegree $personDegree): RedirectResponse|Response {
		$selectedCountry = $personDegree->getCountry();
		$createdDate = $personDegree->getCreatedDate();

		$editForm = $this->createForm(PersonDegreeType::class, $personDegree, ['selectedCountry' => $selectedCountry->getId()]);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$currentUser = $this->userRepository->getFromPersonDegree($personDegree->getId());

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

			if (count($currentUser) > 0) {
				$personDegree->setUser($currentUser[0]);
			}
			$this->em->persist($personDegree);
			$this->em->flush();
			return $this->redirectToRoute('persondegree_show', ['id' => $personDegree->getId()]);
		}

		return $this->render('persondegree/edit.html.twig', [
			'personDegree' => $personDegree,
			'edit_form' => $editForm->createView(),
			'allActivities' => $this->activityService->getAllActivities(),
			'selectedCountry' => $selectedCountry
		]);
	}

	#[Route(path: '/delete/{id}', name: 'persondegree_delete', methods: ['GET'])]
	public function deleteElementAction(Request $request, ?PersonDegree $personDegree): RedirectResponse {
		if (array_key_exists('HTTP_REFERER', $request->server->all())) {
            if($personDegree) {
                $user = $personDegree->getUser();
                if ($user) {
                    $this->personDegreeService->removeRelations($user);
                    $this->em->remove($user);
                    $this->em->flush();
                    $this->addFlash('success', 'La suppression de l\'utilisateur est faite avec success');

                } else {
                    $this->addFlash('warning', 'Impossible de suppression l\'utilisateur');
                    return $this->redirect($request->server->all()['HTTP_REFERER']);
                }
            } else {
                $this->addFlash('warning', 'Impossible de suppression le diplômé');
                return $this->redirect($request->server->all()['HTTP_REFERER']);
            }
		}
		return $this->redirectToRoute('persondegree_index');
	}

	#[Route(path: '/filters/{id}/school', name: 'persondegree_filters_school', methods: ['GET'])]
	public function getFiltersBySchoolAction(School $school): JsonResponse {
		$listDegree = [];
		/** @var Degree $degree */
		foreach ($school->getDegrees() as $degree) {
			$listDegree[] = [
				'id' => $degree->getId(),
				'name' => $degree->getName()
			];
		}

		$listSectorArea = [];
		$listSectorArea[] = ['id' => $school->getSectorArea1()->getId(), 'name' => $school->getSectorArea1()->getName()];
		if ($school->getSectorArea2())
			$listSectorArea[] = ['id' => $school->getSectorArea2()->getId(), 'name' => $school->getSectorArea2()->getName()];
		if ($school->getSectorArea3())
			$listSectorArea[] = ['id' => $school->getSectorArea3()->getId(), 'name' => $school->getSectorArea3()->getName()];
		if ($school->getSectorArea4())
			$listSectorArea[] = ['id' => $school->getSectorArea4()->getId(), 'name' => $school->getSectorArea4()->getName()];

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
		return new JsonResponse([$listDegree, $listSectorArea, $listActivity]);
	}
}
