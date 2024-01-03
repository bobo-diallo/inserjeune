<?php

namespace App\Controller\Security;

use App\Entity\City;
use App\Entity\Region;
use App\Repository\UserRepository;
use App\Repository\RoleRepository;
use App\Repository\SchoolRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use App\Entity\Role;
use App\Form\UserType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
// use Symfony\Component\Validator\Constraints\Collection;
use Doctrine\ORM\PersistentCollection;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/user')]
#[Security("is_granted('ROLE_ADMIN') 
    or is_granted('ROLE_ADMIN_PAYS') 
    or is_granted('ROLE_ADMIN_REGIONS') 
    or is_granted('ROLE_DIPLOME')
")]
class UserController extends AbstractController {
	private EntityManagerInterface $em;
	private UserRepository $userRepository;
	private SchoolRepository $schoolRepository;
	private RoleRepository $roleRepository;
	private UserPasswordHasherInterface $hasher;
	private RequestStack $requestStack;
	private TranslatorInterface $translator;

	public function __construct(
		EntityManagerInterface      $em,
		UserRepository              $userRepository,
		RoleRepository              $roleRepository,
        SchoolRepository $schoolRepository,
		UserPasswordHasherInterface $hasher,
		RequestStack                $requestStack,
		TranslatorInterface $translator
	) {
		$this->em = $em;
		$this->userRepository = $userRepository;
		$this->roleRepository = $roleRepository;
        $this->schoolRepository = $schoolRepository;
		$this->hasher = $hasher;
		$this->requestStack = $requestStack;
		$this->translator = $translator;
	}

	#[Route(path: '/', name: 'user_index', methods: ['GET'])]
	public function indexAction(): Response {
        $allUsers = $this->userRepository->getAllUser();
        $users = [];
        if($this->getUser()->hasRole('ROLE_ADMIN')) {
            $users = $allUsers;
        } else if($this->getUser()->hasRole('ROLE_ADMIN_PAYS')) {
            foreach ($allUsers as $user) {
                if($user->roles() != 'ROLE_ADMIN_PAYS') {
                    if ($user->country() == $this->getUser()->getCountry()->getName()) {
                        $users[] = $user;
                    }
                }
            }
        } else if($this->getUser()->hasRole('ROLE_ADMIN_REGIONS')) {
            foreach ($allUsers as $user) {
                if(($user->roles() != 'ROLE_ADMIN_PAYS') && ($user->roles() != 'ROLE_ADMIN_REGIONS')) {
                    $regions = $this->getUser()->getAdminRegions();
                    foreach ($regions as $region) {
                        if ($user->region()) {
                            if ($region->getName() == $user->region()) {
                                $users[] = $user;
                            }
                        }
                    }
                }
            }
        }

		return $this->render('user/index.html.twig', [
			'users' => $users
		]);
	}

