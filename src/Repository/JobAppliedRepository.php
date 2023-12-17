<?php

namespace App\Repository;

use App\Entity\City;
use App\Entity\Country;
use App\Entity\JobOffer;
use App\Entity\JobApplied;
use App\Entity\PersonDegree;
use App\Entity\Region;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JobApplied>
 *
 * @method JobApplied|null find($id, $lockMode = null, $lockVersion = null)
 * @method JobApplied|null findOneBy(array $criteria, array $orderBy = null)
 * @method JobApplied[]    findAll()
 * @method JobApplied[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JobAppliedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobApplied::class);
    }

    public function save(JobApplied $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(JobApplied $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByDateAndOfferAndPersonDegree(JobOffer $job, User $user): ?JobApplied
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.idOffer = :idOffer')
            ->andWhere('j.idUser = :idUser')
            ->setParameters(['idOffer'=>$job, 'idUser'=>$user])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * @return JobApplied[]
     */
    public function getAll(): array  {
        $statement = $this->_em->getConnection()->prepare("
			SELECT * 
	        FROM job_applied ja
		");
        $result = $statement->executeQuery();
        return $result->fetchAllAssociative();
    }

    /**
     * @param Int $user
     * @return JobApplied[]
     */
    public function getByUserCompany(int $userId): array  {
        $statement = $this->_em->getConnection()->prepare("
			SELECT 
			    ja.id, 
			    ja.id_offer, 
			    ja.id_user, 
			    ja.applied_date,
			    ja.resumed_applied,
			    ja.is_sended
	        FROM job_applied ja
	        LEFT JOIN job_offer jo ON ja.id_offer = jo.id
	        LEFT JOIN company c ON jo.id_company = c.id
	        LEFT JOIN user u ON c.user_id = u.id
            WHERE 
                u.id = :userId
		");
        $result = $statement->executeQuery(['userId' => $userId]);
        return $result->fetchAllAssociative();
    }

    /**
     * @param Int $user
     * @return JobApplied[]
     */
    public function getByUserSchool(int $userId): array  {
        $statement = $this->_em->getConnection()->prepare("
			SELECT 
			    ja.id, 
			    ja.id_offer, 
			    ja.id_user, 
			    ja.applied_date,
			    ja.resumed_applied,
			    ja.is_sended
	        FROM job_applied ja
	        LEFT JOIN job_offer jo ON ja.id_offer = jo.id
	        LEFT JOIN school s ON jo.id_school = s.id
	        LEFT JOIN user u ON s.user_id = u.id
            WHERE 
                u.id = :userId
		");
        $result = $statement->executeQuery(['userId' => $userId]);
        return $result->fetchAllAssociative();
    }

    /**
     * @param Int $user
     * @return JobApplied[]
     */
    public function getByUserPersonDegree(int $userId): array  {
        $statement = $this->_em->getConnection()->prepare("
			SELECT 
			    ja.id, 
			    ja.id_offer, 
			    ja.id_user, 
			    ja.applied_date,
			    ja.resumed_applied,
			    ja.is_sended
	        FROM job_applied ja
	        LEFT JOIN user u ON ja.id_user = u.id
            WHERE 
                u.id = :userId
		");
        $result = $statement->executeQuery(['userId' => $userId]);
        return $result->fetchAllAssociative();
    }

    /**
     * @param \DateTime $date
     * @return JobApplied[]
     */
    function findBeforeYear(\DateTime $date): array {
        return $this->createQueryBuilder('j')
            ->where('j.appliedDate < :appliedDate')
            ->setParameter('appliedDate', $date)
            ->getQuery()
            ->getResult();
        return [];
    }

    /**
     * @param \DateTime $date
     * @return array
     */
    function findBeforeDate(\DateTime $date): array {
        return $this->createQueryBuilder('j')
            ->where('j.appliedDate < :appliedDate')
            ->setParameter('appliedDate', $date)
            ->getQuery()
            ->getResult();
        return [];
    }

    /**
     * @param \DateTime $date
     * @param Country $country
     * @return array
     */
    function findBeforeDateByCountry(\DateTime $date, Country $country): array {
        return $this->createQueryBuilder('j')
            ->where('j.appliedDate < :appliedDate')
            ->andWhere('j.country = :country')
            ->setParameters(['appliedDate'=> $date, 'country' => $country])
            ->getQuery()
            ->getResult();
        return [];
    }

    /**
     * @param \DateTime $date
     * @param Region $region
     * @return array
     */
    function findBeforeDateByRegion (\DateTime $date, Region $region): array {
        return $this->createQueryBuilder('j')
            ->where('j.appliedDate < :appliedDate')
            ->andWhere('j.region = :region')
            ->setParameters(['appliedDate'=> $date, 'region' => $region])
            ->getQuery()
            ->getResult();
        return [];
    }

    function findBeforeDateByCity (\DateTime $date, City $city): array {
        return $this->createQueryBuilder('j')
            ->where('j.appliedDate < :appliedDate')
            ->andWhere('j.city = :city')
            ->setParameters(['appliedDate'=> $date, 'city' => $city])
            ->getQuery()
            ->getResult();
        return [];
    }

//    /**
//     * @return JobApplied[] Returns an array of JobApplied objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('j')
//            ->andWhere('j.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('j.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?JobApplied
//    {
//        return $this->createQueryBuilder('j')
//            ->andWhere('j.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
