<?php

namespace App\Form;

use App\Entity\GeoLocation;
use App\Entity\Prefecture;
use App\Services\ActivityService;
use App\Services\CityService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use App\Entity\Country;
use App\Entity\SectorArea;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class GeoLocationType extends AbstractType {

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
		$selectedCountry = $options['selectedCountry'];
		$builder
			->add('locality')
			->add('road')
			->add('number')
			->add('country', EntityType::class, [
				'class' => Country::class,
				'required' => false,
				'placeholder' => 'menu.select_country',
				'attr' => ['class' => 'form-control', 'value' => ''],
				'query_builder' => function (EntityRepository $entityRepository) use ($selectedCountry) {
					if ($selectedCountry) {
						return $entityRepository->createQueryBuilder('sa')
							->where('sa.valid = true')
							->andWhere('sa.id = \'' . $selectedCountry . '\'')
							->orderBy('sa.name', 'ASC');
					} else {
						return $entityRepository->createQueryBuilder('sa')
							->where('sa.valid = true')
							->orderBy('sa.name', 'ASC');
					}
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
			->add('otherCity', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Veuillez renseigner la ville', 'placeholder' => 'menu.city'],
				'required' => false
			])
			->add('sectorArea', EntityType::class, [
				'class' => SectorArea::class,
				'required' => false,
				'placeholder' => 'menu.select',
				'attr' => ['class' => 'form-control'],
				'query_builder' => function (EntityRepository $entityRepository) {
					return $entityRepository->createQueryBuilder('sa')
						->orderBy('sa.name', 'ASC');
				}
			])
			->add('showCompanies', CheckboxType::class, ['attr' => [
				'class' => 'form-control',
				'label' => 'Afficher les entreprises ?'],
				'required' => false
			])
			->add('showSchools', CheckboxType::class, ['attr' => [
				'class' => 'form-control',
				'label' => 'Afficher les établissement ?'],
				'required' => false
			])
            ->add('showSearchPersonDegrees', CheckboxType::class, ['attr' => [
                'class' => 'form-control',
                'label' => 'Afficher les diplômés en recherche ?'],
                'required' => false
            ])
			->add('showOtherPersonDegrees', CheckboxType::class, ['attr' => [
				'class' => 'form-control',
				'label' => 'Afficher les autres diplômés ?'],
				'required' => false
			]);
		$this->cityService->addCity($builder, 'city', true);
		$this->activityService->addActivity($builder, 'activity', 'sectorArea', false);
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array('data_class' => GeoLocation::class))
			->setRequired(['selectedCountry']);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBlockPrefix(): string {
		return 'appbundle_geolocation';
	}

}
