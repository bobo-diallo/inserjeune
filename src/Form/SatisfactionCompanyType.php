<?php

namespace App\Form;

use App\Entity\SatisfactionCompany;
use App\Entity\SectorArea;
use App\Services\ActivityService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class SatisfactionCompanyType extends AbstractType {
	private ActivityService $activityService;

	public function __construct(ActivityService $activityService) {
		$this->activityService = $activityService;
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('salaryNumber', ChoiceType::class, [
				'choices' => [
					'0' => '0',
					'menu.from_1_to_5' => 'de 1 à 5',
					'menu.from_6_to_10' => 'de 6 à 10',
					'menu.from_11_to_20' => 'de 11 à 20',
					'menu.from_21_to_50' => 'de 21 à 50',
					'menu.greater_than_50' => '> 50',
				],
				'placeholder' => 'menu.select',
				'attr' => ['class' => 'form-control']
			])
			->add('apprenticeNumber', ChoiceType::class, [
				'choices' => [
					'0' => '0',
					'menu.from_1_to_5' => 'de 1 à 5',
					'menu.from_6_to_10' => 'de 6 à 10',
					'menu.greater_than_10' => '> 10',
				],
				'placeholder' => 'menu.select',
				'attr' => ['class' => 'form-control']
			])
			->add('studentNumber', ChoiceType::class, [
				'choices' => [
					'0' => '0',
					'menu.from_1_to_5' => 'de 1 à 5',
					'menu.from_6_to_10' => 'de 6 à 10',
					'menu.greater_than_10' => '> 10',
				],
				'placeholder' => 'menu.select',
				'attr' => ['class' => 'form-control']
			])
			->add('workerSectorArea', EntityType::class, [
				'class' => SectorArea::class,
				'attr' => ['class' => 'form-control'],
				'required' => false,
				'placeholder' => 'menu.select',
				'query_builder' => function (EntityRepository $entityRepository) {
					return $entityRepository->createQueryBuilder('wsa')
						->orderBy('wsa.name', 'ASC');
				}
			])
			->add('otherWorkerJob', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Veuillez renseigner le nom de la formation', 'placeholder' => 'satisfaction_creator.professions_and_specialization'],
				'required' => false
			])
			->add('technicianSectorArea', EntityType::class, [
				'class' => SectorArea::class,
				'attr' => ['class' => 'form-control'],
				'required' => false,
				'placeholder' => 'menu.select',
				'query_builder' => function (EntityRepository $entityRepository) {
					return $entityRepository->createQueryBuilder('e')
						->orderBy('e.name', 'ASC');
				}
			])
			->add('otherTechnicianJob', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Veuillez renseigner le nom de la formation', 'placeholder' => 'satisfaction_creator.professions_and_specialization'],
				'required' => false
			])
			->add('levelSkill', ChoiceType::class, [
				'choices' => [
					'satisfaction_creator.very_unsatisfactory' => 'très insatisfaisant',
					'satisfaction_creator.unsatisfactory' => 'insatisfaisant',
					'satisfaction_creator.satisfying_between_the_two' => 'satisfaisant ; entre les deux',
					'satisfaction_creator.good' => 'bon',
					'satisfaction_creator.excellent' => 'excellent',
				],
				'expanded' => true,
				// 'choices_as_values' => false,
				'attr' => ['class' => 'form-check']
			])
			->add('levelGlobalSkill', ChoiceType::class, [
				'expanded' => true,
				'choices' => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
				],
				'attr' => ['class' => 'form-control']
			])
			->add('levelTechnicalSkill', ChoiceType::class, [
				'expanded' => true,
				'choices' => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
				],
				'attr' => ['class' => 'form-control']
			])
			->add('levelCommunicationHygieneHealthEnvSkill', ChoiceType::class, [
				'expanded' => true,
				'choices' => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
				],
				'attr' => ['class' => 'form-control']
			])
			->add('levelOtherSkill', ChoiceType::class, [
				'expanded' => true,
				'required' => false,
				'choices' => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
				],
				'placeholder' => false,
				'attr' => ['class' => 'form-control'],
			])
			->add('levelOtherName', TextType::class, [
				'attr' => ['class' => 'form-text', 'data-error' => 'Veuillez renseigner le nom de la compétence', 'placeholder' => 'js.other_skill'],
				'required' => false
			])
			->add('hiringSameProfile', CheckboxType::class, ['attr' => [
				'class' => 'form-control',
				'label' => 'Seriez-vous prêt à engager des diplômés ayant le même profil / cursus scolaire que celui / ceux que vous avez embauché(s) ?'],
				'required' => false
			])
			->add('completeTraining', CheckboxType::class, ['attr' => [
				'class' => 'form-control',
				'label' => 'en cas de lacunes, seriez-vous prêts à compléter la formation du ou des jeune(s) embauché(s) ?'],
				'required' => false
			])
			->add('completeGlobalTraining', CheckboxType::class, ['attr' => [
				'class' => 'form-control',
				'label' => ''],
				'required' => false
			])
			->add('completeTechnicalTraining', CheckboxType::class, ['attr' => [
				'class' => 'form-control',
				'label' => ''],
				'required' => false
			])
			->add('completeCommunicationHygieneHealthEnvTraining', CheckboxType::class, ['attr' => [
				'class' => 'form-control',
				'label' => ''],
				'required' => false
			])
			->add('completeOtherTraining', CheckboxType::class, ['attr' => [
				'class' => 'form-control',
				'label' => ''],
				'required' => false
			])
			->add('hiring6MonthsWorker', ChoiceType::class, [
				'choices' => [
					'0' => '0',
					'menu.from_1_to_5' => 'de 1 à 5',
					'menu.from_6_to_10' => 'de 6 à 10',
					'menu.greater_than_10' => '> 10',
				],
				'placeholder' => 'menu.select',
				'attr' => ['class' => 'form-control']
			])
			->add('hiring6MonthsTechnician', ChoiceType::class, [
				'choices' => [
					'0' => '0',
					'menu.from_1_to_5' => 'de 1 à 5',
					'menu.from_6_to_10' => 'de 6 à 10',
					'menu.greater_than_10' => '> 10',
				],
				'placeholder' => 'menu.select',
				'attr' => ['class' => 'form-control']
			])
			->add('hiring6MonthsApprentice', ChoiceType::class, [
				'choices' => [
					'0' => '0',
					'menu.from_1_to_5' => 'de 1 à 5',
					'menu.from_6_to_10' => 'de 6 à 10',
					'menu.greater_than_10' => '> 10',
				],
				'placeholder' => 'menu.select',
				'attr' => ['class' => 'form-control']
			])
			->add('hiring6MonthsStudent', ChoiceType::class, [
				'choices' => [
					'0' => '0',
					'menu.from_1_to_5' => 'de 1 à 5',
					'menu.from_6_to_10' => 'de 6 à 10',
					'menu.greater_than_10' => '> 10',
				],
				'placeholder' => 'menu.select',
				'attr' => ['class' => 'form-control']
			])
			->add('createdDate');

		$this->activityService->addActivity($builder, 'workerActivities', 'workerSectorArea');
		$this->activityService->addActivity($builder, 'technicianActivities', 'technicianSectorArea');
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => SatisfactionCompany::class
		));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBlockPrefix(): string {
		return 'appbundle_satisfactioncompany';
	}

}
