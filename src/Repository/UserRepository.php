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


    /**
     * @param string $beginPhoneNumber
     * @return User[]
     */
    public function getByBeginPhoneNumber(string $beginPhoneNumber): array {
        return $this->createQueryBuilder('u')
            ->where('u.phone LIKE :beginPhoneNumber')
            ->setParameter('beginPhoneNumber', $beginPhoneNumber)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $role
     * @return User[]
     */
    public function getByRole(string $role): array {
        // return $this->createQueryBuilder('u')
        //     ->select('u.i')
        //     ->join('u.profils', 'ur', 'WITH', 'ur.user_id = u.id')
        //     ->join('u.role', 'r', 'WITH', 'ur.role_id = r.id')
        //     ->where('r.role = :role_name ')
        //     ->setParameter('role_name', $role)
        //
        //     ->getQuery()
        //     ->getResult();

        $statement = $this->_em->getConnection()->prepare('
			SELECT u.id, u.phone
			FROM user AS u, user_role ur, role r
			WHERE u.id = ur.user_id 
			  AND ur.role_id = r.id 
			  AND r.role = :roleName
		');
        $result = $statement->executeQuery(['roleName' => $role]);
        return $result->fetchAll();
    }
}
