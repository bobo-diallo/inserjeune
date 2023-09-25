<?php

namespace App\Form;

use App\Entity\Activity;
use App\Entity\Degree;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DegreeType extends AbstractType {
	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('name', TextType::class, [
				'attr' => [
					'class' => 'form-control',
					'autocomplete' => 'off',
					'data-error' => 'error.fill_in_wording',
					'placeholder' => 'menu.label'],
				'required' => true
			])
			->add('description', TextareaType::class, [
				'attr' => [
					'class' => 'form-control',
					'placeholder' => 'menu.description'
				],
				'required' => false
			])
			->add('level', ChoiceType::class, [
				'choices' => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
					'7' => '7',
				],
				'attr' => ['class' => 'form-control']
			])
			->add('activity', EntityType::class, [
				'class' => Activity::class,
				'choice_label' => 'name',
				'attr' => ['class' => 'form-control'],
				'required' => false
			]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => Degree::class
		));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBlockPrefix(): string {
		return 'appbundle_degree';
	}
}
