<?php

namespace App\Services;

use App\Entity\Activity;
use App\Entity\SectorArea;
use App\Repository\ActivityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityRepository;

class ActivityService {

	private EntityManager $entityManager;
	private ActivityRepository $activityRepository;

	public function __construct(
		EntityManagerInterface $entityManager,
		ActivityRepository $activityRepository
	) {
		$this->entityManager = $entityManager;
		$this->activityRepository = $activityRepository;
	}

	/**
	 * Permet d'ajouter sur le formualaire un champs facultatif de type sectorArea qu'il faut d'abord selectionner pour
	 * pouvoir selectionner le champs activity
	 *
	 * @param FormBuilderInterface $builder
	 * @param string $activityName : Nom du champs activities sur le formulaire
	 * @param string $sectorAreaName : Nom du champs sectorArea sur le formulaire
	 * @param bool $multiple
	 */
	public function addActivity(
		FormBuilderInterface $builder,
		string               $activityName,
		string               $sectorAreaName = 'sectorArea',
		bool                 $multiple = true
	): void {
		$builder->get($sectorAreaName)->addEventListener(
			FormEvents::POST_SUBMIT,
			function (FormEvent $event) use ($activityName, $multiple, $builder) {
				$form = $event->getForm();
				/** @var SectorArea $sectorArea */
				$sectorArea = $form->getData();
				// Recupérer les activities par defaut
				$methodName = sprintf('get%s', ucfirst($activityName));
				$selectedActivities = $builder->getData()->$methodName();

				// $activities = ($sectorArea) ? $sectorArea->getActivities() : [];
				$activities = ($sectorArea) ? $sectorArea->getActivities() : $selectedActivities;
				$this->addActivityField($form->getParent(), $activities, $activityName, $multiple);
			}
		);

		$builder->addEventListener(
			FormEvents::POST_SET_DATA,
			function (FormEvent $event) use ($activityName, $multiple) {
				$data = $event->getData();

				$methodName = sprintf('get%s', ucfirst($activityName));
				/** @var ArrayCollection $activities */
				$activities = $data->$methodName();
				$form = $event->getForm();

				// La condition à vérifier depend de l'activité (si c'est une collection d'activity ou un objet activity)
				$condition = ($multiple) ? ($activities->count() > 0) : ($activities);

				if ($condition) {
					$this->addActivityField($form, $activities, $activityName, $multiple);
				} else {
					$this->addActivityField($form, null, $activityName, $multiple);
				}
			}
		);

		// Important pour les options "Autre" sur les metiers
		// Supprimer l'option "Autre" avec id = "" sur les activities
		$builder->addEventListener(
			FormEvents::PRE_SUBMIT,
			function (FormEvent $event) use ($activityName) {
				$data = $event->getData();

				if (array_key_exists($activityName, $data)) {
					$arrayActivities = $data[$activityName];
					if (is_array($arrayActivities)) {
						foreach ($arrayActivities as $key => $value) {
							if ($value == null) unset($arrayActivities[$key]);
						}
					}
					$data[$activityName] = $arrayActivities;
					$event->setData($data);
				}
			}
		);
	}

	/**
	 * Rajoute un champ region au formulaire
	 */
	private function addActivityField(
		FormInterface $form,
		              $activities = null,
		string        $activityName = null,
		bool          $multiple = false
	): void {
		$builder = ($multiple) ?
			$this->addMultipleActivity($form, $activities, $activityName) :
			$this->addUniqueActivity($form, $activities, $activityName);

		$form->add($builder->getForm());
	}

	private function addUniqueActivity(
		FormInterface &$form,
		              $activities = null,
		?string        $activityName = null
	): FormBuilderInterface {

		if ($activities instanceof Activity) {
			$data[] = $activities;
		} else $data = $activities;

		return $form->getConfig()->getFormFactory()->createNamedBuilder(
			$activityName,
			EntityType::class,
			null,
			[
				'class' => Activity::class,
				'choice_label' => 'name',
				'placeholder' => $activities ? 'Selectionnez le métier/filière' : 'Selectionnez le secteur d\'activité',
				'auto_initialize' => false,
				'choices' => $data ? $data : [],
				'attr' => ['class' => 'form-control'],
				'required' => false,
				'placeholder' => 'Sélectionnez',
				'query_builder' => function (EntityRepository $entityRepository) {
					return $entityRepository->createQueryBuilder('a')
						->orderBy('a.name', 'ASC');
				}
			]
		);
	}

	private function addMultipleActivity(FormInterface &$form, $activities = null, ?string $activityName = null): FormBuilderInterface {
		return $form->getConfig()->getFormFactory()->createNamedBuilder(
			$activityName,
			EntityType::class,
			null,
			[
				'class' => Activity::class,
				'choice_label' => 'name',
				'multiple' => true,
				'placeholder' => $activities ? 'Selectionnez le métier/filière' : 'Selectionnez le secteur d\'activité',
				'auto_initialize' => false,
				'choices' => $activities ? $activities : [],
				'data' => $activities,
				'attr' => ['class' => 'form-control'],
				'required' => false,
				'query_builder' => function (EntityRepository $entityRepository) {
					return $entityRepository->createQueryBuilder('a')
						->orderBy('a.name', 'ASC');
				}
			]
		);
	}

	/**
	 * @return Activity[]|array
	 */
	public function getAllActivities(): array {
		return $this->activityRepository->findAll();
	}
}
