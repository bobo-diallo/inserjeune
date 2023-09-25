<?php

namespace App\Form;

use App\Entity\Country;
use App\Entity\Region;
use App\Entity\City;
use App\Entity\User;
use App\Entity\Role;
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
use App\Tools\Utils;

class UserType extends AbstractType {

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
            ->add('country', EntityType::class, [
                'class' => Country::class,
                'required' => false,
                'placeholder' => 'menu.select_country',
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
			->add('region', EntityType::class, [
				'class' => Region::class,
                'required' => false,
				'placeholder' => 'menu.select_region',
				'attr' => [
					'class' => 'form-control',
					'data-error' => 'Region non autorisé ou inconnu',
				],
				'query_builder' => function (EntityRepository $entityRepository) {
					return $entityRepository->createQueryBuilder('a')
						->where('a.valid = true')
						->orderBy('a.name', 'ASC');
				}
			])
            ->add('diaspora', CheckboxType::class, [
                'attr' => ['class' => 'form-control', 'label' => 'menu.diaspora ?'],
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
            ->add('residenceRegion', EntityType::class, [
				'required' => false,
                'class' => Region::class,
                'placeholder' => 'graduate.select_your_residence_country',
                'attr' => [
                    'class' => 'form-control',
                    'data-error' => 'error.unauthorized_or_unknown_country',
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
					'placeholder' => 'menu.phone_number'
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
					'placeholder' => 'menu.email'
				],
				'required' => false
			])
			->add('password', PasswordType::class, [
				'attr' => [
					'class' => 'form-control',
					'data-minlength' => '6',
					'data-error' => 'Minimum 6',
					'placeholder' => 'menu.password'
				],
				'required' => true
			])
			->add('plainPassword', RepeatedType::class, [
				'type' => PasswordType::class,
				'required' => true,
				'first_options' => ['label' => 'menu.password',
					'attr' => [
						'class' => 'form-control',
						'data-minlength' => '6',
						'data-error' => 'Minimum 6',
						'placeholder' => 'menu.password'
					]],
				'second_options' => ['label' => 'Repeter le mot de passe',
					'attr' => [
						'class' => 'form-control',
						'placeholder' => 'menu.confirm'
					]],
			])
			// Enpecher l'admin d'ajouter des users avec un role DIPLOME ou ENTREPRISE ou ETABLISSEMENT
			->add('profils', EntityType::class, [
				'class' => Role::class,
				'multiple' => true,
                'query_builder' => function (EntityRepository $r) {
                    return $r->createQueryBuilder('ig')
                        ->where('ig.role != :role1')
                        ->andWhere('ig.role != :role2')
                        ->andWhere('ig.role != :role3')
                        ->setParameters([':role1'=> 'ROLE_DIPLOME' , ':role2'=> 'ROLE_ENTREPRISE', ':role3'=> 'ROLE_ETABLISSEMENT',])
                        ->orderBy('ig.pseudo', 'ASC');
                },
				'attr' => [
					'class' => 'form-control select2',
				]
			])
			->add('typePerson', ChoiceType::class, [
				'mapped' => false,
				'placeholder' => 'menu.select',
				'choices' => [
					'menu.company' => Utils::COMPANY,
					'menu.establishment' => Utils::SCHOOL,
					'menu.graduate' => Utils::PERSON_DEGREE,
				],
				'attr' => ['class' => 'form-control']
			])
            ->add('adminRegions', EntityType::class, [
                'class' => Region::class,
                'multiple' => true,
                'attr' => [
                    'class' => 'form-control select2',
                ]
            ])
            ->add('adminCities', EntityType::class, [
                'class' => City::class,
                'multiple' => true,
                'attr' => [
                    'class' => 'form-control select2',
                    'id' => 'idAdminCities',
                ]
            ])
        ;
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
