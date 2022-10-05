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
				'placeholder' => 'Sélectionnez',
				'required' => true
			])
			->add('sex', ChoiceType::class, [
				'choices' => [
					'un homme' => 'un homme',
					'une femme' => 'une femme',
				],
				'attr' => ['class' => 'form-control'],
				'placeholder' => 'Sélectionnez',
				'required' => true
			])
			->add('lastDegreeYear', ChoiceType::class, [
				'choices' => $this->getYears('2010'),
				'attr' => ['class' => 'form-control'],
				'placeholder' => 'Sélectionnez',
				'required' => true
			])
			->add('lastDegreeMonth', ChoiceType::class, [
				'choices' => [
					'En cours' => '0',
					'Janvier' => '1',
					'Février' => '2',
					'Mars' => '3',
					'Avril' => '4',
					'Mai' => '5',
					'Juin' => '6',
					'Juillet' => '7',
					'Août' => '8',
					'Septembre' => '9',
					'Octobre' => '10',
					'Novembre' => '11',
					'Décembre' => '12',
				],
				'attr' => ['class' => 'form-control'],
				'placeholder' => 'Sélectionnez',
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
				'attr' => ['class' => 'form-control', 'data-error' => 'Minimum 3 caractères', 'placeholder' => 'Prénom'],
				'required' => true
			])
			->add('lastname', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Minimum 3 caractères', 'placeholder' => 'Nom'],
				'required' => true
			])
			->add('birthDate', TextType::class, [
				'attr' => ['class' => 'datepicker form-control', 'placeholder' => 'Date de naissance'],
				'required' => true
			])
			->add('addressNumber', IntegerType::class, [
				'attr' => ['class' => 'form-control', 'placeholder' => 'Numéro adresse'],
				'required' => false
			])
			->add('addressLocality', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Minimum 3 caractères', 'placeholder' => 'Localité'],
				'required' => false
			])
			->add('addressRoad', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Minimum 3 caractères', 'placeholder' => 'Rue'],
				'required' => false
			])
			->add('otherCity', TextType::class, [
				'attr' => ['class' => 'form-control', 'placeholder' => 'autre ville'],
				'required' => false
			])
			->add('phoneMobile1', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Téléhone invalide', 'placeholder' => 'Téléphone de connexion'],
				'required' => true
			])
			->add('phoneHome', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Téléhone invalide', 'placeholder' => 'Autre téléphone'],
				'required' => false
			])
			->add('phoneMobile2', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Téléhone invalide', 'placeholder' => 'Téléphone parent ou proche'],
				'required' => true
			])
			->add('phoneMobile3', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Téléhone invalide', 'placeholder' => 'Téléphone parent ou proche'],
				'required' => false
			])
			->add('phoneHome', TextType::class, [
				'attr' => ['class' => 'form-control', 'placeholder' => 'Téléphone Fixe'],
				'required' => false
			])
			->add('phoneOffice', TextType::class, [
				'attr' => ['class' => 'form-control', 'placeholder' => 'Téléphone bureau'],
				'required' => false
			])
			->add('email', EmailType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Email invalide', 'placeholder' => 'Email@domaine.extension'],
				'required' => false
			])
			->add('degree', EntityType::class, [
				'class' => Degree::class,
				'placeholder' => 'Sélectionnez',
				'required' => true,
				'attr' => ['class' => 'form-control']
			])
			->add('school', EntityType::class, [
				'class' => School::class,
				'placeholder' => 'Sélectionnez',
				'attr' => ['class' => 'form-control'],
				'required' => true,
				'query_builder' => function (EntityRepository $entityRepository) use ($selectedCountry) {
					return $entityRepository->createQueryBuilder('sa')
						->where('sa.country = \'' . $selectedCountry . '\'')
						->orderBy('sa.name', 'ASC');
				}
			])
			->add('registrationStudentSchool', TextType::class, [
				'attr' => ['class' => 'form-control', 'placeholder' => 'N° Immatriculation dans l\'établissement'],
				'required' => false
			])
			->add('otherSchool', TextType::class, ['attr' => [
				'class' => 'form-control',
				'data-error' => 'Autre établissement ?',
				'placeholder' => ' établissement'],
				'required' => false,
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
			->add('otherDegree', TextType::class, ['attr' => [
				'class' => 'form-control',
				'data-error' => 'Autre diplôme ?',
				'placeholder' => ' diplôme'],
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
			->add('latitude', TextType::class, ['attr' => ['hidden' => 'hidden'], 'required' => false])
			->add('longitude', TextType::class, ['attr' => ['hidden' => 'hidden'], 'required' => false])
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
			->add('user', EntityType::class, [
				'class' => User::class,
				'choice_label' => 'email',
				'required' => false,
				'attr' => ['class' => 'form-control']
			]);

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
