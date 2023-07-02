<?php

namespace App\Form;

use App\Entity\Contract;
use App\Entity\Currency;
use App\Entity\JobNotFoundReason;
use App\Entity\SatisfactionSalary;
use App\Entity\Company;
use App\Entity\SectorArea;
use App\Services\ActivityService;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SatisfactionSalaryType extends AbstractType {

	private ActivityService $activityService;

	public function __construct(ActivityService $activityService) {
		$this->activityService = $activityService;
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$selectedCountry  = $options['selectedCountry'] ?? null;
		$builder
			->add('monthlySalary', IntegerType::class, [
				'attr' => ['class' => 'form-control', 'placeholder' => 'satisfaction_creator.monthly_pay', 'min' => 0],
				'required' => true,
			])
			->add('daylySalary', IntegerType::class, [
				'attr' => ['class' => 'form-control', 'placeholder' => 'satisfaction_salary.daily_pay', 'min' => 0],
				'required' => false
			])
			->add('company', EntityType::class, [
				'class' => Company::class,
				'attr' => ['class' => 'form-control'],
				'required' => false,
				'placeholder' => 'menu.select',
				'query_builder' => function (EntityRepository $entityRepository) use ($selectedCountry) {
					return $entityRepository->createQueryBuilder('sa')
						->where('sa.country = \'' . $selectedCountry . '\'')
						->orderBy('sa.name', 'ASC');
				}
			])
			->add('companyName', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Minimum 3 caractères', 'placeholder' => 'company.name_of_the_company'],
				'required' => true
			])
			->add('companyCity', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Minimum 3 caractères', 'placeholder' => 'menu.city'],
				'required' => true
			])
			->add('companyPhone', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Minimum 3 caractères', 'placeholder' => 'menu.standard_number'],
				'required' => true
			])
			->add('jobName', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Minimum 3 caractères', 'placeholder' => 'satisfaction_search.function'],
				'required' => false
			])
			->add('jobStatus', ChoiceType::class, [
				'choices' => [
					'company.worker' => 'Ouvrier',
					'company.qualified_worker' => 'Ouvrier qualifié',
					'company.technician' => 'Technicien',
					'company.senior_technician' => 'Technicien supérieur',
				],
				'placeholder' => 'menu.select',
				'attr' => ['class' => 'form-control']
			])
			->add('jobTime', TextType::class, [
				'attr' => ['class' => 'datepicker form-control', 'placeholder' => 'menu.hiring_date'],
				'required' => true
			])
			->add('workHoursPerDay', ChoiceType::class, [
				'choices' => [
					'menu.between_1_and_4_hours' => 'entre 1 et 4 heures',
					'menu.between_5_and_8_hours' => 'entre 5 et 8 heures',
					'menu.more_than_8_hours' => 'plus de 8 heures',
				],
				'attr' => ['class' => 'form-control'],
				'placeholder' => 'menu.select',
				'required' => true
			])
			->add('jobSatisfied', CheckboxType::class, ['attr' => [
				'class' => 'form-control',
				'label' => 'satisfaction_salary.satisfied_with_job'],
				'required' => false
			])
			->add('trainingSatisfied', CheckboxType::class, ['attr' => [
				'class' => 'form-control',
				'label' => 'satisfaction_salary.satisfied_with_training'],
				'required' => false
			])
			->add('contract', EntityType::class, [
				'class' => Contract::class,
				'attr' => ['class' => 'form-control'],
				'placeholder' => 'menu.select'
			])
			->add('otherContract', TextType::class, [
				'attr' => ['class' => 'form-control', 'placeholder' => 'job.type_of_contract'],
				'required' => false
			])
			->add('jobNotFoundOther', TextType::class, ['attr' => [
				'class' => 'form-control',
				'data-error' => 'Autre raison d\'emploi non trouvé ?',
				'placeholder' => ' menu.reason'],
				'required' => false,
			])
			->add('currency', EntityType::class, [
				'class' => Currency::class,
				'attr' => ['class' => 'form-control'],
				'placeholder' => 'menu.select_currency',
				'query_builder' => function (EntityRepository $entityRepository) {
					return $entityRepository->createQueryBuilder('sa')
						->orderBy('sa.name', 'ASC');
				}
			])
			->add('jobNotFoundReasons', EntityType::class, [
				'class' => JobNotFoundReason::class,
				'multiple' => true,
				'placeholder' => 'menu.no_answer',
				'attr' => ['class' => 'form-control'],
				'required' => false
			])
			->add('otherActivityName', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Veuillez renseigner le métier', 'placeholder' => ' satisfaction_search.job'],
				'required' => false
			])
			->add('createdDate')
			->add('sectorArea', EntityType::class, [
				'class' => SectorArea::class,
				'attr' => ['class' => 'form-control'],
				'required' => true,
				'placeholder' => 'menu.select',
				'query_builder' => function (EntityRepository $entityRepository) {
					return $entityRepository->createQueryBuilder('sa')
						->orderBy('sa.name', 'ASC');
				}
			]);
		$this->activityService->addActivity($builder, 'activity', 'sectorArea', false);
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(['data_class' => SatisfactionSalary::class])
			->setRequired(['selectedCountry']);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBlockPrefix(): string {
		return 'appbundle_satisfactionsalary';
	}

}
