<?php

namespace App\Form;

use App\Entity\Country;
use App\Entity\Region;
use App\Entity\Prefecture;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrefectureType extends AbstractType {
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('name', TextType::class, [
                'attr' => ['class' => 'form-control',
                    'data-error' => 'Minimum 3 caractères',
                    'placeholder' => 'menu.name'],
                'required' => true
            ])
            ->add('country', EntityType::class, [
                'class' => Country::class,
                'choice_label' => 'name',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ]);

        $builder->get('country')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                $this->addRegionField($form->getParent(), $form->getData());
            }
        );

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event) {
                $data = $event->getData();
                /* @var Region $region */
                $region = $data->getRegion();
                $form = $event->getForm();

                if ($region) {
                    $country  = $region->getCountry();
                    $this->addRegionField($form, $country);
                    $form->get('country')->setData($country);
                } else {
                    $this->addRegionField($form, null);
                }
            }
        );
    }

    /**
     * Rajoute un champ country au formulaire
     *
     * @param FormInterface $form
     * @param Country|null $country
     */
    private function addRegionField (FormInterface $form, Country $country = null)
    {
        $builder = $form->getConfig()->getFormFactory()->createNamedBuilder(
            'region',
            EntityType::class,
            null,
            [
                'class' => Region::class,
                'choice_label' => 'name',
                'placeholder' => $country ? 'menu.select_region' : 'menu.select_country',
                'auto_initialize' => false,
                'choices' => $country ? $country->getRegions() : [],
                'attr' => ['class' => 'form-control']
            ]

        );
        $form->add($builder->getForm());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => Prefecture::class
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string {
        return 'appbundle_prefecture';
    }

}
