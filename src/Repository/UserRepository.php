<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, User::class);
	}

	public function remove(User $entity, bool $flush = false): void {
		$this->getEntityManager()->remove($entity);

		if ($flush) {
			$this->getEntityManager()->flush();
		}
	}

	/**
	 * Used to upgrade (rehash) the user's password automatically over time.
	 */
	public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void {
		if (!$user instanceof User) {
			throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
		}

		$user->setPassword($newHashedPassword);

		$this->add($user, true);
	}

	public function add(User $entity, bool $flush = false): void {
		$this->getEntityManager()->persist($entity);

		if ($flush) {
			$this->getEntityManager()->flush();
		}
	}

	/**
	 * @param $id
	 * @return User[]
	 */
	public function getFromPersonDegree($id): array {
		return $this->createQueryBuilder('u')
			->addSelect('p')
			->leftJoin('u.personDegree', 'p')
			->where('p.id = :id ')
			->setParameter('id', $id)
			->setMaxResults(1)
			->getQuery()
			->getResult();
	}

	/**
	 * @param $id
	 * @return User[]
	 */
	public function getFromCompany($id): array {
		return $this->createQueryBuilder('u')
			->addSelect('c')
			->leftJoin('u.company', 'c')
			->where('c.id = :id ')
			->setParameter('id', $id)
			->setMaxResults(1)
			->getQuery()
			->getResult();
	}

	/**
	 * @param $id
	 * @return User[]
	 */
	public function getFromSchool($id): array {
		return $this->createQueryBuilder('u')
			->addSelect('s')
			->leftJoin('u.company', 's')
			->where('s.id = :id ')
			->setParameter('id', $id)
			->setMaxResults(1)
			->getQuery()
			->getResult();
	}
}
