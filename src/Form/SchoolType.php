<?php

namespace App\Form;

use App\Entity\Degree;
use App\Entity\Prefecture;
use App\Entity\School;
use App\Entity\SectorArea;
use App\Services\ActivityService;
use App\Services\CityService;
use Symfony\Component\Form\AbstractType;
use App\Entity\Image;
use App\Entity\Country;
use App\Entity\SocialNetwork;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class SchoolType extends AbstractType {
	private ActivityService $activityService;
	private CityService $cityService;

	public function __construct(ActivityService $activityService, CityService $cityService) {
		$this->activityService = $activityService;
		$this->cityService = $cityService;
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('name', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Minimum 3 caractères', 'placeholder' => 'menu.name'],
				'required' => true
			])
			->add('type', ChoiceType::class, [
				'choices' => [
					'menu.select' => '',
                    'Centre de Formation Professionnelle (CFP)' => 'centre de formation professionnelle',
					'Lycée Technique (LT)' => 'lycée technique',
				],
				'attr' => ['class' => 'form-control'],
				'required' => true
			])
			->add('agreeRgpd', CheckboxType::class, [
				'attr' => ['class' => 'form-control', 'label' => 'agréement rgpd ?'],
				'required' => false
			])
			->add('description', TextareaType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Minimum 3 ' . 'menu.characters',
                    'placeholder' => 'menu.description'],
				'required' => false
			])
			->add('addressNumber', IntegerType::class, [
				'attr' => ['class' => 'form-control', 'placeholder' => 'menu.address_number'],
				'required' => false
			])
			->add('addressLocality', TextType::class, [
				'attr' => ['class' => 'form-control',
                    'data-error' => 'Minimum 3 ' . 'menu.characters',
                    'placeholder' => 'menu.address'],
				'required' => false
			])
			->add('addressRoad', TextType::class, [
				'attr' => ['class' => 'form-control',
                    'data-error' => 'Minimum 3 ' . 'menu.characters',
                    'placeholder' => 'menu.street'],
				'required' => false
			])
			->add('phoneStandard', TextType::class, [
				'attr' => ['class' => 'form-control',
                    'data-error' => 'error.invalid_phone',
                    'placeholder' => 'menu.login_phone'],
				'required' => true
			])
			->add('phoneOther', TextType::class, [
				'attr' => ['class' => 'form-control',
                    'data-error' => 'error.invalid_phone',
                    'placeholder' => 'menu.other_phone_number'],
				'required' => false
			])
			->add('email', EmailType::class, [
				'attr' => ['class' => 'form-control',
                    'data-error' => 'error.invalid_mail',
                    'placeholder' => 'menu.email'],
				'required' => true
			])
			->add('latitude', TextType::class, [
				'attr' => ['class' => 'form-control',
                    'data-error' => 'menu.complete' . " " . 'menu.latitude',
                    'placeholder' => 'menu.latitude'],
				'required' => false
			])
			->add('longitude', TextType::class, [
				'attr' => ['class' => 'form-control',
                    'data-error' => 'menu.complete' . " " . 'menu.longitude',
                    'placeholder' => 'menu.longitude'],
				'required' => false
			])
			->add('locationMode', CheckboxType::class, [
				'attr' => ['class' => 'form-control', 'label' => 'Location Mode ?'],
				'required' => false
			])
			->add('image', EntityType::class, [
				'class' => Image::class,
				'attr' => ['class' => 'form-control'],
				'required' => false
			])
			->add('registration', TextType::class, [
				'attr' => ['class' => 'form-control',
                    'placeholder' => 'menu.registration'],
				'required' => false
			])
			->add('socialNetworks', EntityType::class, [
				'class' => SocialNetwork::class,
				'multiple' => true,
			])
			->add('mapsAddress', TextType::class, ['attr' => ['hidden' => 'hidden'], 'required' => false])
			->add('country', EntityType::class, [
				'class' => Country::class,
				'required' => true,
				'placeholder' => 'menu.select',
				'attr' => ['class' => 'form-control', 'value' => ''],
				'query_builder' => function (EntityRepository $entityRepository) {
					return $entityRepository->createQueryBuilder('sa')
						->where('sa.valid = true')
						->orderBy('sa.name', 'ASC');
				}
			])
			->add('degrees', EntityType::class, [
				'class' => Degree::class,
				'attr' => ['class' => 'form-control'],
				'required' => true,
				'multiple' => true,
				'placeholder' => 'menu.select',
			])
			->add('sectorArea1', EntityType::class, [
				'class' => SectorArea::class,
				'attr' => ['class' => 'form-control school_sectorArea'],
				'required' => true,
				'placeholder' => 'menu.select',
				'query_builder' => function (EntityRepository $entityRepository) {
					return $entityRepository->createQueryBuilder('sa')
						->orderBy('sa.name', 'ASC');
				}
			])
			->add('sectorArea2', EntityType::class, [
				'class' => SectorArea::class,
				'attr' => ['class' => 'form-control school_sectorArea'],
				'required' => false,
				'placeholder' => 'menu.select',
				'query_builder' => function (EntityRepository $entityRepository) {
					return $entityRepository->createQueryBuilder('sa')
						->orderBy('sa.name', 'ASC');
				}
			])
			->add('sectorArea3', EntityType::class, [
				'class' => SectorArea::class,
				'attr' => ['class' => 'form-control school_sectorArea'],
				'required' => false,
				'placeholder' => 'menu.select',
				'query_builder' => function (EntityRepository $entityRepository) {
					return $entityRepository->createQueryBuilder('sa')
						->orderBy('sa.name', 'ASC');
				}
			])
			->add('sectorArea4', EntityType::class, [
				'class' => SectorArea::class,
				'attr' => ['class' => 'form-control  school_sectorArea'],
				'required' => false,
				'placeholder' => 'menu.select',
				'query_builder' => function (EntityRepository $entityRepository) {
					return $entityRepository->createQueryBuilder('sa')
						->orderBy('sa.name', 'ASC');
				}
			])
			->add('sectorArea5', EntityType::class, [
				'class' => SectorArea::class,
				'attr' => ['class' => 'form-control  school_sectorArea'],
				'required' => false,
				'placeholder' => 'menu.select',
				'query_builder' => function (EntityRepository $entityRepository) {
					return $entityRepository->createQueryBuilder('sa')
						->orderBy('sa.name', 'ASC');
				}
			])
			->add('sectorArea6', EntityType::class, [
				'class' => SectorArea::class,
				'attr' => ['class' => 'form-control  school_sectorArea'],
				'required' => false,
				'placeholder' => 'menu.select',
				'query_builder' => function (EntityRepository $entityRepository) {
					return $entityRepository->createQueryBuilder('sa')
						->orderBy('sa.name', 'ASC');
				}
			])
            ->add('prefecture', EntityType::class, [
                'required' => false,
                'class' => Prefecture::class,
                'placeholder' => 'menu.prefecture',
                'attr' => [
                    'class' => 'form-control',
                    'data-error' => 'error.unauthorized_or_unknown_prefecture',
                ],
                'query_builder' => function (EntityRepository $entityRepository) {
                    return $entityRepository->createQueryBuilder('b')
                        ->orderBy('b.name', 'ASC');
                }
            ])
			->add('otherDegree', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'menu.other_degree'], 'required' => false])
			->add('otherActivity1', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'menu.other_activity'], 'required' => false])
			->add('otherActivity2', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'menu.other_activity'], 'required' => false])
			->add('otherActivity3', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'menu.other_activity'], 'required' => false])
			->add('otherActivity4', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'menu.other_activity'], 'required' => false])
			->add('otherActivity5', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'menu.other_activity'], 'required' => false])
			->add('otherActivity6', TextType::class, ['attr' => ['class' => 'form-control', 'placeholder' => 'menu.other_activity'], 'required' => false]);

		$this->cityService->addCity($builder, 'city', true);
		$this->activityService->addActivity($builder, 'activities1', 'sectorArea1');
		$this->activityService->addActivity($builder, 'activities2', 'sectorArea2');
		$this->activityService->addActivity($builder, 'activities3', 'sectorArea3');
		$this->activityService->addActivity($builder, 'activities4', 'sectorArea4');
		$this->activityService->addActivity($builder, 'activities5', 'sectorArea5');
		$this->activityService->addActivity($builder, 'activities6', 'sectorArea6');
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => School::class
		));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBlockPrefix(): string {
		return 'appbundle_school';
	}
}
