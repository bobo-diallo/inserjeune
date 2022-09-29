<?php

namespace App\Twig;

use App\Services\PersonDegreeService;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use App\Tools\Utils;

class AppExention extends AbstractExtension {
	private TokenStorageInterface $tokenStorage;
	private PersonDegreeService $degreeService;

	public function __construct(TokenStorageInterface $tokenStorage, PersonDegreeService $degreeService) {
		$this->tokenStorage = $tokenStorage;
		$this->degreeService = $degreeService;
	}

	public function getFilters(): array {
		return [
			new TwigFilter('role', [$this, 'formatRole']),
			new TwigFilter('dir_role', [$this, 'dirRole']),
			new TwigFilter('route_company', [$this, 'generateRouteCompany']),
			new TwigFilter('route_persondegree', [$this, 'generateRoutePersondegree']),
			new TwigFilter('route_school', [$this, 'generateRouteSchool']),
			new TwigFilter('type_degree', [$this, 'getTypeDegree']),
		];
	}

	/**
	 * @param array $roles
	 * @return string
	 */
	public function formatRole(array $roles): string {
		if (in_array('ROLE_ADMIN', $roles)) return 'Administrateur';
		else if (in_array('ROLE_LEGISLATEUR', $roles)) return 'Législateur';
		else if (in_array(Utils::COMPANY, $roles)) return 'Entreprise';
		else if (in_array(Utils::PERSON_DEGREE, $roles)) return 'Diplômé';
		else if (in_array('ROLE_ENQUETEUR', $roles)) return 'Enquêteur';
		else if (in_array(Utils::SCHOOL, $roles)) return 'Etablissement';
		else return 'User';
	}

	/**
	 * @param array $roles
	 * @return string
	 */
	public function dirRole(array $roles): string {
		if (in_array('ROLE_ADMIN', $roles)) return 'base.html.twig';
		else if (in_array('ROLE_ENTREPRISE', $roles)) return 'frontCompany';
		else return 'USER';
	}

	/**
	 * Permet de preceder une route par front_company_ si l'utilisateur a le rôle ROLE_ENTREPRISE
	 *
	 * @param $route
	 * @return string
	 */
	public function generateRouteCompany($route): string {
		$roles = $this->tokenStorage->getToken()->getUser()->getRoles();

		if (in_array(Utils::COMPANY, $roles)) {
			$deb = (str_starts_with($route, 'company_')) ? 'front_' : 'front_company_';
			return $deb . $route;
		}

		return $route;
	}

	/**
	 * Permet de preceder une route par front_persondegree_ si l'utilisateur a le rôle ROLE_DIPLOME
	 *
	 */
	public function generateRoutePersondegree($route): string {
		$roles = $this->tokenStorage->getToken()->getUser()->getRoles();

		if (in_array(Utils::PERSON_DEGREE, $roles)) {
			$deb = (str_starts_with($route, 'persondegree_')) ? 'front_' : 'front_persondegree_';
			return $deb . $route;
		}
		return $route;
	}

	/**
	 * Permet de preceder une route par front_persondegree_ si l'utilisateur a le rôle ROLE_DIPLOME
	 *
	 * @param $route
	 * @return string
	 */
	public function generateRouteSchool($route): string {
		$roles = $this->tokenStorage->getToken()->getUser()->getRoles();

		if (in_array(Utils::SCHOOL, $roles)) {
			$deb = (str_starts_with($route, 'school_')) ? 'front_' : 'front_school_';
			return $deb . $route;
		}
		return $route;
	}

	/**
	 * Retour le libelle associé au type de diplomé
	 * Exemple: TYPE_EMPLOYED = 'En emploi'
	 *
	 * @param $type
	 * @return string
	 */
	public function getTypeDegree($type): string {
		return $this->degreeService->getTypes()[$type];
	}


}
