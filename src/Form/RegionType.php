<?php

namespace App\Form;

use App\Entity\Country;
use App\Entity\Currency;
use App\Entity\Region;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegionType extends AbstractType {
	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('name', TextType::class, [
				'attr' => ['class' => 'form-control',
                    'data-error' => 'Minimum 3 caractères',
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
            ])
			->add('country', EntityType::class, [
				'class' => Country::class,
				'choice_label' => 'name',
				'attr' => ['class' => 'form-control']
			]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => Region::class
		));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBlockPrefix(): string {
		return 'appbundle_region';
	}

}
