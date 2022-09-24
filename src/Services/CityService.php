<?php

namespace App\Services;

use App\Entity\City;
use App\Entity\Region;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class CityService {
	/**
	 * Permet d'ajouter une ville en choisissant d'abord le pays, puis la region et enfin la ville
	 */
	public function addCity(
		FormBuilderInterface $builder,
		string               $cityName = 'city',
		bool                 $regionMapped = false,
		string               $regionName = 'region',
		string               $countryName = 'country'): void {
		$builder->get($countryName)->addEventListener(
			FormEvents::POST_SUBMIT,
			function (FormEvent $event) use ($cityName, $regionName, $regionMapped) {
				$form = $event->getForm();
				$this->addRegionField($form->getParent(), $form->getData(), $cityName, $regionName, $regionMapped);
			}
		);

		$builder->addEventListener(
			FormEvents::POST_SET_DATA,
			function (FormEvent $event) use ($cityName, $regionName, $countryName, $regionMapped) {
				$data = $event->getData();
				$methodName = sprintf('get%s', ucfirst($cityName)); // nom du getter renvoyant la ville en question
				/* @var City $city */
				$city = $data->$methodName();
				$form = $event->getForm();

				if ($city) {
					$region = $city->getRegion();
					$country = $region->getCountry();
					$this->addRegionField($form, $country, $cityName, $regionName, $regionMapped);
					$this->addCityField($form, $region, $cityName);
					$form->get($countryName)->setData($country);
					$form->get($regionName)->setData($region);
				} else {
					$this->addRegionField($form, null, $cityName, $regionName, $regionMapped);
					$this->addCityField($form, null, $cityName);
				}
			}
		);
	}

	/**
	 * Rajoute un champ region au formulaire
	 */
	private function addRegionField(
		FormInterface $form,
		              $country = null,
		string        $cityName,
		string        $regionName,
		bool          $regionMapped): void {
		$builder = $form->getConfig()->getFormFactory()->createNamedBuilder(
			$regionName,
			EntityType::class,
			null,
			[
				'class' => Region::class,
				'choice_label' => 'name',
				'mapped' => $regionMapped,
				'placeholder' => $country ? 'Selectionnez la region' : 'Selectionnez le pays',
				'auto_initialize' => false,
				'choices' => $country ? $country->getRegions() : [],
				'attr' => ['class' => 'form-control']
			]
		);
		$builder->addEventListener(
			FormEvents::POST_SUBMIT,
			function (FormEvent $event) use ($cityName) {
				$form = $event->getForm();
				$this->addCityField($form->getParent(), $form->getData(), $cityName);
			}
		);
		$form->add($builder->getForm());
	}

	/**
	 * Rajoute un champ city au formulaire
	 */
	private function addCityField(FormInterface $form, $region = null, string $cityName): void {
		$form->add($cityName, EntityType::class, [
			'class' => City::class,
			'choice_label' => 'name',
			'placeholder' => $region ? 'Selectionnez la ville' : 'Selectionnez la region',
			'choices' => $region ? $region->getCities() : [],
			'attr' => ['class' => 'form-control']
		]);
	}
}
