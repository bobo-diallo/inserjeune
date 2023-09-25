<?php

namespace App\Twig;

use App\Services\PersonDegreeService;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormView;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use App\Tools\Utils;
use Twig\TwigFunction;
use Symfony\Contracts\Translation\TranslatorInterface;

class AppExention extends AbstractExtension {
	private TokenStorageInterface $tokenStorage;
	private PersonDegreeService $degreeService;
    private TranslatorInterface $translator;

	public function __construct(
        TokenStorageInterface $tokenStorage,
        PersonDegreeService $degreeService,
        TranslatorInterface $translator,
    ) {
		$this->tokenStorage = $tokenStorage;
		$this->degreeService = $degreeService;
        $this->translator = $translator;
	}

    public function getFunctions(): array {
        return [
            new TwigFunction('create_translated_select', [$this, 'createTranslatedSelect'], ['is_safe' => ['html']])
        ];
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
		else if (in_array('ROLE_ADMIN_PAYS', $roles)) return 'Admin_pays';
		else if (in_array('ROLE_ADMIN_REGIONS', $roles)) return 'Admin_regions';
		else if (in_array('ROLE_ADMIN_VILLES', $roles)) return 'Admin_villes';
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

    /**
     * @param FormView $data
     * @return string
     */
    public function createTranslatedSelect (FormView  $data): string {
        $formParams = explode('_', $data->vars["id"]);

        if(count($formParams) == 3) {
            $selectClass = $formParams[2];
        }

        $id = $data->vars["id"];
        $fullName = $data->vars["full_name"];
            $required = $data->vars["required"];
            $attr = $data->vars["attr"];
            $choices = $data->vars["choices"];
            $selectValue = $data->vars["value"];

            // if without select2 class
            // $id = "appbundle_" . $actor . "_" . $selectClass;
            // $name = "appbundle_" . $actor . "[" . $selectClass . "]";

            // adaptation for select2
            $classes = explode(" ", $attr["class"]);
            if (in_array("select2", $classes)) {
                    // $id = "userbundle_" . $actor . "_" . $selectClass;
                    // $name = "userbundle_" . $actor . "[" . $selectClass . "][]";
                    $required = '"" multiple=""';
            }

            $html = sprintf('<select id="%s" 
                                       name="%s" 
                                       required="%s" 
                                       class="%s">',
                $id, $fullName, $required, $attr["class"]);

            if (!in_array("select2", $classes)) {
                $html .= sprintf('  <option value="">%s</option>',
                    $this->translator->trans('menu.select'));
            }

            foreach ($choices as $choice) {
                $label = $choice->label;
                // if ($selectClass == "adminCities") {
                //     $actorExplode  = explode("-", $choice->label);
                //     if(count($actorExplode) == 3) {
                //         $countryActor = $this->translator->trans(strtolower(trim($actorExplode[0])));
                //         $regionActor = $this->translator->trans(strtolower(trim($actorExplode[1])));
                //         $cityActor = $this->translator->trans(strtolower(trim($actorExplode[2])));
                //         $label = $countryActor . " - " .$regionActor . " - " . $cityActor;
                //     }
                // }
                if($selectValue == $choice->value) {
                    $html .= sprintf('    <option selected value="%s">%s</option>',
                        $choice->value, ucfirst($this->translator->trans($label)));

                } else {
                    $html .= sprintf('    <option value="%s">%s</option>',
                        $choice->value, ucfirst($this->translator->trans($label)));
                }
            }

            $html .= sprintf('</select>');
            return $html;
        // }
        // return "Error Server";
    }
}
