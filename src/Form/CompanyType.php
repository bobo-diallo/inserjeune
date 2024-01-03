<?php

namespace App\Form;

use App\Entity\Country;
use App\Entity\LegalStatus;
use App\Entity\Prefecture;
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

   public function __construct(
       CityService $cityService
   )
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
            'attr' => ['class' => 'form-control',
                'data-error' => 'error.fill_in_the_name_of_the_company',
                'placeholder' => 'menu.lastname'],
            'required' => true
         ])
         ->add('url', TextType::class, [
            'attr' => ['class' => 'form-control',
                'data-error' => 'error.please_fill_in_the_company_website',
                'placeholder' => 'menu.web_site'],
            'required' => false
         ])
         ->add('agreeRgpd', CheckboxType::class, [
            'attr' => ['class' => 'form-control', 'label' => 'agréement rgpd ?'],
            'required' => false
         ])
         ->add('addressNumber', TextType::class, [
            'attr' => ['class' => 'form-control', 'placeholder' => 'menu.address_number'],
            'required' => false
         ])
         ->add('addressRoad', TextType::class, [
            'attr' => ['class' => 'form-control',
                'data-error' => 'error.please_fill_in_the_street_name',
                'placeholder' => 'menu.street'],
            'required' => false
         ])
         ->add('addressLocality', TextType::class, [
            'attr' => ['class' => 'form-control',
                'data-error' => 'Minimum 4 caractères', 'data-minlength' => '4',
                'placeholder' => 'menu.location'],
            'required' => false
         ])
         ->add('otherCity', TextType::class, [
            'attr' => ['class' => 'form-control', 'placeholder' => 'city.other_city'],
            'required' => false
         ])
         ->add('phoneStandard', TextType::class, [
            'attr' => ['class' => 'form-control',
                'data-error' => 'error.invalid_phone_number',
                'placeholder' => 'menu.login_phone'],
            'required' => true
         ])
         ->add('phoneOther', TextType::class, [
            'attr' => ['class' => 'form-control',
                'data-error' => 'error.invalid_phone_number',
                'placeholder' => 'menu.other_phone_number'],
            'required' => false
         ])
         ->add('email', TextType::class, [
            'attr' => ['class' => 'form-control',
                'data-error' => 'error.invalid_email',
                'placeholder' => 'menu.email'],
            'required' => true
         ])
         ->add('sectorArea', EntityType::class, [
            'class' => SectorArea::class,
            'required' => true,
            'placeholder' => 'menu.select',
            'attr' => ['class' => 'form-control'],
            'query_builder' => function (EntityRepository $entityRepository) {
                return $entityRepository->createQueryBuilder('sa')
                    ->orderBy('sa.name', 'ASC');
            }
         ])
         ->add('legalStatus', EntityType::class, [
            'class' => LegalStatus::class,
            'choice_label' => 'name',
            'placeholder' => 'menu.select',
            'attr' => ['class' => 'form-control']
         ])
         ->add('socialNetworks', EntityType::class, [
            'class' => SocialNetwork::class,
            'mapped' => false, 'choice_label' =>
               'name', 'attr' => ['class' => 'form-control']
         ])

	      ->add('latitude', TextType::class, [
		      'attr' => ['class' => 'form-control',
                  'data-error' => 'menu.complete' . " " . 'menu.latitude',
                  'placeholder' => 'menu.latitude'],
		      'required' => false
	      ])
	      ->add('longitude', TextType::class, [
		      'attr' => ['class' => 'form-control',
                  'data-error' => 'menu.complete' . " " . 'menu.longitude',
                  'placeholder' => 'menu.longitude'],
		      'required' => false
	      ])
	      ->add('locationMode', CheckboxType::class, [
		      'attr' => ['class' => 'form-control', 'label' => 'Location Mode ?'],
		      'required' => false
	      ])
	      ->add('mapsAddress', TextType::class, ['attr' => ['hidden' => 'hidden'], 'required' => false])
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
          ->add('prefecture', EntityType::class, [
              'required' => false,
              'class' => Prefecture::class,
              'placeholder' => 'menu.prefecture',
              'attr' => [
                  'class' => 'form-control',
                  'data-error' => 'error.unauthorized_or_unknown_prefecture',
              ],
              'query_builder' => function (EntityRepository $entityRepository) {
                  return $entityRepository->createQueryBuilder('b')
                      ->orderBy('b.name', 'ASC');
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
