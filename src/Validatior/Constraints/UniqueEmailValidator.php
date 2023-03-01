<?php

namespace App\Validatior\Constraints;

use App\Entity\Role;
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

		$user = $this->tokenStorage->getToken()->getUser();

		$roles = $user->getRoles();
		if (in_array(Role::ROLE_ADMIN, $roles)) {
			$countEmail = $qb->select('COUNT(u.id)')
				->from(User::class, 'u')
				->where('u.email = :email')
				->setParameter('email', $value)
				->getQuery()
				->getSingleScalarResult();

			if ($countEmail > 1) {
				$this->context->buildViolation($constraint->message)->addViolation();
			}
			return;
		}

		$qb->select('u.email')
			->from(User::class, 'u')
			->where('u.id != :id')
			->setParameter('id', $user->getId());

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
