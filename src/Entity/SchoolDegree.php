<?php

namespace App\Entity;

use App\Repository\SchoolDegreeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'school_degree')]
#[ORM\Entity(repositoryClass: SchoolDegreeRepository::class)]
class SchoolDegree {
	#[ORM\Id]
	#[ORM\ManyToOne(targetEntity: School::class)]
	#[ORM\JoinColumn(name: 'id_school', referencedColumnName: 'id')]
	private School $school;

	#[ORM\Id]
	#[ORM\ManyToOne(targetEntity: Degree::class)]
	#[ORM\JoinColumn(name: 'id_degree', referencedColumnName: 'id')]
	private Degree $degree;
}

