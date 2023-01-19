<?php declare(strict_types=1);

namespace App\Model;

final class PersonDegreeReadOnly {
	private int $id;
	private ?string $firstname;
	private ?string $lastname;
	private ?string $email;
	private ?\DateTime $createdDate;
	private bool $checkSchool;
	private ?int $lastDegreeYear;
	private ?int $lastDegreeMonth;
	private ?string $type;
	private ?string $otherSchool;
	private ?string $phoneMobile1;
	private ?string $registrationStudentSchool;
	private ?\DateTime $birthDate;
	private ?int $activityId;
	private ?string $activityName;
	private ?int $degreeId;
	private ?string $degreeName;
	private ?int $countryId;
	private ?string $countryName;
	private ?int $schoolId;
	private ?string $schoolName;
	private ?string $schoolCityName;
	private int $satisfactionSearchesCount = 0;
	private int $satisfactionSalariesCount = 0;
	private int $satisfactionCreators_count = 0;

	public function __construct(
		int $id,
		?string $firstname,
		?string $lastname,
		?string $email,
		?\DateTime $createdDate,
		bool $checkSchool,
		?int $lastDegreeYear,
		?int $lastDegreeMonth,
		?string $type,
		?string $otherSchool,
		?string $phoneMobile1,
		?string $registrationStudentSchool,
		?\DateTime $birthDate,
		?int $activityId,
		?string $activityName,
		?int $degreeId,
		?string $degreeName,
		?int $countryId,
		?string $countryName,
		?int $schoolId,
		?string $schoolName,
		?string $schoolCityName,
		?int $satisfactionSearchesCount,
		?int $satisfactionSalariesCount,
		?int $satisfactionCreators_count
	) {
		$this->id = $id;
		$this->firstname = $firstname;
		$this->lastname = $lastname;
		$this->email = $email;
		$this->createdDate = $createdDate;
		$this->checkSchool = $checkSchool;
		$this->lastDegreeYear = $lastDegreeYear;
		$this->lastDegreeMonth = $lastDegreeMonth;
		$this->type = $type;
		$this->otherSchool = $otherSchool;
		$this->phoneMobile1 = $phoneMobile1;
		$this->registrationStudentSchool = $registrationStudentSchool;
		$this->activityId = $activityId;
		$this->activityName = $activityName;
		$this->degreeId = $degreeId;
		$this->degreeName = $degreeName;
		$this->countryId = $countryId;
		$this->countryName = $countryName;
		$this->schoolId = $schoolId;
		$this->schoolName = $schoolName;
		$this->schoolCityName = $schoolCityName;
		$this->birthDate = $birthDate;
		$this->satisfactionSearchesCount = $satisfactionSearchesCount;
		$this->satisfactionSalariesCount = $satisfactionSalariesCount;
		$this->satisfactionCreators_count = $satisfactionCreators_count;
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

	public function getEmail(): ?string {
		return $this->email;
	}

	public function getCreatedDate(): ?\DateTime {
		return $this->createdDate;
	}

	public function getCheckSchool(): bool {
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

	public function getDegreeId(): ?int {
		return $this->degreeId;
	}

	public function getDegreeName(): ?string {
		return $this->degreeName;
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
