<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Role;

class RoleType extends AbstractType {
	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
            ->add('role', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nom du rôle',
                    'data-error' => 'Minimum 7 caractères'
                ]
            ])
            ->add('pseudo', TextType::class, [
                'invalid_message' => 'Erreur de Pseudo',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Speudo',
                    // 'data-error' => 'Minimum 7 caractères'
                ]
            ])
        ;
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults([
			'data_class' => Role::class
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBlockPrefix(): string {
		return 'userbundle_role';
	}

}
