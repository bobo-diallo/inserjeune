<?php

namespace App\Form;

use App\Entity\Contract;
use App\Entity\Country;
use App\Entity\Image;
use App\Entity\JobOffer;
use App\Entity\SectorArea;
use App\Services\AcitivityService;
use App\Services\CityService;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JobOfferType extends AbstractType {

	private CityService $cityService;
	private AcitivityService $activityService;

	public function __construct(CityService $cityService, AcitivityService $activityService) {
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
			->add('description', TextareaType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Veuillez renseigner la description ', 'rows' => 6, 'placeholder' => 'Minimum 20 caractères'],
				'required' => true
			])
			->add('postedContact', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Veuillez renseigner le nom du contact ', 'placeholder' => 'Nom du contact'],
				'required' => false
			])
			->add('postedPhone', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Veuillez renseigner le numéro de téléphone ', 'placeholder' => 'Numéro de téléphone'],
				'required' => false
			])
			->add('postedEmail', EmailType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Veuillez renseigner l\'email ', 'placeholder' => 'Email pour postuler'],
				'required' => true
			])
			->add('coverLetter', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Minimum 3 caractères', 'placeholder' => 'Lettre de Motivation'],
				'required' => false
			])
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
				'required' => false
			])
			->add('country', EntityType::class, [
				'class' => Country::class,
				'choice_label' => 'name',
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
			->add('file', FileType::class, ['attr' => ['class' => 'form-control'], 'required' => false])
			->add('image', EntityType::class, [
				'class' => Image::class,
				'choice_label' => 'name',
				'required' => false,
				'attr' => ['class' => 'form-control']
			]);

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
