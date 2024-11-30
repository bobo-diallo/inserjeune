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
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use App\Entity\Role;
use App\Form\UserType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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

	#[Route(path: '/', name: 'user_index', methods: ['GET', 'POST'])]
	public function indexAction(
		Request $request,
		DataTableFactory $dataTableFactory,
		UrlGeneratorInterface $urlGenerator,
		TranslatorInterface $translator,
		ParameterBagInterface $parameter
	): Response {

		/** @var User $currentUser */
		$currentUser = $this->getUser();

		$table = $dataTableFactory->create()
			->add('actions', TextColumn::class, [
				'label' => 'Actions',
				'className' => 'no-print',
				'render' => function ($value, $context) use ($urlGenerator) {
					return sprintf(
						'
				 <a href="%s"><img src="/build/images/icon/edit_16.png" alt="edit" class="action-icon"></a>
                 <a href="%s"><img src="/build/images/icon/show_16.png" alt="show" class="action-icon"></a>
                 <a class="danger" onclick="deleteElement(\'%s\')"><img src="/build/images/icon/delete_16.png" alt="delete" class="action-icon"></a>',
						$urlGenerator->generate('user_edit', ['id' => $context->getId()]),
						$urlGenerator->generate('user_show', ['id' => $context->getId()]),
						$urlGenerator->generate('user_delete', ['id' => $context->getId()])
					);
				},
				'orderable' => false,
				'searchable' => false,
			])
			->add('id', TextColumn::class, ['label' => 'ID'])
			->add('country', TextColumn::class, [
				'label' => $translator->trans('menu.country'),
				'field' => 'country.name'
			]);

		// For DBTA
		if ($parameter->get('env(STRUCT_PROVINCE_COUNTRY_CITY)') == 'true') {
			$table->add('region', TextColumn::class, [
				'label' => $translator->trans('menu.region'),
				'field' => 'region.name',
			]);
		}

		$table
			->add('phone', TextColumn::class, ['label' => $translator->trans('menu.phone')])
			->add('username', TextColumn::class, ['label' => $translator->trans('menu.pseudo')])
			->add('email', TextColumn::class, ['label' => $translator->trans('menu.email')])
			->add('profils', TextColumn::class, [
				'label' => $translator->trans('menu.roles'),
				'render' => function ($value, $context) use ($currentUser) {
					return implode(', ', $context->getRoles());
				}
			])
			->createAdapter(ORMAdapter::class, [
				'entity' => User::class,
				'query' => function (QueryBuilder $builder) use($currentUser) {
					$builder
						// ->select('u.id, u.phone, u.username, u.email, country.name, region.name')
						->select('u, country, region')
						->from(User::class, 'u')
						->leftJoin('u.country', 'country')
						->leftJoin('u.region', 'region');

					if ($currentUser->hasRole('ROLE_ADMIN_PAYS')) {
						$builder
							->andWhere('u.country = :country')
							->setParameter('country', $currentUser->getCountry()->getId());
					} else if ($currentUser->hasRole('ROLE_ADMIN_REGIONS')) {
						$regionIds = $currentUser
							->getAdminRegions()
							->map(function (Region $region) {
								return $region->getId();
							});
						$builder
							->andWhere('u.region IN (:regionIds)')
							->setParameter('regionIds', $regionIds);
					}
				},
			])
			->handleRequest($request);

		if ($table->isCallback()) {
			return $table->getResponse();
		}

		return $this->render('user/index.html.twig', [
			'datatable' => $table
		]);
	}

	#[Route(path: '/new', name: 'user_new', methods: ['POST', 'GET'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$user = new User();
        $form = $this->createForm(UserType::class, $user);
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

            // TO BE CHECK FOR DBTA
            if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'false') {
                if ($user->hasRole(Role::ROLE_ADMIN_REGIONS)) {
                    if($user->getAdminRegions()) {
                        $user->setCountry($user->getAdminRegions()[0]->getCountry());
                    }
                }

                if ($user->hasRole(Role::ROLE_ADMIN_VILLES)) {
                    $user->setCountry($user->getAdminCities()[0]->getRegion()->getCountry());
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

            // TO BE CHECK FOR DBTA
            if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'false') {
                if ($user->hasRole(Role::ROLE_ADMIN_REGIONS)) {
                    if($user->getAdminRegions()) {
                        $user->setCountry($user->getAdminRegions()[0]->getCountry());
                    }
                }

                if ($user->hasRole(Role::ROLE_ADMIN_VILLES)) {
                    $user->setCountry($user->getAdminCities()[0]->getRegion()->getCountry());
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
