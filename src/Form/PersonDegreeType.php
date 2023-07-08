<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Contract;
use App\Entity\Country;
use App\Entity\Degree;
use App\Entity\PersonDegree;
use App\Entity\School;
use App\Entity\SectorArea;
use App\Entity\User;
use App\Services\ActivityService;
use App\Services\CityService;
use App\Services\PersonDegreeService;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonDegreeType extends AbstractType {
	private CityService $cityService;
	private ActivityService $activityService;
	private PersonDegreeService $degreeService;

	public function __construct(
		CityService         $cityService,
		ActivityService     $activityService,
		PersonDegreeService $degreeService) {
		$this->cityService = $cityService;
		$this->activityService = $activityService;
		$this->degreeService = $degreeService;
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$selectedCountry = $options['selectedCountry'];
		$builder
			->add('status', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Veuillez renseigner le statut', 'placeholder' => 'Statut'],
				'required' => true
			])
			->add('agreeRgpd', CheckboxType::class, [
				'attr' => ['class' => 'form-control', 'label' => 'agréement rgpd ?'],
				'required' => false
			])
			->add('type', ChoiceType::class, [
				'choices' => array_flip($this->degreeService->getTypes()),
				'attr' => ['class' => 'form-control'],
				'placeholder' => 'menu.select',
				'required' => true
			])
			->add('sex', ChoiceType::class, [
				'choices' => [
					'menu.a_man' => 'un homme',
					'menu.a_woman' => 'une femme',
				],
				'attr' => ['class' => 'form-control'],
				'placeholder' => 'menu.select',
				'required' => true
			])
			->add('lastDegreeYear', ChoiceType::class, [
				'choices' => $this->getYears('2010'),
				'attr' => ['class' => 'form-control'],
				'placeholder' => 'menu.select',
				'required' => true
			])
			->add('lastDegreeMonth', ChoiceType::class, [
				'choices' => [
					'menu.under_study' => '0',
					'date.january' => '1',
					'date.febuary' => '2',
					'date.march' => '3',
					'date.april' => '4',
					'date.may' => '5',
					'date.june' => '6',
					'date.july' => '7',
					'date.august' => '8',
					'date.september' => '9',
					'date.october' => '10',
					'date.november' => '11',
					'date.december' => '12',
				],
				'attr' => ['class' => 'form-control'],
				'placeholder' => 'menu.select',
				'required' => true
			])
			->add('previousEndedContract', TextType::class, [
				'attr' => ['class' => 'single-daterange form-control'],
				'required' => false
			])
			->add('monthlySalary', IntegerType::class, [
				'attr' => ['class' => 'form-control', 'placeholder' => 'Salaire'],
				'required' => false
			])
			->add('firstname', TextType::class, [
				'attr' => ['class' => 'form-control',
                    'data-error' => 'Minimum 3 ' . 'menu.characters',
                    'placeholder' => 'menu.firstname'],
				'required' => true
			])
			->add('lastname', TextType::class, [
				'attr' => ['class' => 'form-control',
                    'data-error' => 'Minimum 3 ' . 'menu.characters',
                    'placeholder' => 'menu.name'],
				'required' => true
			])
			->add('birthDate', TextType::class, [
				'attr' => ['class' => 'datepicker form-control',
                    'placeholder' => 'menu.date_of_birth'],
				'required' => true
			])
			->add('addressNumber', IntegerType::class, [
				'attr' => ['class' => 'form-control',
                    'placeholder' => 'menu.address_number'],
				'required' => false
			])
			->add('addressLocality', TextType::class, [
				'attr' => ['class' => 'form-control',
                    'data-error' => 'Minimum 3 ' . 'menu.characters',
                    'placeholder' => 'menu.location'],
				'required' => false
			])
            ->add('addressDiaspora', TextType::class, [
                'attr' => ['class' => 'form-control',
                    'data-error' => 'Minimum 3 ' . 'menu.characters',
                    'placeholder' => 'menu.address_diaspora'],
                'required' => false
            ])
            ->add('diaspora', CheckboxType::class, [
                'attr' => ['class' => 'form-control', 'label' => 'Diaspora ?'],
                'required' => false
            ])
            ->add('residenceCountry', EntityType::class, [
                'required' => false,
                'class' => Country::class,
                'placeholder' => 'graduate.select_your_residence_country',
                'attr' => [
                    'class' => 'form-control',
                    'data-error' => 'error.unauthorized_or_unknown_country',
                ],
                'query_builder' => function (EntityRepository $entityRepository) {
                    return $entityRepository->createQueryBuilder('b')
                        ->orderBy('b.name', 'ASC');
                }
            ])
			->add('addressRoad', TextType::class, [
				'attr' => ['class' => 'form-control',
                    'data-error' => 'Minimum 3 ' . 'menu.characters',
                    'placeholder' => 'menu.street'],
				'required' => false
			])
			->add('otherCity', TextType::class, [
				'attr' => ['class' => 'form-control',
                    'placeholder' => 'city.other_city'],
				'required' => false
			])
			->add('phoneMobile1', TextType::class, [
				'attr' => ['class' => 'form-control',
                    'data-error' => 'error.invalid_phone',
                    'placeholder' => 'menu.login_phone'],
				'required' => true
			])
			->add('phoneHome', TextType::class, [
				'attr' => ['class' => 'form-control',
                    'data-error' => 'error.invalid_phone',
                    'placeholder' => 'menu.other_phone_country_code_number'],
				'required' => false
			])
			->add('phoneMobile2', TextType::class, [
				'attr' => ['class' => 'form-control',
                    'data-error' => 'error.invalid_phone',
                    'placeholder' => 'menu.cell_phone_of_a_1st_parent'],
				'required' => true
			])
			->add('phoneMobile3', TextType::class, [
				'attr' => ['class' => 'form-control',
                    'data-error' => 'error.invalid_phone',
                    'placeholder' => 'menu.cell_phone_of_a_2nd_parent'],
				'required' => false
			])
			->add('phoneHome', TextType::class, [
				'attr' => ['class' => 'form-control',
                    'placeholder' => 'company.other_phone_code_no'],
				'required' => false
			])
			->add('phoneOffice', TextType::class, [
				'attr' => ['class' => 'form-control', 'placeholder' => 'Téléphone bureau'],
				'required' => false
			])
			->add('email', EmailType::class, [
				'attr' => ['class' => 'form-control',
                    'data-error' => 'Email invalide',
                    'placeholder' => 'Email@domaine.extension'],
				'required' => true
			])
			->add('degree', EntityType::class, [
				'class' => Degree::class,
				'placeholder' => 'menu.select',
				'required' => true,
				'attr' => ['class' => 'form-control']
			])
			->add('school', EntityType::class, [
				'class' => School::class,
				'placeholder' => 'menu.select',
				'attr' => ['class' => 'form-control'],
				'required' => true,
				'query_builder' => function (EntityRepository $entityRepository) use ($selectedCountry) {
					return $entityRepository->createQueryBuilder('sa')
						->where('sa.country = \'' . $selectedCountry . '\'')
						->orderBy('sa.name', 'ASC');
				}
			])
			->add('registrationStudentSchool', TextType::class, [
				'attr' => ['class' => 'form-control',
                    'placeholder' => 'graduate.registration_in_establisment'],
				'required' => false
			])
			->add('otherSchool', TextType::class, ['attr' => [
				'class' => 'form-control',
				'data-error' => 'school.other_establishment' .' ?',
				'placeholder' => 'school.other_establishment'],
				'required' => false,
			])
			->add('sectorArea', EntityType::class, [
				'class' => SectorArea::class,
				'required' => true,
				'placeholder' => 'menu.select',
				'attr' => ['class' => 'form-control'],
				'query_builder' => function (EntityRepository $entityRepository) {
					return $entityRepository->createQueryBuilder('sa')
						->orderBy('sa.name', 'ASC');
				}
			])
			->add('otherActivity', TextType::class, ['attr' => [
				'class' => 'form-control',
				'data-error' => 'menu.other_trade' . ' ?',
				'placeholder' => 'menu.other_trade'],
				'required' => false,
			])
			->add('otherDegree', TextType::class, ['attr' => [
				'class' => 'form-control',
				'data-error' => 'menu.other_degree' . ' ?',
				'placeholder' => 'menu.other_degree'],
				'required' => false,
			])
			->add('contract', EntityType::class, [
				'class' => Contract::class,
				'attr' => ['class' => 'form-control']
			])
			->add('lastedCompany', EntityType::class, [
				'class' => Company::class,
				'attr' => ['class' => 'form-control']
			])
			->add('company', EntityType::class, [
				'class' => Company::class,
				'attr' => ['class' => 'form-control']
			])
			->add('latitude', TextType::class, [
				'attr' => ['class' => 'form-control',
                    'data-error' => 'graduate.complete' . ' la latitude',
                    'placeholder' => 'menu.latitude'],
				'required' => false
			])
			->add('longitude', TextType::class, [
				'attr' => ['class' => 'form-control',
                    'data-error' => 'menu.complete' . ' la longitude',
                    'placeholder' => 'menu.longitude'],
				'required' => false
			])
			->add('locationMode', CheckboxType::class, [
				'attr' => ['class' => 'form-control', 'label' => 'Location Mode ?'],
				'required' => false
			])
			->add('mapsAddress', TextType::class, ['attr' => ['hidden' => 'hidden'], 'required' => false])
			->add('image')
			->add('createdDate')
			->add('country', EntityType::class, [
				'class' => Country::class,
				'choice_label' => 'name',
				'mapped' => true,
				'required' => false,
				'attr' => ['class' => 'form-control'],
				'query_builder' => function (EntityRepository $entityRepository) {
					return $entityRepository->createQueryBuilder('sa')
						->where('sa.valid = true')
						->orderBy('sa.name', 'ASC');
				}
			])
		;

		$this->cityService->addCity($builder, 'addressCity', true);
		$this->activityService->addActivity($builder, 'activity', 'sectorArea', false);
	}

	/**
	 * @param $min
	 * @param string $max
	 * @return array
	 */
	private function getYears($min, string $max = 'current') {
		$years = range(($max === 'current' ? date('Y') : $max), $min);

		return array_combine($years, $years);
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array('data_class' => PersonDegree::class))
			->setRequired(['selectedCountry']);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBlockPrefix(): string {
		return 'appbundle_persondegree';
	}
}
