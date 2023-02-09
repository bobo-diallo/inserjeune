<?php

namespace App\Validatior\Constraints;

use App\Entity\User;
use App\Validatior\UnexpectedTypeException;
use App\Validatior\UnexpectedValueException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueEmailValidator extends ConstraintValidator {
	private EntityManagerInterface $em;
	private TokenStorageInterface $tokenStorage;

	public function __construct(
		EntityManagerInterface $em,
		TokenStorageInterface $tokenStorage,
	)
	{
		$this->em = $em;
		$this->tokenStorage = $tokenStorage;
	}
	public function validate(mixed $value, Constraint $constraint) {
		if (!$constraint instanceof UniqueEmail) {
			throw new UnexpectedTypeException($constraint, UniqueEmail::class);
		}

		if (null === $value || '' === $value) {
			return;
		}

		if (!is_string($value)) {
			throw new UnexpectedValueException($value, 'string');
		}

		$qb = $this->em->createQueryBuilder();

		$currentId = $this->tokenStorage->getToken()->getUser()->getId();

		$qb->select('u.email')
			->from(User::class, 'u')
			->where('u.id != :id')
			->setParameter('id', $currentId);

		$results = $qb->getQuery()->execute();

		foreach ($results as $key => $arrayRegistration) {
			foreach ($arrayRegistration as $email) {
				if ($value == $email) {
					$this->context->buildViolation($constraint->message)
						->addViolation();
				}
			}
		}
	}
}
