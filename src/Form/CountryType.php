<?php

namespace App\Form;

use App\Entity\Country;
use App\Entity\Currency;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
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
				'attr' => ['class' => 'form-control',
                    'data-error' => 'error.fill_in_wording',
                    'placeholder' => 'menu.name'],
				'required' => true
			])
			->add('phoneCode', IntegerType::class, [
                'required' => true
            ])
			->add('phoneDigit', IntegerType::class, [
                'required' => true
            ])
			->add('valid')
			->add('isoCode', TextType::class, [
				'attr' => ['class' => 'form-control',
                    // 'data-error' => 'Minimum 3, maximun 3',
                    'placeholder' => 'country.iso_code'],
				// 'required' => true
				'required' => false
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
