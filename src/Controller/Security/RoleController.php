<?php

namespace App\Controller\Security;

use App\Form\RoleType;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Role;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/role')]
#[IsGranted('ROLE_ADMIN')]
class RoleController extends AbstractController {
	private EntityManagerInterface $em;
	private RoleRepository $roleRepository;

	public function __construct(
		EntityManagerInterface $em,
		RoleRepository         $roleRepository
	) {
		$this->em = $em;
		$this->roleRepository = $roleRepository;
	}

	#[Route('/', name: 'role_index', methods: ['GET'])]
	public function indexAction(): Response {
		return $this->render('role/index.html.twig', array(
			'roles' => $this->roleRepository->findAll(),
		));
	}

	#[Route('/new', name: 'role_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$role = new Role();
		$form = $this->createForm(RoleType::class, $role);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->em->persist($role);
			$this->em->flush();

			return $this->redirectToRoute('role_index');
		}

		return $this->render('role/new.html.twig', [
			'role' => $role,
			'form' => $form->createView(),
		]);
	}

	private function createDeleteForm(Role $role): Form {
		return $this->createFormBuilder()
			->setAction($this->generateUrl('role_delete', ['id' => $role->getId()]))
			->setMethod('DELETE')
			->getForm();
	}
}
