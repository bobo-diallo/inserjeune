<?php

namespace App\Form;

use App\Entity\AvatarDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class AvatarType extends AbstractType {

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('file', FileType::class, [
				'label' => 'Ajouter un photo de profil',
				'mapped' => false,
				'required' => false,
				'constraints' => [
					new File([
						'maxSize' => '2048k',
						'mimeTypes' => ['image/jpeg', 'image/png'],
						'mimeTypesMessage' => 'Please upload a JPEG or PNG image',
						'maxSizeMessage' => 'File size must be less than 2 M0',
					])
				],
			])
		;
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => AvatarDTO::class,
		));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBlockPrefix(): string {
		return 'appbundle_avatar';
	}
}
