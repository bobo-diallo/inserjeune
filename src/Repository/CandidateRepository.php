<?php

namespace App\Repository;

use App\Entity\Candidate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * CandidateRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CandidateRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Candidate::class);
	}
}