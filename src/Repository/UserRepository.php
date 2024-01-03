<?php

namespace App\Repository;

use App\Entity\User;
use App\Model\UserReadOnly;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
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
	 * @throws Exception
	 */
    public function getByRole(string $role): array {
        $statement = $this->_em->getConnection()->prepare('
			SELECT u.id, u.phone
			FROM user AS u, user_role ur, role r
			WHERE u.id = ur.user_id 
			  AND ur.role_id = r.id 
			  AND r.role = :roleName
		');
        $result = $statement->executeQuery(['roleName' => $role]);
        return $result->fetchAllAssociative();
    }

	/**
	 * @return UserReadOnly[]
	 * @throws Exception
	 */
	public function getAllUser(): array {
		$users = $this->getEntityManager()
			->getConnection()
			->createQueryBuilder()
			->select('
				u.id,
				u.username,
				u.email,
				u.phone,
				country.name as countryName,
				region.name as regionName,
				GROUP_CONCAT(r.role SEPARATOR \', \') as roles,
				GROUP_CONCAT(r.pseudo SEPARATOR \', \') as pseudos,
				GROUP_CONCAT(rg.name SEPARATOR \', \') as adminRegions,
				GROUP_CONCAT(c.name SEPARATOR \', \') as adminCities
			')
			->from('user', 'u')
			->leftJoin('u', 'country', 'country', 'u.country_id = country.id')
			->leftJoin('u', 'region', 'region', 'u.region_id = region.id')
			->leftJoin('u', 'user_role', 'user_role', 'u.id = user_role.user_id')
			->leftJoin('user_role', 'role', 'r', 'r.id = user_role.role_id')
            ->leftJoin('u', 'user_admin_regions', 'user_admin_regions', 'u.id = user_admin_regions.user_id')
            ->leftJoin('user_admin_regions', 'region', 'rg', 'rg.id = user_admin_regions.region_id')
            ->leftJoin('u', 'user_admin_cities', 'user_admin_cities', 'u.id = user_admin_cities.user_id')
            ->leftJoin('user_admin_cities', 'city', 'c', 'c.id = user_admin_cities.city_id')
			->groupBy(
				'u.id,
				u.id,
				u.username,
				u.email,
				u.phone,
				country.name,
				region.name'
			)
			->executeQuery()
			->fetchAllAssociative();

		return array_map(function ($user) {
			return new UserReadOnly(
				$user['id'],
				$user['username'],
				$user['email'],
				$user['phone'],
				$user['countryName'],
				$user['regionName'],
				$user['roles'],
				$user['pseudos'],
				$user['adminRegions'],
				$user['adminCities'],
			);
		}, $users);
	}
}
