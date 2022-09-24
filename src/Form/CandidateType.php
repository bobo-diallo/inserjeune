<?php

namespace App\Form;

use App\Entity\Candidate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CandidateType extends AbstractType
{
   /**
    * {@inheritdoc}
    */
   public function buildForm(FormBuilderInterface $builder, array $options)
   {
      $builder
         ->add('coverLetter', FileType::class)
         ->add('cv', FileType::class)
         ->add('message', TextareaType::class, [
            'attr' => [
				'class' => 'form-control',
	            'data-error' => 'Minimum 50 caractères',
	            'placeholder' => 'Message',
	            'rows' => '10'
            ],
            'required' => false
         ])
         ->add('emailDestination');
   }

   /**
    * {@inheritdoc}
    */
   public function configureOptions(OptionsResolver $resolver)
   {
      $resolver->setDefaults(array(
         'data_class' => Candidate::class
      ));
   }

   /**
    * {@inheritdoc}
    */
   public function getBlockPrefix(): string {
      return 'appbundle_candidate';
   }


}
