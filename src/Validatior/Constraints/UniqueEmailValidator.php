<?php

namespace App\Validatior\Constraints;

use App\Entity\User;
use App\Validatior\UnexpectedTypeException;
use App\Validatior\UnexpectedValueException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueEmailValidator extends ConstraintValidator {
	private EntityManagerInterface $em;

	public function __construct(EntityManagerInterface $em)
	{
		$this->em = $em;
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

		$qb->select('u.email')
			->from(User::class, 'u');

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
