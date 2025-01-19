<?php declare(strict_types=1);

namespace App\Model;

final class PersonDegreeReadOnly {
	public int $id;
	public ?string $firstname;
	public ?string $lastname;
	public ?string $sex;
	public ?string $email;
	public ?\DateTime $createdDate;
	public ?bool $checkSchool;
	public ?int $lastDegreeYear;
	public ?int $lastDegreeMonth;
	public ?string $type;
	public ?string $otherSchool;
	public ?string $phoneMobile1;
	public ?string $registrationStudentSchool;
	public ?\DateTime $birthDate;
	// public ?int $sectorAreaId;
	// public ?string $sectorAreaName;
	public ?int $activityId;
	public ?string $activityName;
	public ?int $degreeId;
	public ?string $degreeName;
    public ?int $cityId;
    public ?string $cityName;
	public ?int $countryId;
	public ?string $countryName;

    public ?int $prefectureId;
    public ?string $prefectureName;
	public ?int $schoolId;
	public ?string $schoolName;
	public ?string $schoolCityName;
	public int $satisfactionSearchesCount = 0;
	public int $satisfactionSalariesCount = 0;
	public int $satisfactionCreators_count = 0;
	public function __construct(
		int $id,
		?string $firstname,
		?string $lastname,
		?string $sex,
		?string $email,
		?\DateTime $createdDate,
		?bool $checkSchool,
		?int $lastDegreeYear,
		?int $lastDegreeMonth,
		?string $type,
		?string $otherSchool,
		?string $phoneMobile1,
		?string $registrationStudentSchool,
		?\DateTime $birthDate,
		?int $activityId,
		?string $activityName,
		// ?int $sectorAreaId,
		// ?string $sectorAreaName,
		?int $degreeId,
		?string $degreeName,
        ?int $cityId,
        ?string $cityName,
		?int $countryId,
		?string $countryName,
		?int $schoolId,
		?string $schoolName,
        ?int $prefectureId,
        ?string $prefectureName,
		?string $schoolCityName,
		?int $satisfactionSearchesCount,
		?int $satisfactionSalariesCount,
		?int $satisfactionCreators_count
	) {
		$this->id = $id;
		$this->firstname = $firstname;
		$this->lastname = $lastname;
		$this->sex = $sex;
		$this->email = $email;
		$this->createdDate = $createdDate;
		$this->checkSchool = $checkSchool;
		$this->lastDegreeYear = $lastDegreeYear;
		$this->lastDegreeMonth = $lastDegreeMonth;
		$this->type = $type;
		$this->otherSchool = $otherSchool;
		$this->phoneMobile1 = $phoneMobile1;
		$this->registrationStudentSchool = $registrationStudentSchool;
		// $this->sectorAreaId = $sectorAreaId;
		// $this->sectorAreaName = $sectorAreaName;
		$this->activityId = $activityId;
		$this->activityName = $activityName;
		$this->degreeId = $degreeId;
		$this->degreeName = $degreeName;
        $this->cityId = $cityId;
        $this->cityName = $cityName;
		$this->countryId = $countryId;
		$this->countryName = $countryName;
		$this->schoolId = $schoolId;
		$this->schoolName = $schoolName;
		$this->schoolCityName = $schoolCityName;
		$this->birthDate = $birthDate;
        $this->prefectureId = $prefectureId;
        $this->prefectureName = $prefectureName;
		$this->satisfactionSearchesCount = $satisfactionSearchesCount;
		$this->satisfactionSalariesCount = $satisfactionSalariesCount;
		$this->satisfactionCreators_count = $satisfactionCreators_count;

		$this->sex = $this->getSex();
	}

	public function getId(): int {
		return $this->id;
	}

	public function getFirstname(): ?string {
		return $this->firstname;
	}

	public function getLastname(): ?string {
		return $this->lastname;
	}

	public function getSex(): ?string {
		$sex = $this->sex;
		if ($sex == 'un homme') {
			$sex = 'menu.man';
		}
		if ($sex == 'une femme') {
			$sex = 'menu.woman';
		}
		return $sex;
	}

	public function getEmail(): ?string {
		return $this->email;
	}

	public function getCreatedDate(): ?\DateTime {
		return $this->createdDate;
	}

	public function getCheckSchool(): ?bool {
		return $this->checkSchool;
	}

	public function getLastDegreeYear(): ?int {
		return $this->lastDegreeYear;
	}

	public function getLastDegreeMonth(): ?int {
		return $this->lastDegreeMonth;
	}

	public function getType(): ?string {
		return $this->type;
	}

	public function getOtherSchool(): ?string {
		return $this->otherSchool;
	}

	public function getPhoneMobile1(): ?string {
		return $this->phoneMobile1;
	}

	public function getRegistrationStudentSchool(): ?string {
		return $this->registrationStudentSchool;
	}

	public function getActivityId(): ?int {
		return $this->activityId;
	}

	public function getActivityName(): ?string {
		return $this->activityName;
	}

	/*public function getSectorAreaId(): ?int {
		return $this->sectorAreaId;
	}

	public function getSectorAreaName(): ?string {
		return $this->sectorAreaName;
	}*/

	public function getDegreeId(): ?int {
		return $this->degreeId;
	}

	public function getDegreeName(): ?string {
		return $this->degreeName;
	}

    public function getCityId(): ?int {
        return $this->cityId;
    }

    public function getCityName(): ?string {
        return $this->cityName;
    }
	public function getCountryId(): ?int {
		return $this->countryId;
	}

	public function getCountryName(): ?string {
		return $this->countryName;
	}

	public function getSchoolId(): ?int {
		return $this->schoolId;
	}

	public function getSchoolName(): ?string {
		return $this->schoolName;
	}


    public function getPrefectureId(): ?int {
        return $this->prefectureId;
    }

    public function getPrefectureName(): ?string {
        return $this->prefectureName;
    }

	public function getSchoolCityName(): ?string {
		return $this->schoolCityName;
	}

	public function school(): ?string {
		if ($this->schoolCityName) {
			return $this->schoolName . ', ' . $this->schoolCityName;
		} return '';
	}

	public function country(): ?string {
		return $this->countryName;
	}
    public function city(): ?string {
        return $this->cityName;
    }

    public function degree(): ?string {
		return $this->degreeName;
	}

	public function activity(): ?string {
		return $this->activityName;
	}

	public function getBirthDate(): ?\DateTime {
		return $this->birthDate;
	}

	public function getSatisfactionSearchesCount(): int {
		return $this->satisfactionSearchesCount;
	}

	public function getSatisfactionSalariesCount(): int {
		return $this->satisfactionSalariesCount;
	}

	public function getSatisfactionCreatorsCount(): int {
		return $this->satisfactionCreators_count;
	}

}
