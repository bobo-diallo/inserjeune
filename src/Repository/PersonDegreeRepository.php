<?php

namespace App\Repository;

use App\Entity\PersonDegree;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Entity\Activity;
use App\Entity\City;
use App\Entity\Country;
use App\Entity\Region;
use App\Entity\SectorArea;
use Doctrine\Persistence\ManagerRegistry;

/**
 * PersonDegreeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PersonDegreeRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, PersonDegree::class);
	}

   /**
    * @param Country $country
    * @return array
    */
   public function getNameByCountry(Country $country)
   {
      return $this->createQueryBuilder('s')
         ->where('s.country = :country')
         ->setParameter('country', $country)
         ->getQuery()
         ->getResult();
   }

   /**
    * @param Region $region
    * @return array
    */
   public function getNameByRegion(Region $region)
   {
      return $this->createQueryBuilder('s')
         ->where('s.region = :region')
         ->setParameter('region', $region)
         ->getQuery()
         ->getResult();
   }

   /**
    * @param City $city
    * @return array
    */
   public function getNameByCity(City $city)
   {
      return $this->createQueryBuilder('s')
         ->where('s.addressCity = :city')
         ->setParameter('city', $city)
         ->getQuery()
         ->getResult();
   }

   /**
    * @param SectorArea $sectorArea
    * @return array
    */
   public function getBySectorArea(SectorArea $sectorArea)
   {
      return $this->createQueryBuilder('s')
         ->where('s.sectorArea = :sector_area')
         ->setParameter('sector_area', $sectorArea)
         ->getQuery()
         ->getResult();
   }

   /**
    * @param Country $country
    * @param SectorArea $sectorArea
    * @return array
    */
   public function getByCountryAndSectorArea(Country $country, SectorArea $sectorArea)
   {
      return $this->createQueryBuilder('pd')
         ->where('pd.country = :country ')
         ->andWhere('pd.sectorArea = :sectorArea')
         ->setParameters(['country' => $country, 'sectorArea' => $sectorArea])
         ->getQuery()
         ->getResult();
   }

   /**
    * @param Region $region
    * @param SectorArea $sectorArea
    * @return array
    */
   public function getByRegionAndSectorArea(Region $region, SectorArea $sectorArea)
   {
      return $this->createQueryBuilder('pd')
         ->where('pd.region = :region ')
         ->andWhere('pd.sectorArea = :sectorArea')
         ->setParameters(['region' => $region, 'sectorArea' => $sectorArea])
         ->getQuery()
         ->getResult();
   }

   /**
    * @param Activity $activity
    * @return array
    */
   public function getByActivity (Activity $activity)
   {
      return $this->createQueryBuilder('s')
         ->where('s.activity = :activity')
         ->setParameter('activity', $activity)
         ->getQuery()
         ->getResult();
   }

   /**
    * @param Activity $activity
    * @return array
    */
   public function getByOtherActivity (Activity $activity)
   {
      return $this->createQueryBuilder('s')
         ->where('s.activity = :activity')
         ->setParameter('activity', $activity)
         ->getQuery()
         ->getResult();
   }

   /**
    * @param string $type
    * @return array
    */
   public function getByType (string $type)
   {
      return $this->createQueryBuilder('s')
         ->where('s.type = :type')
         ->setParameter('type', $type)
         ->getQuery()
         ->getResult();
   }

   /**
    * @param string $contract
    * @return array
    */
   public function getByContract (string $contract)
   {
      return $this->createQueryBuilder('s')
         ->where('s.contract = :contract')
         ->setParameter('contract', $contract)
         ->getQuery()
         ->getResult();
   }

   /**
    * @param Country $country
    * @param string $type
    * @return array
    */
   public function getByCountryAndType (Country $country, string $type)
   {
      return $this->createQueryBuilder('s')
         ->where('s.country = :country ')
         ->andWhere('s.type = :type')
         ->setParameters(['country' => $country, 'type' => $type])
         ->getQuery()
         ->getResult();
   }

   /**
    * @param Region $region
    * @param string $type
    * @return array
    */
   public function getByRegionAndType (Region $region, string $type)
   {
      return $this->createQueryBuilder('s')
         ->where('s.region = :region ')
         ->andWhere('s.type = :type')
         ->setParameters(['region' => $region, 'type' => $type])
         ->getQuery()
         ->getResult();
   }

    /**
     * @param string $firstName
     * @param string $lastName
     * @param \DateTime $birthDate
     * @return array
     */
    public function getByFirstNameAndLastNameAndBirthDate (
		string $firstName,
		string $lastName,
		\DateTime $birthDate)
    {
        return $this->createQueryBuilder('s')
            ->where('s.firstname = :firstname ')
            ->andWhere('s.lastname = :lastname')
            ->andWhere('s.birthDate = :birthDate')
            ->setParameters(['firstname' => $firstName, 'lastname' => $lastName, 'birthDate' => $birthDate])
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $firstName
     * @param string $lastName
     * @param \DateTime $birthDate
     * @param \DateTime $createdDate
     * @return array
     */
    public function getByFirstNameAndLastNameAndBirthDateAndCreatedDate (
		string $firstName,
		string $lastName,
		\DateTime $birthDate,
		\DateTime $createdDate
    )
    {
        return $this->createQueryBuilder('s')
            ->where('s.firstname = :firstname ')
            ->andWhere('s.lastname = :lastname')
            ->andWhere('s.birthDate = :birthDate')
            ->andWhere('s.createdDate = :createdDate')
            ->setParameters([
				'firstname' => $firstName,
				'lastname' => $lastName,
				'birthDate' => $birthDate,
				'createdDate' => $createdDate
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $firstname
     * @param string $lastname
     * @param \DateTime $createdDate
     * @return array
     */
    public function getByFirstnameAndLastameAndCreatedDate (
		string $firstname,
		string $lastname,
		\DateTime $createdDate
    )
    {
        return $this->createQueryBuilder('s')
            ->where('s.firstname = :firstname')
            ->andWhere('s.lastname = :lastname')
            ->andWhere('s.createdDate = :createdDate')
            ->setParameters([ 'firstname' => $firstname, 'lastname' => $lastname, 'createdDate' => $createdDate ])
            ->getQuery()
            ->getResult();
    }
}