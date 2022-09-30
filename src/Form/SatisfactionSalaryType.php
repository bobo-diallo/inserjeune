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
				'attr' => ['class' => 'form-control', 'placeholder' => 'Salaire Mensuel', 'min' => 0],
				'required' => true,
			])
			->add('daylySalary', IntegerType::class, [
				'attr' => ['class' => 'form-control', 'placeholder' => 'Salaire Journalier', 'min' => 0],
				'required' => false
			])
			->add('company', EntityType::class, [
				'class' => Company::class,
				'attr' => ['class' => 'form-control'],
				'required' => false,
				'placeholder' => 'Choisissez',
				'query_builder' => function (EntityRepository $entityRepository) use ($selectedCountry) {
					return $entityRepository->createQueryBuilder('sa')
						->where('sa.country = \'' . $selectedCountry . '\'')
						->orderBy('sa.name', 'ASC');
				}
			])
			->add('companyName', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Minimum 3 caractères', 'placeholder' => 'Nom de l\'Entreprise'],
				'required' => true
			])
			->add('companyCity', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Minimum 3 caractères', 'placeholder' => 'Ville'],
				'required' => true
			])
			->add('companyPhone', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Minimum 3 caractères', 'placeholder' => 'Numéro du standard'],
				'required' => true
			])
			->add('jobName', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Minimum 3 caractères', 'placeholder' => 'Fonction'],
				'required' => false
			])
			->add('jobStatus', ChoiceType::class, [
				'choices' => [
					'Ouvrier' => 'Ouvrier',
					'Ouvrier qualifié' => 'Ouvrier qualifié',
					'Technicien' => 'Technicien',
					'Technicien supérieur' => 'Technicien supérieur',
				],
				'placeholder' => 'Sélectionnez',
				'attr' => ['class' => 'form-control']
			])
			->add('jobTime', TextType::class, [
				'attr' => ['class' => 'datepicker form-control', 'placeholder' => 'Date d\'embauche'],
				'required' => true
			])
			->add('workHoursPerDay', ChoiceType::class, [
				'choices' => [
					'entre 1 et 4 heures' => 'entre 1 et 4 heures',
					'entre 5 et 8 heures' => 'entre 5 et 8 heures',
					'plus de 8 heures' => 'plus de 8 heures',
				],
				'attr' => ['class' => 'form-control'],
				'placeholder' => 'Sélectionnez',
				'required' => true
			])
			->add('jobSatisfied', CheckboxType::class, ['attr' => [
				'class' => 'form-control',
				'label' => 'Etes-vous satisfait de votre emploi ?'],
				'required' => false
			])
			->add('trainingSatisfied', CheckboxType::class, ['attr' => [
				'class' => 'form-control',
				'label' => 'Etes-vous satisfait de votre formation ?'],
				'required' => false
			])
			->add('contract', EntityType::class, [
				'class' => Contract::class,
				'attr' => ['class' => 'form-control'],
				'placeholder' => 'Choisissez'
			])
			->add('otherContract', TextType::class, [
				'attr' => ['class' => 'form-control', 'placeholder' => 'Type de contrat'],
				'required' => false
			])
			->add('jobNotFoundOther', TextType::class, ['attr' => [
				'class' => 'form-control',
				'data-error' => 'Autre raison d\'emploi non trouvé ?',
				'placeholder' => ' Raison'],
				'required' => false,
			])
			->add('currency', EntityType::class, [
				'class' => Currency::class,
				'attr' => ['class' => 'form-control'],
				'placeholder' => 'Sélectionnez la devise',
				'query_builder' => function (EntityRepository $entityRepository) {
					return $entityRepository->createQueryBuilder('sa')
						->orderBy('sa.name', 'ASC');
				}
			])
			->add('jobNotFoundReasons', EntityType::class, [
				'class' => JobNotFoundReason::class,
				'multiple' => true,
				'placeholder' => 'Pas de réponse',
				'attr' => ['class' => 'form-control'],
				'required' => false
			])
			->add('otherActivityName', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Veuillez renseigner le métier', 'placeholder' => ' Métier'],
				'required' => false
			])
			->add('createdDate')
			->add('sectorArea', EntityType::class, [
				'class' => SectorArea::class,
				'attr' => ['class' => 'form-control'],
				'required' => true,
				'placeholder' => 'Sélectionnez',
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
