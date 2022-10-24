<?php declare(strict_types=1);

namespace App\Validatior\Constraints;

use App\Entity\School;
use App\Validatior\UnexpectedTypeException;
use App\Validatior\UnexpectedValueException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class DuplicateSchoolRegisteredValidator extends ConstraintValidator {
	private EntityManagerInterface $em;

	public function __construct(EntityManagerInterface $em)
	{
		$this->em = $em;
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

		$qb->select('s.registration')
			->from(School::class, 's');

		$arrayRegistrations = $qb->getQuery()->execute();

		foreach ($arrayRegistrations as $key => $arrayRegistration) {
			foreach ($arrayRegistration as $email) {
				if ($value == $email) {
					$this->context->buildViolation($constraint->message)
						->addViolation();
				}
			}
		}
	}
}
