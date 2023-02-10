<?php

namespace App\Form;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Role;
use App\Tools\Utils;
use App\Entity\Country;

class UserType extends AbstractType {

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('country', EntityType::class, [
				'class' => Country::class,
				'placeholder' => 'Sélectionnez votre pays',
				'attr' => [
					'class' => 'form-control',
					'data-error' => 'Pays non autorisé ou inconnu',
				],
				'query_builder' => function (EntityRepository $entityRepository) {
					return $entityRepository->createQueryBuilder('a')
						->where('a.valid = true')
						->orderBy('a.name', 'ASC');
				}
			])
            ->add('diaspora', CheckboxType::class, [
                'attr' => ['class' => 'form-control', 'label' => 'Diaspora ?'],
                'required' => false
            ])
            ->add('residenceCountry', EntityType::class, [
				'required' => false,
                'class' => Country::class,
                'placeholder' => 'Sélectionnez votre pays de résidence',
                'attr' => [
                    'class' => 'form-control',
                    'data-error' => 'Pays non autorisé ou inconnu',
                ],
                'query_builder' => function (EntityRepository $entityRepository) {
                    return $entityRepository->createQueryBuilder('a')
                        ->orderBy('a.name', 'ASC');
                }
            ])
			->add('phone', TextType::class, [
				'attr' => [
					'class' => 'form-control',
					'data-minlength' => '6',
					'data-error' => 'Minimum 9 caractère',
					'placeholder' => 'Numéro de téléphone'
				],
				'required' => true
			])
			->add('validCode', TextType::class, [
				'attr' => [
					'class' => 'form-control',
					'placeholder' => 'Code'
				],
				'required' => false
			])
			->add('username', TextType::class, [
				'attr' => [
					'class' => 'form-control',
					'data-minlength' => '6',
					'data-error' => 'Minimum 4 caractère',
					'placeholder' => 'Pseudo'
				],
				'required' => false
			])
			->add('email', TextType::class, [
				'attr' => [
					'class' => 'form-control',
					'data-error' => 'Email invalide',
					'placeholder' => 'Email'
				],
				'required' => false
			])
			->add('password', PasswordType::class, [
				'attr' => [
					'class' => 'form-control',
					'data-minlength' => '6',
					'data-error' => 'Minimum 6',
					'placeholder' => 'Mot de passe'
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
			])
			// Enpecher l'admin d'ajouter des users avec un role DIPLOME ou ENTREPRISE
			->add('profils', EntityType::class, [
				'class' => Role::class,
				'multiple' => true,
				'attr' => [
					'class' => 'form-control select2',
				]
			])
			->add('typePerson', ChoiceType::class, [
				'mapped' => false,
				'placeholder' => 'Sélectionnez',
				'choices' => [
					'Entreprise' => Utils::COMPANY,
					'Etablissement' => Utils::SCHOOL,
					'Diplômé' => Utils::PERSON_DEGREE,
				],
				'attr' => ['class' => 'form-control']
			]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults([
			'data_class' => User::class
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBlockPrefix(): string {
		return 'userbundle_user';
	}

}
