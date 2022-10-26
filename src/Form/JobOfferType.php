<?php

namespace App\Form;

use App\Entity\Contract;
use App\Entity\Country;
use App\Entity\Image;
use App\Entity\JobOffer;
use App\Entity\SectorArea;
use App\Services\ActivityService;
use App\Services\CityService;
use Doctrine\ORM\EntityRepository;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class JobOfferType extends AbstractType {

	private CityService $cityService;
	private ActivityService $activityService;

	public function __construct(CityService $cityService, ActivityService $activityService) {
		$this->cityService = $cityService;
		$this->activityService = $activityService;
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('title', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Veuillez renseigner le titre', 'placeholder' => 'Titre de l\'offre'],
				'required' => true
			])
			->add('description', CKEditorType::class, ['required' => true])
			->add('candidateProfile', CKEditorType::class)
			->add('closedDate', TextType::class, [
				'attr' => ['class' => 'datepicker form-control', 'placeholder' => 'Date d\'expiration' ],
				'required' => true
			])
			->add('postedContact', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Veuillez renseigner le nom du contact ', 'placeholder' => 'Nom du contact'],
				'required' => true
			])
			->add('postedPhone', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Veuillez renseigner le numéro de téléphone ', 'placeholder' => 'Numéro de téléphone'],
				'required' => false
			])
			->add('postedEmail', EmailType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Veuillez renseigner l\'email ', 'placeholder' => 'Email pour postuler'],
				'required' => true
			])
			// ->add('coverLetter', TextType::class, [
			// 	'attr' => ['class' => 'form-control', 'data-error' => 'Minimum 3 caractères', 'placeholder' => 'Lettre de Motivation'],
			// 	'required' => false
			// ])
			->add('sectorArea', EntityType::class, [
				'class' => SectorArea::class,
				'required' => true,
				'placeholder' => 'Sélectionnez',
				'attr' => ['class' => 'form-control'],
				'query_builder' => function (EntityRepository $entityRepository) {
					return $entityRepository->createQueryBuilder('sa')
						->orderBy('sa.name', 'ASC');
				}
			])
			->add('otherActivity', TextType::class, ['attr' => [
				'class' => 'form-control',
				'data-error' => 'Autre métier ?',
				'placeholder' => ' métier'],
				'required' => false,
			])
			->add('contract', EntityType::class, [
				'class' => Contract::class,
				'attr' => ['class' => 'form-control'],
				'required' => true
			])
			->add('country', EntityType::class, [
				'class' => Country::class,
				'choice_label' => 'name',
				'required' => true,
				'attr' => ['class' => 'form-control'],
				'query_builder' => function (EntityRepository $entityRepository) {
					return $entityRepository->createQueryBuilder('sa')
						->where('sa.valid = true')
						->orderBy('sa.name', 'ASC');
				}
			])
			->add('otherCity', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Minimum 3 caractères', 'placeholder' => 'Autre ville'],
				'required' => false
			])
			->add('file', FileType::class, [
				'label' => 'Joindre la description de l\'offre (PDF)',
				'mapped' => false,
				'required' => false,
				'constraints' => [
					new File([
						'maxSize' => '2048k',
						'mimeTypes' => [
							'application/pdf',
							'application/x-pdf',
						],
						'mimeTypesMessage' => 'Please upload a valid PDF document',
					])
				],
			])
		;

		$this->cityService->addCity($builder, 'city', true);
		$this->activityService->addActivity($builder, 'activity', 'sectorArea', false);
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => JobOffer::class,
		));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBlockPrefix(): string {
		return 'appbundle_joboffer';
	}
}
