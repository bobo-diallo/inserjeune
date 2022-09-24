<?php

namespace App\Form;

use App\Entity\JobNotFoundReason;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JobNotFoundReasonType extends AbstractType {
	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('name', TextType::class, ['attr' => [
			'class' => 'form-control',
			'data-error' => 'Quelle raison d\'emploi non trouvé ?',
			'placeholder' => ' Raison'],
			'required' => false
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => JobNotFoundReason::class
		));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBlockPrefix(): string {
		return 'appbundle_jobnotfoundreason';
	}

}
