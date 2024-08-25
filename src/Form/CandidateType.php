<?php

namespace App\Form;

use App\Entity\Candidate;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CandidateType extends AbstractType
{
   /**
    * {@inheritdoc}
    */
   public function buildForm(FormBuilderInterface $builder, array $options)
   {
      $builder
	      ->add('cv', FileType::class, [
		      'label' => 'Selectionner votre CV (PDF)',
		      'mapped' => false,
		      'required' => true,
		      'constraints' => [
			      new File([
				      'maxSize' => '2M',
				      'mimeTypes' => [
					      'application/pdf',
					      'application/x-pdf',
				      ],
				      'mimeTypesMessage' => 'Please upload a valid PDF document',
			      ])
		      ],
	      ])
	      ->add('coverLetter', FileType::class, [
		      'label' => 'Selectionner votre lettre de moditivation (PDF)',
		      'mapped' => false,
		      'required' => true,
		      'constraints' => [
			      new File([
				      'maxSize' => '2M',
				      'mimeTypes' => [
					      'application/pdf',
					      'application/x-pdf',
				      ],
				      'mimeTypesMessage' => 'Please upload a valid PDF document',
			      ])
		      ],
	      ])
         ->add('message', CKEditorType::class)
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
