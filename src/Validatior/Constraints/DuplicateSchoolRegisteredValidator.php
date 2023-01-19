<?php declare(strict_types=1);

namespace App\Validatior\Constraints;

use App\Entity\School;
use App\Validatior\UnexpectedTypeException;
use App\Validatior\UnexpectedValueException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class DuplicateSchoolRegisteredValidator extends ConstraintValidator {
	private EntityManagerInterface $em;
	private TokenStorageInterface $tokenStorage;

	public function __construct(
		EntityManagerInterface $em,
		TokenStorageInterface $tokenStorage
	)
	{
		$this->em = $em;
		$this->tokenStorage = $tokenStorage;
	}
	public function validate(mixed $value, Constraint $constraint) {
		if (!$constraint instanceof DuplicateSchoolRegistered) {
			throw new UnexpectedTypeException($constraint, DuplicateSchoolRegistered::class);
		}

		if (null === $value || '' === $value) {
			return;
		}

		if (!is_string($value)) {
			throw new UnexpectedValueException($value, 'string');
		}

		$qb = $this->em->createQueryBuilder();

		$user = $this->tokenStorage->getToken()->getUser();
		$qb->select('s.registration')
			->from(School::class, 's')
			->where('s.user != :user')
			->setParameter('user', $user);

		$arrayRegistrations = $qb->getQuery()->execute();

		foreach ($arrayRegistrations as $key => $arrayRegistration) {
			foreach ($arrayRegistration as $registration) {
				if ($value == $registration) {
					$this->context->buildViolation($constraint->message)
						->addViolation();
				}
			}
		}
	}
}
