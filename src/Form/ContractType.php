<?php

namespace App\Form;

use App\Entity\Contract;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContractType extends AbstractType {
	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('name', TextType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Veuillez renseigner le libellé', 'placeholder' => 'Libellé'],
				'required' => true
			])
			->add('description', TextareaType::class, [
				'attr' => ['class' => 'form-control', 'data-error' => 'Minimum 20 caractères', 'placeholder' => 'Description'],
				'required' => false
			]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => Contract::class
		));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBlockPrefix(): string {
		return 'appbundle_contract';
	}
}