	#[Route(path: '/new', name: 'user_new', methods: ['POST', 'GET'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$user = new User();
        // $form = $this->createForm(UserType::class, $user);
        // //Adaptation for DBTA
        // if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'true') {
            $form = $this->createForm(UserType::class, $user);
        // }
        $roles = $this->roleRepository->findAll();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
            //Adaptation for DBTA
            if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'true') {
                if($user->getRegion()) {
                    $user->setCountry($user->getRegion()->getCountry());
                } elseif (count($user->getAdminRegions())>0) {
                    $user->setCountry($user->getAdminRegions()[0]->getCountry());
                }
            }

            //Only for Principal
            if($user->hasRole(Role::ROLE_PRINCIPAL)) {
                if($user->getSchool())
                    $user->setPrincipalSchool($user->getSchool()->getId());
            }

			$user->setPassword($this->hasher->hashPassword($user, $user->getPlainPassword()));
			$user->setEnabled(true);

			$this->em->persist($user);
			$this->em->flush();
			return $this->redirectToRoute('user_show', ['id' => $user->getId()]);
		}

        //Change access of Roles function of current Administrator level
        if($this->getUser()->hasRole('ROLE_ADMIN_PAYS')) {
            $this->changeRoleFormAdminPays ($form);
        } else if($this->getUser()->hasRole('ROLE_ADMIN_REGIONS')) {
            $this->changeRoleFormAdminRegions ($form);
        }

		return $this->render('user/new.html.twig', [
			'user' => $user,
			'form' => $form->createView(),
            'roles' => $roles
		]);
	}

	#[Route(path: '/{id}', name: 'user_show', methods: ['GET'])]
	public function showAction(User $user2): Response {
		return $this->render('user/show.html.twig', [
			'user2' => $user2
		]);
	}

	#[Route(path: '/{id}/edit', name: 'user_edit', methods: ['GET', 'POST', 'PUT'])]
	public function editAction(Request $request, User $user): RedirectResponse|Response {

        //Only for Principal
        if($user->hasRole(Role::ROLE_PRINCIPAL)) {
            if($user->getPrincipalSchool()) {
                $school = $this->schoolRepository->find($user->getPrincipalSchool());
                if($school) {
                    $user->setSchool($school);
                }
            }
        }

        $editForm = $this->createForm(UserType::class, $user);
        $roles = $this->roleRepository->findAll();
		$editForm->handleRequest($request);

        //Change access of Roles function of current Administrator level
        if($this->getUser()->hasRole('ROLE_ADMIN_PAYS')) {
            $this->changeRoleFormAdminPays ($editForm);
        } else if($this->getUser()->hasRole('ROLE_ADMIN_REGIONS')) {
            $this->changeRoleFormAdminRegions ($editForm);
        }

		if ($editForm->isSubmitted() && $editForm->isValid()) {
            //Adaptation for DBTA
            if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'true') {
                if($user->getRegion()) {
                    $user->setCountry($user->getRegion()->getCountry());
                } elseif (count($user->getAdminRegions())>0) {
                    $user->setCountry($this->getFirstRegionCityNotNull($user->getAdminRegions())->getCountry());
                } elseif (count($user->getAdminCities())>0) {
                    $user->setRegion($this->getFirstRegionCityNotNull($user->getAdminCities())->getRegion());
                    $user->setCountry($user->getRegion()->getCountry());
                }
            }

            //Only for Principal
            if($user->hasRole(Role::ROLE_PRINCIPAL)) {
                if($user->getSchool())
                    $user->setPrincipalSchool($user->getSchool()->getId());
            }

			$user->setPassword($this->hasher->hashPassword($user, $user->getPlainPassword()));
			$this->em->flush();
			return $this->redirectToRoute('user_show', ['id' => $user->getId()]);
		}

		return $this->render('user/edit.html.twig', [
			'user' => $user,
			'edit_form' => $editForm->createView(),
            'roles' => $roles
		]);
	}

	#[Route(path: '/delete/{id}', name: 'user_delete', methods: ['GET'])]
	public function deleteAction(Request $request, ?User $user): RedirectResponse {
		if (array_key_exists('HTTP_REFERER', $request->server->all())) {
			if ($user) {
                if($user->getSchool()) {
                    $principals = $this->userRepository->findByPrincipalSchool($user->getSchool()->getId());
                    foreach ($principals as $principal) {
                        $this->em->remove($principal);
                        // var_dump("principal:",$principal->getId());
                    }
                }
				$this->removeRelations($user);
				$this->em->remove($user);
                // var_dump("user:",$user->getId());die();
				$this->em->flush();
				$this->addFlash('success', $this->translator->trans('flashbag.the_deletion_is_done_successfully'));
			} else {
				$this->addFlash('warning', $this->translator->trans('flashbag.unable_to_delete_the_country'));
				return $this->redirect($request->server->all()['HTTP_REFERER']);
			}
		}
		return $this->redirectToRoute('user_index');
	}

	/**
	 * @param User $user
	 */
	private function removeRelations(User $user) {
		if ($user->getPersonDegree()) {
			foreach ($user->getPersonDegree() as $diplome) {
				$this->em->remove($diplome);
			}
		}
		if ($user->getCompany()) {
			foreach ($user->getCompany() as $company) {
				$this->em->remove($company);
			}
		}
		if ($user->getSchool()) {
			foreach ($user->getSchool() as $school) {
				$this->em->remove($school);
			}
		}
	}
    private function changeRoleFormAdminPays ($form) {
        $form->remove('profils');
        $form->add('profils', EntityType::class, [
            'class' => Role::class,
            'multiple' => true,
            'query_builder' => function (RoleRepository $r) {
                return $r->createQueryBuilder('ig')
                    ->Where('ig.role = \'ROLE_ADMIN_REGIONS\'')
                    ->orWhere('ig.role = \'ROLE_ADMIN_VILLES\'')
                    ->orWhere('ig.role = \'ROLE_LEGISLATEUR\'')
                    ->orWhere('ig.role = \'ROLE_PRINCIPAL\'');
            },
            'attr' => ['class' => 'form-control select2',]
        ]);
    }

    private function changeRoleFormAdminRegions ($form) {
        $form->remove('profils');
        $form->add('profils', EntityType::class, [
            'class' => Role::class,
            'multiple' => true,
            'query_builder' => function (RoleRepository $r) {
                return $r->createQueryBuilder('ig')
                    ->Where('ig.role = \'ROLE_ADMIN_VILLES\'')
                    ->orWhere('ig.role = \'ROLE_PRINCIPAL\'');
            },
            'attr' => ['class' => 'form-control select2',]
        ]);
    }

    /**
     * Fix Bug if null region store with select2 JS function
     * @param Collection $collection
     * @return Region|City
     */
    private function getFirstRegionCityNotNull (Collection $collection): Region|City {
        $FirstObjectNotNull = null;
        for ($i = 0 ; $i < count($collection) ; $i++) {
            if($collection[$i]) {
                $FirstObjectNotNull = $collection[$i];
                $i = count($collection);
            }
        }
        return $FirstObjectNotNull;
    }
}
