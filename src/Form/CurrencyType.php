<?php

namespace App\Form;

use App\Entity\Currency;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CurrencyType extends AbstractType {
	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('name', TextType::class, [
				'attr' => ['class' => 'form-control',
                    'data-error' => 'error.fill_in_wording',
                    'placeholder' => 'menu.name'],
				'required' => true
			])
			->add('isoName', TextType::class, [
				'attr' => ['class' => 'form-control',
                    'data-error' => 'error.fill_in_wording',
                    'placeholder' => 'currency.iso_name'],
				'required' => true
			])
			->add('isoNum', TextType::class, [
				'attr' => ['class' => 'form-control',
                    'data-error' => 'error.fill_in_wording',
                    'placeholder' => 'currency.iso_number'],
				'required' => true
			])
			->add('isoSymbol', TextType::class, [
				'attr' => ['class' => 'form-control',
                    'data-error' => 'error.fill_in_wording',
                    'placeholder' => 'currency.iso_symbol'],
				'required' => true
			]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => Currency::class
		));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBlockPrefix(): string {
		return 'appbundle_currency';
	}

}
