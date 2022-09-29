<?php

namespace App\Form;

use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Form\DataTransformer\RoleTransformer;

class RoleCollectionType extends AbstractType {
	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->addModelTransformer(new CollectionToArrayTransformer(), true)
			->addModelTransformer(new RoleTransformer(), true);
	}

	public function getParent(): string {
		return TextType::class;
	}

}
