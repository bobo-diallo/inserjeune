<?php

namespace App\Form;

use App\Entity\ChangePasswordDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangePasswordType extends AbstractType {

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('phone', TextType::class, [
				'attr' => [
					'class' => 'form-control',
					'data-minlength' => '6',
					'data-error' => 'Minimum 9 caractère',
					'placeholder' => 'Numéro de téléphone'
				],
				'required' => true
			])

			->add('plainPassword', RepeatedType::class, [
				'type' => PasswordType::class,
				'required' => true,
				'first_options' => ['label' => 'Mot de passe',
					'attr' => [
						'class' => 'form-control',
						'data-minlength' => '6',
						'data-error' => 'Minimum 6',
						'placeholder' => 'Mot de passe'
					]],
				'second_options' => ['label' => 'Repeter le mot de passe',
					'attr' => [
						'class' => 'form-control',
						'placeholder' => 'Mot de passe'
					]],
			]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults([
			'data_class' => ChangePasswordDTO::class
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBlockPrefix(): string {
		return 'userbundle_user';
	}

}
