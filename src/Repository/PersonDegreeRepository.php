<?php

namespace App\Repository;

use App\Entity\Degree;
use App\Entity\PersonDegree;
use App\Entity\Prefecture;
use App\Entity\School;
use App\Model\PersonDegreeReceiverNotification;
use App\Model\PersonDegreeReadOnly;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Entity\Activity;
use App\Entity\City;
use App\Entity\Country;
use App\Entity\Region;
use App\Entity\SectorArea;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * PersonDegreeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PersonDegreeRepository extends ServiceEntityRepository {
	private ParameterBagInterface $_parameter;

	public function __construct(
		ManagerRegistry $registry,
		ParameterBagInterface $parameter
	) {
		parent::__construct($registry, PersonDegree::class);
		$this->_parameter = $parameter;
	}

	/**
	 * @param int[] $cities
	 * @param int[] $regions
	 * @param int[] $schools
	 * @return PersonDegreeReadOnly[]
	 */
    public function getAllCityRegionPersonDegree(
		array $cities = [],
		array $regions = [],
		array $schools = []
    ): array {
        $qb = $this->createQueryBuilder('p')
            ->select('
			p.id,
			p.firstname,
			p.lastname,
			p.email, 
			p.createdDate,
			p.checkSchool,
			p.lastDegreeYear,
			p.lastDegreeMonth,
			p.type,
			p.otherSchool,
			p.phoneMobile1,
			p.registrationStudentSchool,
			p.birthDate,
			activity.id as activity_id,
			activity.name as activity_name,
			degree.id as degree_id,
			degree.name as degree_name,
			city.id as city_id,
			city.name as city_name,
			region.id as region_id,
			region.name as region_name,
			school.id AS school_id,
			school.name as school_name,
			prefecture.id as prefecture_id,
			prefecture.name as prefecture_name,
			school_city.name as school_city_name,
			COUNT(satisfaction_creators.id) as satisfaction_creators_count,
			COUNT(satisfaction_salaries.id) as satisfaction_salaries_count,
			COUNT(satisfaction_searches.id) as satisfaction_searches_count
			')
            ->leftJoin('p.region', 'region')
            ->leftJoin('p.addressCity', 'city')
            ->leftJoin('p.degree', 'degree')
            ->leftJoin('p.activity', 'activity')
            ->leftJoin('p.school', 'school')
            ->leftJoin('school.city', 'school_city')
            ->leftJoin('city.prefecture', 'prefecture')
            ->leftJoin('p.satisfactionCreators', 'satisfaction_creators')
            ->leftJoin('p.satisfactionSalaries', 'satisfaction_salaries')
            ->leftJoin('p.satisfactionSearches', 'satisfaction_searches')
        ;

		$expr = $qb->expr();
        if (count($regions)) {
            $qb = $qb->andWhere($expr->in('region.id', $regions));
        }

        if (count($cities)) {
			$qb = $qb->andWhere($expr->in('city.id', $cities));
        }

        if (count($schools)) {
			$qb = $qb->andWhere($expr->in('school.id', $schools));
        }

        $persons = $qb
            ->groupBy('
			p.id,
			p.firstname,
			p.lastname,
			p.email, 
			p.createdDate,
			p.checkSchool,
			p.lastDegreeYear,
			p.lastDegreeMonth,
			p.type,
			p.otherSchool,
			p.phoneMobile1,
			p.registrationStudentSchool,
			p.birthDate,
			activity.id,
			activity.name,
			degree.id,
			degree.name,
			city.id,
			city.name,
			region.id,
			region.name,
			school.id,
			school.name,
			prefecture.id,
			prefecture.name,
			school_city.name
			')
            ->getQuery()
            ->getArrayResult();

        return array_map(function ($person) {
            return new PersonDegreeReadOnly(
                $person['id'],
                $person['firstname'],
                $person['lastname'],
                $person['email'],
                $person['createdDate'],
                $person['checkSchool'],
                $person['lastDegreeYear'],
                $person['lastDegreeMonth'],
                $person['type'],
                $person['otherSchool'],
                $person['phoneMobile1'],
                $person['registrationStudentSchool'],
                $person['birthDate'],
                $person['activity_id'],
                $person['activity_name'],
                $person['degree_id'],
                $person['degree_name'],
                $person['city_id'],
                $person['city_name'],
                $person['region_id'],
                $person['region_name'],
                $person['school_id'],
                $person['school_name'],
                $person['prefecture_id'],
                $person['prefecture_name'],
                $person['school_city_name'],
                $person['satisfaction_searches_count'],
                $person['satisfaction_salaries_count'],
                $person['satisfaction_creators_count'],
            );
        }, $persons);

		// return $this->_paginator->paginate($data, $page, $this->_parameter->get('default_pagination_limit'));
    }

	/**
	 * @param int|null $addressCity
	 * @param int|null $countryId
	 * @param int|null $schoolId
	 * @return PersonDegreeReadOnly[]
	 */
	public function getAllPersonDegree(
		?int $addressCity = null,
		?int $countryId = null,
		?int $schoolId = null
	): array {
		$qb = $this->createQueryBuilder('p')
			->select('
			p.id,
			p.firstname,
			p.lastname,
			p.email, 
			p.createdDate,
			p.checkSchool,
			p.lastDegreeYear,
			p.lastDegreeMonth,
			p.type,
			p.otherSchool,
			p.phoneMobile1,
			p.registrationStudentSchool,
			p.birthDate,
			activity.id as activity_id,
			activity.name as activity_name,
			degree.id as degree_id,
			degree.name as degree_name,
			city.id as city_id,
			city.name as city_name,
			country.id as country_id,
			country.name as country_name,
			school.id AS school_id,
			school.name as school_name,
			prefecture.id as prefecture_id,
			prefecture.name as prefecture_name,
			school_city.name as school_city_name,
			COUNT(satisfaction_creators.id) as satisfaction_creators_count,
			COUNT(satisfaction_salaries.id) as satisfaction_salaries_count,
			COUNT(satisfaction_searches.id) as satisfaction_searches_count
			')
			->leftJoin('p.country', 'country')
			->leftJoin('p.addressCity', 'city')
			->leftJoin('p.degree', 'degree')
			->leftJoin('p.activity', 'activity')
			->leftJoin('p.school', 'school')
			->leftJoin('school.city', 'school_city')
            ->leftJoin('city.prefecture', 'prefecture')
			->leftJoin('p.satisfactionCreators', 'satisfaction_creators')
			->leftJoin('p.satisfactionSalaries', 'satisfaction_salaries')
			->leftJoin('p.satisfactionSearches', 'satisfaction_searches')
			;

		if ($countryId) {
			$qb = $qb->where('country.id = :country')
				->setParameter('country', $countryId);
		}

		if ($schoolId){
			$qb = $qb->where('school.id = :school')
				->setParameter('school', $schoolId);
		}

		$persons = $qb
			->groupBy('
			p.id,
			p.firstname,
			p.lastname,
			p.email, 
			p.createdDate,
			p.checkSchool,
			p.lastDegreeYear,
			p.lastDegreeMonth,
			p.type,
			p.otherSchool,
			p.phoneMobile1,
			p.registrationStudentSchool,
			p.birthDate,
			activity.id,
			activity.name,
			degree.id,
			degree.name,
			city.id,
			city.name,
			country.id,
			country.name,
			school.id,
			school.name,
			prefecture.id,
			prefecture.name,
			school_city.name
			')
			->getQuery()
			->getArrayResult();

		return array_map(function ($person) {
			return new PersonDegreeReadOnly(
				$person['id'],
				$person['firstname'],
				$person['lastname'],
				$person['email'],
				$person['createdDate'],
				$person['checkSchool'],
				$person['lastDegreeYear'],
				$person['lastDegreeMonth'],
				$person['type'],
				$person['otherSchool'],
				$person['phoneMobile1'],
				$person['registrationStudentSchool'],
				$person['birthDate'],
				$person['activity_id'],
				$person['activity_name'],
				$person['degree_id'],
				$person['degree_name'],
                $person['city_id'],
                $person['city_name'],
				$person['country_id'],
				$person['country_name'],
				$person['school_id'],
				$person['school_name'],
                $person['prefecture_id'],
                $person['prefecture_name'],
				$person['school_city_name'],
				$person['satisfaction_searches_count'],
				$person['satisfaction_salaries_count'],
				$person['satisfaction_creators_count'],
			);
		}, $persons);

		// return $this->_paginator->paginate($data, $page, $this->_parameter->get('default_pagination_limit'));
	}

	/**
	 * @param Country $country
	 * @return PersonDegree[]
	 */
	public function getNameByCountry(Country $country): array {
		return $this->createQueryBuilder('s')
			->where('s.country = :country')
			->setParameter('country', $country)
			->getQuery()
			->getResult();
	}

	/**
	 * @param Region $region
	 * @return PersonDegree[]
	 */
	public function getNameByRegion(Region $region): array {
		return $this->createQueryBuilder('s')
			->where('s.region = :region')
			->setParameter('region', $region)
			->getQuery()
			->getResult();
	}

	/**
	 * @param City $city
	 * @return PersonDegree[]
	 */
	public function getNameByCity(City $city): array {
		return $this->createQueryBuilder('s')
			->where('s.addressCity = :city')
			->setParameter('city', $city)
			->getQuery()
			->getResult();
	}

	/**
	 * @param SectorArea $sectorArea
	 * @return PersonDegree[]
	 */
	public function getBySectorArea(SectorArea $sectorArea): array {
		return $this->createQueryBuilder('s')
			->where('s.sectorArea = :sector_area')
			->setParameter('sector_area', $sectorArea)
			->getQuery()
			->getResult();
	}

	/**
	 * @param Country $country
	 * @param SectorArea $sectorArea
	 * @return PersonDegree[]
	 */
	public function getByCountryAndSectorArea(Country $country, SectorArea $sectorArea): array {
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
	 * @return PersonDegree[]
	 */
	public function getByRegionAndSectorArea(Region $region, SectorArea $sectorArea): array {
		return $this->createQueryBuilder('pd')
			->where('pd.region = :region ')
			->andWhere('pd.sectorArea = :sectorArea')
			->setParameters(['region' => $region, 'sectorArea' => $sectorArea])
			->getQuery()
			->getResult();
	}

	/**
	 * @param Activity $activity
	 * @return PersonDegree[]
	 */
	public function getByActivity(Activity $activity): array {
		return $this->createQueryBuilder('s')
			->where('s.activity = :activity')
			->setParameter('activity', $activity)
			->getQuery()
			->getResult();
	}

	/**
	 * @param Activity $activity
	 * @return PersonDegree[]
	 */
	public function getByOtherActivity(Activity $activity): array {
		return $this->createQueryBuilder('s')
			->where('s.activity = :activity')
			->setParameter('activity', $activity)
			->getQuery()
			->getResult();
	}

	/**
	 * @param string $type
	 * @return PersonDegree[]
	 */
	public function getByType(string $type): array {
		return $this->createQueryBuilder('s')
			->where('s.type = :type')
			->setParameter('type', $type)
			->getQuery()
			->getResult();
	}

	/**
	 * @param string $contract
	 * @return PersonDegree[]
	 */
	public function getByContract(string $contract): array {
		return $this->createQueryBuilder('s')
			->where('s.contract = :contract')
			->setParameter('contract', $contract)
			->getQuery()
			->getResult();
	}

	/**
	 * @param Country $country
	 * @param string $type
	 * @return PersonDegree[]
	 */
	public function getByCountryAndType(Country $country, string $type): array {
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
	 * @return PersonDegree[]
	 */
	public function getByRegionAndType(Region $region, string $type): array {
		return $this->createQueryBuilder('s')
			->where('s.region = :region ')
			->andWhere('s.type = :type')
			->setParameters(['region' => $region, 'type' => $type])
			->getQuery()
			->getResult();
	}

	/**
	 * @return PersonDegree[]
	 */
	public function getByFirstNameAndLastNameAndBirthDate(
		string    $firstName,
		string    $lastName,
		?\DateTime $birthDate
	): array {
		return $this->createQueryBuilder('s')
			->where('s.firstname = :firstname ')
			->andWhere('s.lastname = :lastname')
			->andWhere('s.birthDate = :birthDate')
			->setParameters(['firstname' => $firstName, 'lastname' => $lastName, 'birthDate' => $birthDate])
			->getQuery()
			->getResult();
	}

	/**
	 * @return PersonDegree[]
	 */
	public function getByFirstNameAndLastNameAndBirthDateAndCreatedDate(
		string    $firstName,
		string    $lastName,
		?\DateTime $birthDate,
		?\DateTime $createdDate
	): array {
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
	 * @return PersonDegree[]
	 */
	public function getByFirstnameAndLastameAndCreatedDate(
		string    $firstname,
		string    $lastname,
		?\DateTime $createdDate
	): array {
		return $this->createQueryBuilder('s')
			->where('s.firstname = :firstname')
			->andWhere('s.lastname = :lastname')
			->andWhere('s.createdDate = :createdDate')
			->setParameters(['firstname' => $firstname, 'lastname' => $lastname, 'createdDate' => $createdDate])
			->getQuery()
			->getResult();
	}

	/**
	 * @return PersonDegree[]
	 */
	public function getByCountryAndSchool(Country $country, School $school): array {
		return $this->createQueryBuilder('pd')
			->where('pd.country = :country ')
			->andWhere('pd.school = :school')
			->setParameters(['country' => $country, 'school' => $school])
			->getQuery()
			->getResult();
	}

	public function getByCountryBetweenCreatedDateAndEndDate (
		Country $country,
		?\DateTime $beginDate,
		?\DateTime $endDate): array {
		return $this->createQueryBuilder('s')
			->where('s.country = :country ')
			->andWhere ('s.createdDate BETWEEN :beginDate AND :endDate')
			->setParameters(['country' => $country, 'beginDate'=> $beginDate, 'endDate' => $endDate])
			->getQuery()
			->getResult();
	}

    public function getByPrefectureAndSchoolBetweenCreatedDateAndEndDate (
        Prefecture $prefecture,
        ?School $school,
        ?\DateTime $beginDate,
        ?\DateTime $endDate): array {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.addressCity', 'ct')
            ->where('ct.prefecture = :prefecture ')
            ->andWhere ('s.school = :school ')
            ->andWhere ('s.createdDate BETWEEN :beginDate AND :endDate')
            ->setParameters([
                'prefecture' => $prefecture,
                'school' => $school,
                'beginDate'=> $beginDate,
                'endDate' => $endDate
            ])
            ->getQuery()
            ->getResult();
    }

	public function getByCountryAndSchoolBetweenCreatedDateAndEndDate (
		Country $country,
		?School $school,
		?\DateTime $beginDate,
		?\DateTime $endDate): array {
		return $this->createQueryBuilder('s')
			->where('s.country = :country ')
			->andWhere ('s.school = :school ')
			->andWhere ('s.createdDate BETWEEN :beginDate AND :endDate')
			->setParameters([
				'country' => $country,
				'school' => $school,
				'beginDate'=> $beginDate,
				'endDate' => $endDate
			])
			->getQuery()
			->getResult();
	}

	public function getByRegionBetweenCreatedDateAndEndDate(
		Region $region,
		?\DateTime $beginDate,
		?\DateTime $endDate): array {
		return $this->createQueryBuilder('s')
			->where('s.region = :region ')
			->andWhere ('s.createdDate BETWEEN :beginDate AND :endDate')
			->setParameters(['region' => $region, 'beginDate'=> $beginDate, 'endDate'=>$endDate])
			->getQuery()
			->getResult();
	}

    public function getByPrefectureBetweenCreatedDateAndEndDate(
        Prefecture $prefecture,
        ?\DateTime $beginDate,
        ?\DateTime $endDate): array {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.addressCity', 'ct')
            ->where('ct.prefecture = :prefecture ')
            ->andWhere ('s.createdDate BETWEEN :beginDate AND :endDate')
            ->setParameters(['prefecture' => $prefecture, 'beginDate'=> $beginDate, 'endDate'=>$endDate])
            ->getQuery()
            ->getResult();
    }

	public function getByRegionAndSchoolBetweenCreatedDateAndEndDate(
		Region $region,
		?School $school,
		?\DateTime $beginDate,
		?\DateTime $endDate): array {
		return $this->createQueryBuilder('s')
			->where('s.region = :region ')
			->andWhere ('s.school = :school ')
			->andWhere ('s.createdDate BETWEEN :beginDate AND :endDate')
			->setParameters(['region' => $region, 'school' => $school, 'beginDate'=> $beginDate, 'endDate'=>$endDate])
			->getQuery()
			->getResult();
	}

	public function getByCountryAndTypeBetweenCreatedDateAndEndDate(
		Country $country,
		string $type,
		?\DateTime $beginDate,
		?\DateTime $endDate): array {
		return $this->createQueryBuilder('s')
			->where('s.country = :country ')
			->andWhere('s.type = :type')
			->andWhere('s.createdDate BETWEEN :beginDate AND :endDate')
			->setParameters(['country' => $country, 'type' => $type, 'beginDate'=> $beginDate, 'endDate'=>$endDate])
			->getQuery()
			->getResult();
	}

	/**
	 * @return PersonDegree[]
	 */
	public function getByCountryAndTypeAndSchoolBetweenCreatedDateAndEndDate(
		Country   $country,
		string    $type,
		School    $school,
		?\DateTime $beginDate,
		?\DateTime $endDate): array {
		return $this->createQueryBuilder('s')
			->where('s.country = :country ')
			->andWhere('s.type = :type')
			->andWhere('s.school = :school')
			->andWhere('s.createdDate BETWEEN :beginDate AND :endDate')
			->setParameters(['country' => $country, 'type' => $type, 'school' => $school, 'beginDate'=> $beginDate, 'endDate'=>$endDate])
			->getQuery()
			->getResult();
	}

	/**
	 * @return PersonDegree[]
	 */
	public function getByRegionAndTypeBetweenCreatedDateAndEndDate(
		Region    $region,
		string    $type,
		?\DateTime $beginDate,
		?\DateTime $endDate): array {
		return $this->createQueryBuilder('s')
			->where('s.region = :region ')
			->andWhere('s.type = :type')
			->andWhere('s.createdDate BETWEEN :beginDate AND :endDate')
			->setParameters(['region' => $region, 'type' => $type, 'beginDate'=> $beginDate, 'endDate'=>$endDate])
			->getQuery()
			->getResult();
	}

    /**
     * @return PersonDegree[]
     */
    public function getByPrefectureAndTypeBetweenCreatedDateAndEndDate(
        Prefecture    $prefecture,
        string    $type,
        ?\DateTime $beginDate,
        ?\DateTime $endDate): array {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.addressCity', 'ct')
            ->where('ct.prefecture = :prefecture ')
            ->andWhere('s.type = :type')
            ->andWhere('s.createdDate BETWEEN :beginDate AND :endDate')
            ->setParameters(['prefecture' => $prefecture, 'type' => $type, 'beginDate'=> $beginDate, 'endDate'=>$endDate])
            ->getQuery()
            ->getResult();
    }

	/**
	 * @param Region $region
	 * @param School $school
	 * @return PersonDegree[]
	 */
	public function getByRegionAndSchool(Region $region, School $school): array {
		return $this->createQueryBuilder('s')
			->where('s.region = :region ')
			->andWhere('s.school = :school')
			->setParameters(['region' => $region, 'school' => $school])
			->getQuery()
			->getResult();
	}

    /**
     * @param Prefecture $prefecture
     * @param School $school
     * @return PersonDegree[]
     */
    public function getByPrefectureAndSchool(Prefecture $prefecture, School $school): array {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.addressCity', 'ct')
            ->where('ct.prefecture = :prefecture ')
            ->andWhere('s.school = :school')
            ->setParameters(['prefecture' => $prefecture, 'school' => $school])
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Prefecture $prefecture
     * @return PersonDegree[]
     */
    public function getByPrefecture(Prefecture $prefecture): array {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.addressCity', 'ct')
            ->where('ct.prefecture = :prefecture ')
            ->setParameters(['prefecture' => $prefecture])
            ->getQuery()
            ->getResult();
    }

	/**
	 * @return PersonDegree[]
	 */
	public function getByRegionAndTypeAndSchoolBetweenCreatedDateAndEndDate(
		Region    $region,
		string    $type,
		School    $school,
		?\DateTime $beginDate,
		?\DateTime $endDate): array {
		return $this->createQueryBuilder('s')
			->where('s.region = :region ')
			->andWhere('s.type = :type')
			->andWhere('s.school = :school')
			->andWhere('s.createdDate BETWEEN :beginDate AND :endDate')
			->setParameters(['region' => $region, 'type' => $type, 'school' => $school, 'beginDate' => $beginDate, 'endDate' => $endDate])
			->getQuery()
			->getResult();
	}

    /**
     * @return PersonDegree[]
     */
    public function getByPrefectureAndTypeAndSchoolBetweenCreatedDateAndEndDate(
        Prefecture  $prefecture,
        string      $type,
        School      $school,
        ?\DateTime $beginDate,
        ?\DateTime $endDate): array {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.addressCity', 'ct')
            ->where('ct.prefecture = :prefecture ')
            ->andWhere('s.type = :type')
            ->andWhere('s.school = :school')
            ->andWhere('s.createdDate BETWEEN :beginDate AND :endDate')
            ->setParameters(['prefecture' => $prefecture, 'type' => $type, 'school' => $school, 'beginDate' => $beginDate, 'endDate' => $endDate])
            ->getQuery()
            ->getResult();
    }

	/**
	 * @return PersonDegree[]
	 */
	public function getByCountryAndSectorAreaBetweenCreatedDateAndEndDate(
		Country    $country,
		SectorArea $sectorArea,
		?\DateTime  $beginDate,
		?\DateTime  $endDate): array {
		return $this->createQueryBuilder('s')
			->where('s.country = :country ')
			->andWhere('s.sectorArea = :sectorArea')
			->andWhere('s.createdDate BETWEEN :beginDate AND :endDate')
			->setParameters(['country' => $country, 'sectorArea' => $sectorArea, 'beginDate' => $beginDate, 'endDate' => $endDate])
			->getQuery()
			->getResult();
	}

	/**
	 * @return PersonDegree[]
	 */
	public function getByCountryAndSectorAreaAndSchoolBetweenCreatedDateAndEndDate(
		Country    $country,
		SectorArea $sectorArea,
		School     $school,
		?\DateTime  $beginDate,
		?\DateTime  $endDate): array {
		return $this->createQueryBuilder('s')
			->where('s.country = :country ')
			->andWhere('s.sectorArea = :sectorArea')
			->andWhere('s.school = :school')
			->andWhere('s.createdDate BETWEEN :beginDate AND :endDate')
			->setParameters(['country' => $country, 'sectorArea' => $sectorArea, 'school' => $school, 'beginDate' => $beginDate, 'endDate' => $endDate])
			->getQuery()
			->getResult();
	}

	/**
	 * @return PersonDegree[]
	 */
	public function getByRegionAndSectorAreaBetweenCreatedDateAndEndDate(
		Region     $region,
		SectorArea $sectorArea,
		?\DateTime  $beginDate,
		?\DateTime  $endDate): array {
		return $this->createQueryBuilder('s')
			->where('s.region = :region ')
			->andWhere('s.sectorArea = :sectorArea')
			->andWhere('s.createdDate BETWEEN :beginDate AND :endDate')
			->setParameters(['region' => $region, 'sectorArea' => $sectorArea, 'beginDate' => $beginDate, 'endDate' => $endDate])
			->getQuery()
			->getResult();
	}

	/**
	 * @return PersonDegree[]
	 */
	public function getByRegionAndSectorAreaAndSchoolBetweenCreatedDateAndEndDate(
		Region     $region,
		SectorArea $sectorArea,
		School     $school,
		?\DateTime  $beginDate,
		?\DateTime  $endDate): array {
		return $this->createQueryBuilder('s')
			->where('s.region = :region ')
			->andWhere('s.sectorArea = :sectorArea')
			->andWhere('s.school = :school')
			->andWhere('s.createdDate BETWEEN :beginDate AND :endDate')
			->setParameters(['region' => $region, 'sectorArea' => $sectorArea, 'school' => $school, 'beginDate' => $beginDate, 'endDate' => $endDate])
			->getQuery()
			->getResult();
	}

	/**
	 * @return PersonDegree[]
	 */
	public function getBySectorAreaBetweenCreatedDateAndEndDate(
		SectorArea $sectorArea,
		?\DateTime  $beginDate,
		?\DateTime  $endDate): array {
		return $this->createQueryBuilder('s')
			->where('s.sectorArea = :sector_area')
			->andWhere('s.createdDate BETWEEN :beginDate AND :endDate')
			->setParameters(['sector_area' => $sectorArea, 'beginDate' => $beginDate, 'endDate' => $endDate])
			->getQuery()
			->getResult();
	}

	/**
	 * @return PersonDegree[]
	 */
	public function getBySectorAreaAndSchoolBetweenCreatedDateAndEndDate(
		SectorArea $sectorArea,
		School     $school,
		?\DateTime  $beginDate,
		?\DateTime  $endDate): array {
		return $this->createQueryBuilder('s')
			->where('s.sectorArea = :sector_area')
			->andWhere('s.createdDate BETWEEN :beginDate AND :endDate')
			->andWhere('s.school = :school')
			->setParameters(['sector_area' => $sectorArea, 'school' => $school, 'beginDate' => $beginDate, 'endDate' => $endDate])
			->getQuery()
			->getResult();
	}

	/**
	 * @return PersonDegree[]
	 */
	public function getByActivityBetweenCreatedDateAndEndDate(
		Activity  $activity,
		?\DateTime $beginDate,
		?\DateTime $endDate): array {
		return $this->createQueryBuilder('s')
			->where('s.activity = :activity')
			->andWhere('s.createdDate BETWEEN :beginDate AND :endDate')
			->setParameters(['activity' => $activity, 'beginDate' => $beginDate, 'endDate' => $endDate])
			->getQuery()
			->getResult();
	}

	/**
	 * @return PersonDegree[]
	 */
	public function getByActivityAndSchoolBetweenCreatedDateAndEndDate(
		Activity  $activity,
		School    $school,
		?\DateTime $beginDate,
		?\DateTime $endDate): array {
		return $this->createQueryBuilder('s')
			->where('s.activity = :activity')
			->andWhere('s.school = :school')
			->andWhere('s.createdDate BETWEEN :beginDate AND :endDate')
			->setParameters(['activity' => $activity, 'school' => $school, 'beginDate' => $beginDate, 'endDate' => $endDate])
			->getQuery()
			->getResult();
	}

	/**
	 * @return PersonDegree[]
	 */
	public function getByTypeBetweenCreatedDateAndEndDate(
		string    $type,
		?\DateTime $beginDate,
		?\DateTime $endDate): array {
		return $this->createQueryBuilder('s')
			->where('s.type = :type')
			->andWhere('s.createdDate BETWEEN :beginDate AND :endDate')
			->setParameters(['type' => $type, 'beginDate' => $beginDate, 'endDate' => $endDate])
			->getQuery()
			->getResult();
	}
	/**
	 * @param string $type
	 * @param School $school
	 * @return PersonDegree[]
	 */
	public function getByTypeAndSchool(string $type, School $school): array {
		return $this->createQueryBuilder('s')
			->where('s.type = :type')
			->andWhere('s.school = :school')
			->setParameters(['type'=> $type, 'school' => $school])
			->getQuery()
			->getResult();
	}

	/**
	 * @return PersonDegree[]
	 */
	public function getByTypeAndSchoolBetweenCreatedDateAndEndDate(
		string    $type,
		School    $school,
		?\DateTime $beginDate,
		?\DateTime $endDate): array {
		return $this->createQueryBuilder('s')
			->where('s.type = :type')
			->andWhere('s.school = :school')
			->andWhere('s.createdDate BETWEEN :beginDate AND :endDate')
			->setParameters(['type' => $type, 'school' => $school, 'beginDate' => $beginDate, 'endDate' => $endDate])
			->getQuery()
			->getResult();
	}

	/**
	 * @return PersonDegree[]
	 */
	public function getByCountryAndDegreeBetweenCreatedDateAndEndDate(
		Country   $country,
		Degree    $degree,
		?\DateTime $beginDate,
		?\DateTime $endDate): array {
		return $this->createQueryBuilder('s')
			->where('s.country = :country ')
			->andWhere('s.degree = :degree')
			->andWhere('s.createdDate BETWEEN :beginDate AND :endDate')
			->setParameters(['country' => $country, 'degree' => $degree, 'beginDate' => $beginDate, 'endDate' => $endDate])
			->getQuery()
			->getResult();
	}

	/**
	 * @return PersonDegree[]
	 */
	public function getByCountryAndDegreeAndSchoolBetweenCreatedDateAndEndDate(
		Country   $country,
		Degree    $degree,
		School    $school,
		?\DateTime $beginDate,
		?\DateTime $endDate): array {
		return $this->createQueryBuilder('s')
			->where('s.country = :country ')
			->andWhere('s.degree = :degree')
			->andWhere('s.school = :school')
			->andWhere('s.createdDate BETWEEN :beginDate AND :endDate')
			->setParameters(['country' => $country, 'degree' => $degree, 'school' => $school, 'beginDate' => $beginDate, 'endDate' => $endDate])
			->getQuery()
			->getResult();
	}

	/**
	 * @return PersonDegree[]
	 */
	public function getByRegionAndDegreeBetweenCreatedDateAndEndDate(
		Region    $region,
		Degree    $degre,
		?\DateTime $beginDate,
		?\DateTime $endDate): array {
		return $this->createQueryBuilder('s')
			->where('s.region = :region ')
			->andWhere('s.degree = :degree')
			->andWhere('s.createdDate BETWEEN :beginDate AND :endDate')
			->setParameters(['region' => $region, 'degree' => $degre, 'beginDate' => $beginDate, 'endDate' => $endDate])
			->getQuery()
			->getResult();
	}

	/**
	 * @return PersonDegree[]
	 */
	public function getByRegionAndDegreeAndSchoolBetweenCreatedDateAndEndDate(
		Region    $region,
		Degree    $degree,
		School    $school,
		?\DateTime $beginDate,
		?\DateTime $endDate): array {
		return $this->createQueryBuilder('s')
			->where('s.region = :region ')
			->andWhere('s.degree = :degree')
			->andWhere('s.school = :school')
			->andWhere('s.createdDate BETWEEN :beginDate AND :endDate')
			->setParameters(['region' => $region, 'degree' => $degree, 'school' => $school, 'beginDate' => $beginDate, 'endDate' => $endDate])
			->getQuery()
			->getResult();
	}

	/**
	 * @param School $school
	 * @param boolean $unlocked
	 * @return PersonDegree[]
	 */
	public function getBySchoolAndByUnlocked(School $school, bool $unlocked): array {
		return $this->createQueryBuilder('s')
			->where('s.school = :school ')
			->andWhere('s.unlocked = :unlocked')
			->setParameters(['school' => $school, 'unlocked' => $unlocked])
			->getQuery()
			->getResult();
	}

	/**
	 * @param array $personDegreeIds
	 * @return PersonDegreeReceiverNotification[]
	 */
	public function getPersonDegreeWithIds(array $personDegreeIds): array {
		return $this->createQueryBuilder('person_degree')
			->select('NEW ' . PersonDegreeReceiverNotification::class . '(
				person_degree.id, 
				person_degree.firstname, 
				person_degree.lastname, 
				school.name,
				person_degree.email, 
				user.phone, 
				person_degree.temporaryPasswd)')
			->innerJoin('person_degree.user', 'user')
			->innerJoin('person_degree.school', 'school')
			->where('person_degree.id IN (:ids)')
			->andWhere('person_degree.email IS NOT NULL')
			->setParameter('ids', $personDegreeIds)
			->getQuery()
			->getResult();
	}

    function getPersondegreesByCityForCoordinates(City $city): array {
        return $this->createQueryBuilder('pd')
            ->select('pd.id, pd.latitude, pd.longitude')
            ->where('pd.addressCity = :city')
            ->setParameters([
                'city' => $city,
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * @return PersonDegree[]
     */
    function getWithoutCoordinate(): array {
        return $this->createQueryBuilder('pd')
            ->select('pd.id')
            ->where('pd.latitude IS NULL')
            ->orWhere('pd.longitude IS NULL')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Country $country
     * @return array
     */
    function getWithoutCoordinateByCountry(Country $country): array {
        return $this->createQueryBuilder('pd')
            ->select('pd.id')
            ->where('pd.latitude IS NULL')
            ->orWhere('pd.longitude IS NULL')
            ->andWhere('pd.country = :country')
            ->setParameter('country', $country)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Region $region
     * @return array
     */
    function getWithoutCoordinateByRegion(Region $region): array {
        return $this->createQueryBuilder('pd')
            ->select('pd.id')
            ->where('pd.latitude IS NULL')
            ->orWhere('pd.longitude IS NULL')
            ->andWhere('pd.region = :region')
            ->setParameter('region', $region)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param City $city
     * @return array
     */
    function getWithoutCoordinateByCity(City $city): array {
        return $this->createQueryBuilder('pd')
            ->select('pd.id')
            ->where('pd.latitude IS NULL')
            ->orWhere('pd.longitude IS NULL')
            ->andWhere('pd.addressCity = :city')
            ->setParameter('city', $city)
            ->getQuery()
            ->getResult();
    }

    public function getSameCordinates(): array {
        $statement = $this->_em->getConnection()->prepare("
			SELECT 
			    pd.id, 
			    pd.longitude,
			    pd.latitude,
			    DATE_FORMAT(pd.created_date, '%d/%m/%Y') as created_date,
			    DATE_FORMAT(pd.updated_date, '%d/%m/%Y') as updated_date,
			    ct.name AS city,
			    c.name AS country,
			    'duplicate coo' as error,
			    'persondegree' as actor
	        FROM person_degree pd
	        LEFT JOIN city ct ON pd.id_city = ct.id
	        LEFT JOIN country c ON pd.id_country = c.id
	        WHERE (pd.longitude, pd.latitude) IN (
	            SELECT p.longitude, p.latitude
	            FROM person_degree p
	            GROUP BY p.longitude, p.latitude
	            HAVING COUNT(*) > 1
	        )
	        ORDER BY pd.longitude
		");
        $result = $statement->executeQuery();
        return $result->fetchAllAssociative();
    }

    /**
     * @param int $countryId
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function getSameCordinatesByCountry(int $countryId): array {
        $statement = $this->_em->getConnection()->prepare("
			SELECT 
			    pd.id, 
			    pd.longitude,
			    pd.latitude,
			    DATE_FORMAT(pd.created_date, '%d/%m/%Y') as created_date,
			    DATE_FORMAT(pd.updated_date, '%d/%m/%Y') as updated_date,
			    ct.name AS city,
			    c.name AS country,
			    'duplicate coo' as error,
			    'persondegree' as actor
	        FROM person_degree pd
	        LEFT JOIN city ct ON pd.id_city = ct.id
	        LEFT JOIN country c ON pd.id_country = c.id
	        WHERE c.id = $countryId
	        AND (pd.longitude, pd.latitude) IN (
	            SELECT p.longitude, p.latitude
	            FROM person_degree p
	            GROUP BY p.longitude, p.latitude
	            HAVING COUNT(*) > 1
	        )
	        ORDER BY pd.longitude
		");
        $result = $statement->executeQuery();
        return $result->fetchAllAssociative();
    }

    /**
     * @param int $regionId
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function getSameCordinatesByRegion(int $regionId): array {
        $statement = $this->_em->getConnection()->prepare("
			SELECT 
			    pd.id, 
			    pd.longitude,
			    pd.latitude,
			    DATE_FORMAT(pd.created_date, '%d/%m/%Y') as created_date,
			    DATE_FORMAT(pd.updated_date, '%d/%m/%Y') as updated_date,
			    ct.name AS city,
			    c.name AS country,
			    'duplicate coo' as error,
			    'persondegree' as actor
	        FROM person_degree pd
	        LEFT JOIN city ct ON pd.id_city = ct.id
	        LEFT JOIN region r ON ct.id_region = r.id
	        LEFT JOIN country c ON pd.id_country = c.id
	        WHERE r.id = $regionId
	        AND (pd.longitude, pd.latitude) IN (
	            SELECT p.longitude, p.latitude
	            FROM person_degree p
	            GROUP BY p.longitude, p.latitude
	            HAVING COUNT(*) > 1
	        )
	        ORDER BY pd.longitude
		");
        $result = $statement->executeQuery();
        return $result->fetchAllAssociative();
    }

    /**
     * @param int $cityId
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function getSameCordinatesByCity(int $cityId): array {
        $statement = $this->_em->getConnection()->prepare("
			SELECT 
			    pd.id, 
			    pd.longitude,
			    pd.latitude,
			    DATE_FORMAT(pd.created_date, '%d/%m/%Y') as created_date,
			    DATE_FORMAT(pd.updated_date, '%d/%m/%Y') as updated_date,
			    ct.name AS city,
			    c.name AS country,
			    'duplicate coo' as error,
			    'persondegree' as actor
	        FROM person_degree pd
	        LEFT JOIN city ct ON pd.id_city = ct.id
	        LEFT JOIN country c ON pd.id_country = c.id
	        WHERE ct.id = $cityId
	        AND (pd.longitude, pd.latitude) IN (
	            SELECT p.longitude, p.latitude
	            FROM person_degree p
	            GROUP BY p.longitude, p.latitude
	            HAVING COUNT(*) > 1
	        )
	        ORDER BY pd.longitude
		");
        $result = $statement->executeQuery();
        return $result->fetchAllAssociative();
    }
}
