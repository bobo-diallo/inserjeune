<?php

namespace App\Form;

use App\Entity\Degree;
use App\Entity\JobNotFoundReason;
use App\Entity\SatisfactionSearch;
use App\Entity\SectorArea;
use App\Services\ActivityService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class SatisfactionSearchType extends AbstractType
{
	private ActivityService $activityService;

	public function __construct(ActivityService $activityService)
   {
	   $this->activityService = $activityService;
   }

   /**
    * {@inheritdoc}
    */
   public function buildForm(FormBuilderInterface $builder, array $options)
   {
      $builder
         ->add('registeredTraining', CheckboxType::class, ['attr' => [
            'class' => 'form-control',
            'label' => 'Inscrit en Formation ?'],
            'required' => false
         ])
         ->add('formationPursuitLastDegree', CheckboxType::class, [
            'attr' => ['class' => 'form-control', 'label' => 'diplôme délivre cette formation ?'],
            'required' => false,
         ])
         ->add('otherFormationDegreeName', TextType::class, [
            'attr' => ['class' => 'form-control', 'data-error' => 'Veuillez renseigner le nom du diplôme', 'placeholder' => 'menu.degree'],
            'required' => false
         ])
         ->add('otherFormationActivityName', TextType::class, [
            'attr' => ['class' => 'form-control', 'data-error' => 'Veuillez renseigner le métier', 'placeholder' => 'satisfaction_search.job'],
            'required' => false
         ])
         ->add('degree', EntityType::class, [
            'class' => Degree::class,
            'attr' => ['class' => 'form-control'],
            'required' => true,
            'placeholder' => 'menu.select'
         ])
         ->add('searchWork', CheckboxType::class, [
            'attr' => ['class' => 'form-control', 'label' => 'Recherche d\'emploi ?'],
            'required' => false,
         ])
         ->add('noSearchWorkReason', ChoiceType::class, [
            'choices' => [
               // 'Projet de mariage et/ou d’avoir des enfants' => 'Projet de mariage et/ou d’avoir des enfants',
               'Projet_de_mariage' => 'Projet de mariage et/ou d\’avoir des enfants',
               'Raisons_personnelles' => 'Raisons personnelles',
            ],
            'attr' => ['class' => 'form-control'],
            'placeholder' => 'menu.select',
            'required' => true,
         ])
         ->add('activeVolunteer', CheckboxType::class, [
            'attr' => ['class' => 'form-control', 'label' => 'Bénévole actif ?'],
            'required' => false,
         ])
         ->add('sectorArea', EntityType::class, [
            'class' => SectorArea::class,
            'attr' => ['class' => 'form-control'],
            'required' => true,
            'placeholder' => 'menu.select',
            'query_builder' => function (EntityRepository $entityRepository) {
               return $entityRepository->createQueryBuilder('sa')
                  ->orderBy('sa.name', 'ASC');
            }
         ])
         ->add('otherDomainVolunteer', TextType::class, [
            'attr' => ['class' => 'form-control', 'data-error' => 'Autre activité de bénévolat ?', 'placeholder' => 'satisfaction_search.job'],
            'required' => false
         ])
         ->add('jobVolunteer', TextType::class, [
            'attr' => ['class' => 'form-control', 'data-error' => 'Quelle fonction bénévole ?', 'placeholder' => 'satisfaction_search.function'],
            'required' => false
         ])
         ->add('jobFromFormation', CheckboxType::class, ['attr' => [
            'class' => 'form-control',
            'label' => 'Emploi depuis formation ?'],
            'required' => false,
         ])
         ->add('jobRefuse', CheckboxType::class, ['attr' => [
            'class' => 'form-control',
            'label' => 'Emploi refusé ?'],
            'required' => false,
         ])
         ->add('jobTime', ChoiceType::class, [
            'choices' => [
                'satisfaction.less_than_a_month' => 'moins d\'un mois',
                'satisfaction.between_1_and_3_months' => 'entre 1 et 3 mois',
                'satisfaction.more_than_3_months' => 'plus de 3 mois',
            ],
            'attr' => ['class' => 'form-control']
         ])
         ->add('jobNotFoundReasons', EntityType::class, [
            'class' => JobNotFoundReason::class,
            'multiple' => true,
            'attr' => ['class' => 'form-control']
         ])
         ->add('jobNotFoundOther', TextType::class, ['attr' => [
            'class' => 'form-control',
            'data-error' => 'Autre raison d\'emploi non trouvé ?',
            'placeholder' => 'menu.reason'],
            'required' => false,
         ])
         ->add('confirmed', HiddenType::class, ['mapped' => false])
         ->add('sectorAreaVolunteer', EntityType::class, [
            'class' => SectorArea::class,
            'attr' => ['class' => 'form-control' ],
            'required' => false,
            'placeholder' => 'menu.select',
            'query_builder' => function (EntityRepository $entityRepository) {
               return $entityRepository->createQueryBuilder('sa')
                  ->orderBy('sa.name', 'ASC');
            }
         ]);

      $this->activityService->addActivity($builder, 'activities');
      $this->activityService->addActivity($builder, 'activityVolunteer', 'sectorAreaVolunteer', false);
   }

   /**
    * {@inheritdoc}
    */
   public function configureOptions(OptionsResolver $resolver)
   {
      $resolver->setDefaults(array(
         'data_class' => SatisfactionSearch::class
      ));
   }

   /**
    * {@inheritdoc}
    */
   public function getBlockPrefix(): string {
      return 'appbundle_satisfactionsearch';
   }


}
