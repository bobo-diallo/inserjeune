<?php

namespace App\Repository;

use App\Entity\Activity;
use App\Entity\SectorArea;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\PersistentCollection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * ActivityRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ActivityRepository extends ServiceEntityRepository implements ChildColumTemplateRepository
{
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Activity::class);
	}

   /**
    * @param PersistentCollection $activities
    * @return array
    */
   public function getActivitiesWithout($activities)
   {
      if ($activities != null) {
         $arrayActivities = $activities->toArray();
         $ids = [];
         $sectorArea = null;

		 /** @var Activity $activity */
         foreach ($arrayActivities as $activity) {
            $ids[] = $activity->getId();
            $sectorArea = $activity->getSectorArea();
         }

         $qb = $this->createQueryBuilder('a');
         return $qb
            ->select('a')
            ->where($qb->expr()->notIn('a.id', $ids))
            ->andWhere('a.sectorArea = :sectorArea')
            ->setParameter('sectorArea', $sectorArea)
            ->getQuery()
            ->getResult();
      }

      return [];
   }

   /**
    * @param SectorArea $sectorArea
    * @return array
    */
   public function findBySectorArea(SectorArea $sectorArea)
   {
      return $this->createQueryBuilder('s')
         ->where('s.sectorArea = :sector_area')
         ->setParameter('sector_area', $sectorArea)
         ->getQuery()
         ->getResult();
   }

	/**
	 * @return string[]
	 */
	public function getNames(): array {
		return $this->createQueryBuilder('activity')
			->select('activity.name')
			->getQuery()
			->getSingleColumnResult();
	}

	/**
	 * @param int $sectorId
	 * @return array
	 */
	function getActivitiesOfSector(int $sectorId): array {
		return $this->createQueryBuilder('ac')
			->select('ac.id, ac.name')
			->where('ac.sectorArea = :sectorId')
			->setParameter('sectorId', $sectorId)
			->getQuery()
			->getResult();
	}

	/**
	 * @param int $id
	 * @return string[]
	 */
	public function getNameByParentId(int $id): array {
		return $this->createQueryBuilder('activity')
			->select('activity.name')
			->where('activity.sectorArea = :sectorId')
			->setParameter('sectorId', $id)
			->getQuery()
			->getSingleColumnResult();
	}
}
