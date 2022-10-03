<?php

namespace App\Form;

use App\Entity\Country;
use App\Entity\LegalStatus;
use App\Entity\SectorArea;
use App\Entity\SocialNetwork;
use App\Services\CityService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class CompanyType extends AbstractType
{
   private CityService $cityService;

   public function __construct(CityService $cityService)
   {
      $this->cityService = $cityService;
   }

   /**
    * {@inheritdoc}
    */
   public function buildForm(FormBuilderInterface $builder, array $options)
   {
      $builder
         ->add('name', TextType::class, [
            'attr' => ['class' => 'form-control', 'data-error' => 'Veuillez renseigner le nom de l\'entreprise', 'placeholder' => 'Nom'],
            'required' => true
         ])
         ->add('url', TextType::class, [
            'attr' => ['class' => 'form-control', 'data-error' => 'Veuillez renseigner le site de l\'entreprise', 'placeholder' => 'Site Internet'],
            'required' => false
         ])
         ->add('agreeRgpd', CheckboxType::class, [
            'attr' => ['class' => 'form-control', 'label' => 'agréement rgpd ?'],
            'required' => false
         ])
         ->add('addressNumber', TextType::class, [
            'attr' => ['class' => 'form-control', 'placeholder' => 'Numero adresse'],
            'required' => false
         ])
         ->add('addressRoad', TextType::class, [
            'attr' => ['class' => 'form-control', 'data-error' => 'Veuillez renseigner le numero de la rue', 'placeholder' => 'Rue'],
            'required' => false
         ])
         ->add('addressLocality', TextType::class, [
            'attr' => ['class' => 'form-control', 'data-error' => 'Minimum 4 caractères', 'data-minlength' => '4', 'placeholder' => 'Localité'],
            'required' => false
         ])
         ->add('otherCity', TextType::class, [
            'attr' => ['class' => 'form-control', 'placeholder' => 'autre ville'],
            'required' => false
         ])
         ->add('phoneStandard', TextType::class, [
            'attr' => ['class' => 'form-control', 'data-error' => 'Téléphone est invalide', 'placeholder' => 'Téléphone de connexion'],
            'required' => true
         ])
         ->add('phoneOther', TextType::class, [
            'attr' => ['class' => 'form-control', 'data-error' => 'Phone invalide', 'placeholder' => 'Autre téléphone'],
            'required' => false
         ])
         ->add('email', TextType::class, [
            'attr' => ['class' => 'form-control', 'data-error' => 'Email est invalide', 'placeholder' => 'Email'],
            'required' => true
         ])
         ->add('sectorArea', EntityType::class, [
            'class' => SectorArea::class,
            'required' => true,
            'placeholder' => 'Sélectionnez',
            'attr' => ['class' => 'form-control'],
            'query_builder' => function (EntityRepository $entityRepository) {
               return $entityRepository->createQueryBuilder('sa')
                  ->orderBy('sa.name', 'ASC');
            }
         ])
         ->add('legalStatus', EntityType::class, [
            'class' => LegalStatus::class,
            'choice_label' => 'name',
            'placeholder' => 'Sélectionnez',
            'attr' => ['class' => 'form-control']
         ])
         ->add('socialNetworks', EntityType::class, [
            'class' => SocialNetwork::class,
            'mapped' => false, 'choice_label' =>
               'name', 'attr' => ['class' => 'form-control']
         ])

	      ->add('latitude', TextType::class, [
		      'attr' => ['class' => 'form-control', 'data-error' => 'renseigner la latitude', 'placeholder' => 'latitude'],
		      'required' => false
	      ])
	      ->add('longitude', TextType::class, [
		      'attr' => ['class' => 'form-control', 'data-error' => 'renseigner la longitude', 'placeholder' => 'longitude'],
		      'required' => false
	      ])
	      ->add('locationMode', CheckboxType::class, [
		      'attr' => ['class' => 'form-control', 'label' => 'Location Mode ?'],
		      'required' => false
	      ])
         ->add('country', EntityType::class, [
            'class' => Country::class,
            'required' => false,
            'attr' => ['class' => 'form-control', 'value' => ''],
            'query_builder' => function (EntityRepository $entityRepository) {
               return $entityRepository->createQueryBuilder('sa')
                  ->where('sa.valid = true')
                  ->orderBy('sa.name', 'ASC');
            }
         ])
      ;

      $this->cityService->addCity($builder, 'city', true);
   }

   /**
    * {@inheritdoc}
    */
   public function configureOptions(OptionsResolver $resolver)
   {
      $resolver->setDefaults(array(
         'data_class' => 'App\Entity\Company'
      ));
   }

   /**
    * {@inheritdoc}
    */
   public function getBlockPrefix()
   {
      return 'appbundle_company';
   }


}
