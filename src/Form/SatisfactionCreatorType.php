<?php

namespace App\Form;

use App\Entity\SatisfactionCreator;
use App\Entity\JobNotFoundReason;
use App\Entity\SectorArea;
use App\Entity\Currency;
use App\Services\ActivityService;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class SatisfactionCreatorType extends AbstractType {

	private ActivityService $activityService;

	public function __construct(ActivityService $activityService) {
		$this->activityService = $activityService;
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('sectorArea', EntityType::class, [
				'class' => SectorArea::class,
				'attr' => ['class' => 'form-control'],
				'required' => true,
				'placeholder' => 'menu.select',
				'query_builder' => function (EntityRepository $entityRepository) {
					return $entityRepository->createQueryBuilder('sa')
						->orderBy('sa.name', 'ASC');
				}
			])
			->add('otherActivity', TextType::class, ['attr' => [
				'class' => 'form-control',
				'data-error' => 'Autre métier ?',
				'placeholder' => 'menu.job'],
				'required' => false,
			])
			->add('legalCompany', CheckboxType::class, ['attr' => [
				'class' => 'form-control',
				'label' => 'existence juridique ?'],
				'required' => false
			])
			->add('monthlySalary', IntegerType::class, [
				'attr' => ['class' => 'form-control', 'placeholder' => 'satisfaction_creator.monthly_pay', 'min' => 0],
				'required' => true,
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
			->add('usefulTraining', CheckboxType::class, ['attr' => [
				'class' => 'form-control',
				'label' => 'Votre Formation a telle été utile pour l\'entreprise ?'],
				'required' => false
			])
			->add('jobNotFoundReasons', EntityType::class, [
				'class' => JobNotFoundReason::class,
				'multiple' => true,
				'attr' => ['class' => 'form-control'],
				'required' => false
			])
			->add('jobNotFoundOther', TextType::class, ['attr' => [
				'class' => 'form-control',
				'data-error' => 'Autre raison d\'emploi non trouvé ?',
				'placeholder' => 'menu.reason'],
				'required' => false,
			])
			->add('createdDate');

		$this->activityService->addActivity($builder, 'activities');
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => SatisfactionCreator::class
		));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBlockPrefix(): string {
		return 'appbundle_satisfactioncreator';
	}

}
