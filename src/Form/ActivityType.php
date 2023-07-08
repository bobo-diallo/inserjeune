<?php

namespace App\Form;

use App\Entity\Activity;
use App\Entity\SectorArea;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivityType extends AbstractType
{
   /**
    * {@inheritdoc}
    */
   public function buildForm(FormBuilderInterface $builder, array $options)
   {
      $builder
         ->add('name', TextType::class, [
            'attr' => [
				'class' => 'form-control',
	            'data-error' => 'Veuillez renseigner le libellé',
	            'placeholder' => 'menu.label'
            ],
            'required' => true
         ])
         ->add('sectorArea', EntityType::class, [
            'class' => SectorArea::class,
            'choice_label' => 'name',
            'attr' => ['class' => 'form-control']
         ])
      ;
   }

   /**
    * {@inheritdoc}
    */
   public function configureOptions(OptionsResolver $resolver)
   {
      $resolver->setDefaults(['data_class' => Activity::class]);
   }

   /**
    * {@inheritdoc}
    */
   public function getBlockPrefix(): string {
      return 'appbundle_degree';
   }
}
