<?php

namespace App\Repository;

use App\Entity\SatisfactionSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Entity\PersonDegree;
use Doctrine\Persistence\ManagerRegistry;

/**
 * SatisfactionSearchRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SatisfactionSearchRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, SatisfactionSearch::class);
	}

   public function getSatisfactionSearchs(PersonDegree $personDegree)
   {
      return $this->createQueryBuilder('s')
         ->select('s')
         ->where('s.personDegree = :personDegree')
         ->setParameter('personDegree', $personDegree)
         ->getQuery()
         ->getResult();
   }

   /**
    * @param PersonDegree $personDegree
    * @return mixed
    * @throws \Doctrine\ORM\NonUniqueResultException
    */
   public function getLastSatisfaction(PersonDegree $personDegree)
   {
      return $this->createQueryBuilder('ss')
         ->select('ss')
         ->where('ss.personDegree = :personDegree')
         ->orderBy('ss.id', 'DESC')
         ->setParameter('personDegree', $personDegree)
         ->setMaxResults(1)
         ->getQuery()
         ->getOneOrNullResult();
   }
}