<?php

namespace App\Services;

use App\Entity\City;
use App\Entity\PersonDegree;
use App\Entity\Region;
use App\Entity\Role;
use App\Entity\User;
use App\Model\PersonDegreeReadOnly;
use App\Repository\RegionRepository;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\Column\TwigColumn;
use Omines\DataTablesBundle\DataTable;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class PersonDegreeDatatableService {
	private DataTableFactory $dataTableFactory;
	private TranslatorInterface $translator;
	private ParameterBagInterface $parameter;
	private RegionRepository $regionRepository;

	public function __construct(
		DataTableFactory $dataTableFactory,
		TranslatorInterface $translator,
		ParameterBagInterface $parameter,
		RegionRepository $regionRepository
	) {
		$this->dataTableFactory = $dataTableFactory;
		$this->translator = $translator;
		$this->parameter = $parameter;
		$this->regionRepository = $regionRepository;
	}

	public function generateDatatable(
		Request $request,
		User $currentUser,
		?int $schoolId
	): DataTable {
		$userCountry = $currentUser->getCountry();
		$countryId = $userCountry ? $userCountry->getId() : null;

		$userRegions = [];
		$userCities = [];

		if (!$schoolId) {
			if ($currentUser->hasRole(Role::ROLE_ADMIN_PAYS)) {
				$userCountry = $currentUser->getCountry();
				$userRegions = $this->regionRepository->findByCountry($userCountry->getId());
			} else if ($currentUser->hasRole(Role::ROLE_ADMIN_REGIONS)) {
				$userRegions = $currentUser->getAdminRegions();
			} else if ($currentUser->hasRole(Role::ROLE_ADMIN_VILLES)) {
				$userCities = $currentUser->getAdminCities();
			}
		}

		$findAll = false;
		if (count($userRegions) == 0 && count($userCities) == 0 && $this->parameter->get('env(STRUCT_PROVINCE_COUNTRY_CITY)') != 'true') {
			$findAll = true;
		}

		$table = $this->dataTableFactory->create();
		if ($currentUser->hasAnyRole(
			Role::ROLE_ADMIN,
			Role::ROLE_ADMIN_PAYS,
			Role::ROLE_ADMIN_REGIONS,
			Role::ROLE_ADMIN_VILLES
		)) {
			$table->add('actions', TwigColumn::class, [
				'label' => 'Actions',
				'className' => 'no-print',
				'template' => 'persondegree/datatable/index/_actions_column.html.twig',
				'orderable' => true,
			])
				->add('id', TextColumn::class, [
					'label' => 'ID',
					'orderable' => true,
				]);
		}

		if ($currentUser->hasRole(Role::ROLE_ETABLISSEMENT)) {
			$table->add('actions', TwigColumn::class, [
				'label' => 'Actions',
				'className' => 'no-print',
				'template' => 'persondegree/datatable/index/_actions_school_column.html.twig',
				'orderable' => true,
				'field' => 'p.checkSchool'
			]);
		}

		if ($currentUser->hasAnyRole(Role::ROLE_ETABLISSEMENT, Role::ROLE_PRINCIPAL)) {
			$table->add('registration', TwigColumn::class, [
				'label' => $this->translator->trans('menu.registration'),
				'template' => 'persondegree/datatable/index/_registration_column.html.twig',
				'orderable' => true,
				'searchable' => true,
				'field' => 'p.registrationStudentSchool'
			]);
		}

		$table
			->add('firstname', TextColumn::class, [
				'label' => $this->translator->trans('menu.firstname'),
				'orderable' => true,
				'searchable' => true,
			])
			->add('lastname', TextColumn::class, [
				'label' => $this->translator->trans('menu.name'),
				'orderable' => true,
				'searchable' => true,
			])
			->add('sex', TwigColumn::class, [
				'label' => $this->translator->trans('menu.gender'),
				'template' => 'persondegree/datatable/index/_sex_column.html.twig',
				'orderable' => true,
				'field' => 'p.sex'
			])
			->add('check_person_degree_satisfaction', TwigColumn::class, [
				'label' => $this->translator->trans('survey.survey_status_with_tag'),
				'template' => 'persondegree/datatable/index/_check_satisfaction_column.html.twig',
				'orderable' => true,
				'field' => 'p.type'
			])
			->add('date_of_birth_with_tag', DateTimeColumn::class, [
				'label' => $this->translator->trans('menu.date_of_birth_with_tag'),
				'format' => 'd/m/Y',
				'orderable' => true,
				'searchable' => true,
				'field' => 'p.birthDate'
			])
			->add('cell_phone', TwigColumn::class, [
				'label' => $this->translator->trans('menu.cell_phone'),
				'template' => 'persondegree/datatable/index/_phone_column.html.twig',
				'orderable' => true,
				'searchable' => true,
				'field' => 'p.phoneMobile1'
			])
			->add('email', TwigColumn::class, [
				'label' => $this->translator->trans('menu.email'),
				'template' => 'persondegree/datatable/index/_email_column.html.twig',
				'field' => 'p.email',
				'orderable' => true,
				'searchable' => true,
			]);

		if ($currentUser->hasAnyRole(
			Role::ROLE_ADMIN,
			Role::ROLE_ETABLISSEMENT,
			Role::ROLE_LEGISLATOR,
			Role::ROLE_DIRECTOR,
			Role::ROLE_ADMIN_PAYS,
		)) {
			if ($this->parameter->get('env(STRUCT_PROVINCE_COUNTRY_CITY)') == 'true') {
				$table->add('region', TextColumn::class, [
					'label' => $this->translator->trans('menu.region'),
					'render' => function ($value, PersonDegreeReadOnly $context) {
						return $context->countryName;
					},
					'orderable' => true,
					'searchable' => true,
					'field' => 'region.name'
				]);
			} else {
				$table->add('country', TextColumn::class, [
					'label' => $this->translator->trans('menu.country'),
					'render' => function ($value, PersonDegreeReadOnly $context) {
						return $context->countryName;
					},
					'orderable' => true,
					'searchable' => true,
					'field' => 'country.name'
				]);
			}
		}

		if ($currentUser->hasAnyRole(
			Role::ROLE_ADMIN,
			Role::ROLE_LEGISLATOR,
			Role::ROLE_DIRECTOR,
			Role::ROLE_ADMIN_PAYS,
			Role::ROLE_ADMIN_REGIONS,
			Role::ROLE_ADMIN_VILLES,
		)) {
			$table->add('school', TwigColumn::class, [
				'label' => $this->translator->trans('menu.establishment'),
				'template' => 'persondegree/datatable/index/_school_column.html.twig',
				'orderable' => true,
				'searchable' => true,
				'field' => 'p.otherSchool'
			]);

			if ($this->parameter->get('env(PREFECTURE_BETWEEN_REGION_CITY)') == 'true') {
				$table->add('prefectureName', TextColumn::class, [
					'label' => $this->translator->trans('menu.prefecture'),
					'field' => 'prefecture.name',
					'render' => function ($value, PersonDegreeReadOnly $context) {
						return $context->prefectureName;
					},
					'orderable' => true,
					'searchable' => true,
				]);
			} else {
				$table->add('city', TextColumn::class, [
					'label' => $this->translator->trans('menu.city'),
					'render' => function ($value, PersonDegreeReadOnly $context) {
						return $context->cityName;
					},
					'orderable' => true,
					'searchable' => true,
					'field' => 'city.name'
				]);
			}
		}

		if ($currentUser->hasAnyRole(
			Role::ROLE_ETABLISSEMENT,
			Role::ROLE_PRINCIPAL,
		)) {
			$table
				->add('degree', TextColumn::class, [
					'label' => $this->translator->trans('menu.degree'),
					'field' => 'degree.name',
					'render' => function ($value, PersonDegreeReadOnly $context) {
						return $context->degreeName;
					},
					'orderable' => true,
				])
				->add('activity', TextColumn::class, [
					'label' => $this->translator->trans('menu.branch'),
					'field' => 'activity.name',
					'render' => function ($value, PersonDegreeReadOnly $context) {
						return $context->activityName;
					},
					'orderable' => true,
					// 'searchable' => true,
				]);
		}

		$table->add('month_year_degree', TwigColumn::class, [
			'label' => $this->translator->trans('degree.month_year_degree_with_tag'),
			'template' => 'persondegree/datatable/index/_month_year_degree_column.html.twig',
		]);

		$table->add('created_date', DateTimeColumn::class, [
			'label' => $this->translator->trans('menu.created_date_with_tag'),
			'format' => 'd M Y H:i',
			'field' => 'p.createdDate',
			'orderable' => true,
		]);

		$table->add('current_situation', TwigColumn::class, [
			'label' => $this->translator->trans('menu.current_situation_with_tag'),
			'template' => 'persondegree/datatable/index/_current_situation_column.html.twig',
		]);

		$table
			->createAdapter(ORMAdapter::class, [
				'entity' => PersonDegree::class,
				'query' => function (QueryBuilder $builder) use ($schoolId, $findAll, $currentUser, $userCities, $userRegions, $userCountry,$countryId) {
					$selectFields = '
					    p.id,
					    p.firstname,
					    p.lastname,
					    p.sex,
					    p.email, 
					    p.createdDate,
					    p.checkSchool,
					    p.lastDegreeYear,
					    p.lastDegreeMonth,
					    p.type,
					    p.otherSchool,
					    p.phoneMobile1,
					    p.registrationStudentSchool,
					    p.birthDate,
					    activity.id,
					    activity.name,
					    degree.id,
					    degree.name,
					    city.id,
					    city.name, ';

						if ($findAll) {
							$selectFields .= 'country.id, country.name, ';
						} else {
							$selectFields .= 'region.id, region.name, ';
						}

						$selectFields .= '
					    school.id,
					    school.name,
					    prefecture.id,
					    prefecture.name,
					    school_city.name,
					    (SELECT COUNT(DISTINCT sse.id) FROM App\Entity\SatisfactionSearch sse WHERE sse.personDegree = p.id),
					    (SELECT COUNT(DISTINCT ss.id) FROM App\Entity\SatisfactionSalary ss WHERE ss.personDegree = p.id),
					    (SELECT COUNT(DISTINCT sc.id) FROM App\Entity\SatisfactionCreator sc WHERE sc.personDegree = p.id)';

					$builder
						->select('NEW ' . PersonDegreeReadOnly::class . "($selectFields)")
						->from(PersonDegree::class, 'p');
					if ($findAll) {
						$builder->leftJoin('p.country', 'country');
					} else {
						$builder->leftJoin('p.region', 'region');
					}

					$builder
						->leftJoin('p.addressCity', 'city')
						->leftJoin('p.degree', 'degree')
						// ->leftJoin('p.sectorArea', 'sectorArea')
						->leftJoin('p.activity', 'activity')
						->leftJoin('p.school', 'school')
						->leftJoin('school.city', 'school_city')
						->leftJoin('city.prefecture', 'prefecture');

					if ($schoolId) {
						$builder->andWhere('p.school = :school')->setParameter('school', $schoolId);;
					} else {
						if (count($userRegions) > 0) {
							$regionIds = array_map(function (Region $region) {
								return $region->getId();
							}, $userRegions);
							$builder->andWhere('p.region IN (:regionIds)')->setParameter('regionIds', $regionIds);
						} else if (count($userCities) > 0) {
							$citiesId = array_map(function (City $city) {
								return $city->getId();
							}, $userCities);
							$builder->andWhere('p.city IN (:citiesId)')->setParameter('citiesId', $citiesId);
						} else {
							if ($this->parameter->get('env(STRUCT_PROVINCE_COUNTRY_CITY)') == 'true') {
								$userRegion = $currentUser->getRegion();
								$regionIds = $userRegion ? [$userRegion->getId()] : [];
								$builder->andWhere('p.region IN (:regionIds)')->setParameter('regionIds', $regionIds);
							} else if ($countryId) {
								$builder->andWhere('p.country = :country')->setParameter('country', $countryId);
							}
						}

						if ($currentUser->getPrincipalSchool()) {
							$builder->andWhere('p.school = :school')->setParameter('school', $currentUser->getPrincipalSchool());;
						}
					}
				},
				'criteria' => [
					function (QueryBuilder $builder) use ($request, $findAll) {
						$search = $request->request->all()['search'] ?? [];
						$searchValue = $search['value'] ?? null;

						if (is_string($searchValue) && $searchValue !== '') {
							$critters = [
								$builder->expr()->like('p.id', ':search'),
								$builder->expr()->like('p.firstname', ':search'),
								$builder->expr()->like('p.lastname', ':search'),
								$builder->expr()->like('p.email', ':search'),
								$builder->expr()->like('p.phoneMobile1', ':search'),
								$builder->expr()->like('p.registrationStudentSchool', ':search'),
								$builder->expr()->like('p.otherSchool', ':search'),
								$builder->expr()->like('degree.name', ':search'),
								$builder->expr()->like('activity.name', ':search'),
								// $builder->expr()->like('sectorArea.name', ':search'),
								$builder->expr()->like('city.name', ':search'),
								$builder->expr()->like('school.name', ':search'),
								$builder->expr()->like('prefecture.name', ':search'),
							];

							if ($findAll) {
								$critters[] = $builder->expr()->like('country.name', ':search');
							} else {
								$critters[] = $builder->expr()->like('region.name', ':search');
							}

							$builder
								->andWhere($builder->expr()->orX(...$critters))
								->setParameter('search', '%' . $searchValue . '%');
						}
					}
				],
			]);

		return $table;
	}
}
