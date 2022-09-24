<?php

namespace App\Form;

use App\Entity\Country;
use App\Entity\Currency;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CountryType extends AbstractType {
	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('name', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Veuillez renseigner le libellé', 'placeholder' => 'Nom'],
				'required' => true
			])
			->add('phoneCode')
			->add('phoneDigit')
			->add('valid')
			->add('isoCode', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Minimum 3, maximun 3', 'placeholder' => 'Code ISO'],
				'required' => true
			])
			->add('currency', EntityType::class, [
				'class' => Currency::class,
				'attr' => ['class' => 'form-control']
			]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => Country::class
		));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBlockPrefix(): string {
		return 'appbundle_country';
	}

}
