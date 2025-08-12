<?php

namespace App\Twig;

use App\Entity\Region;
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
use App\Repository\PrefectureRepository;

class AppExention extends AbstractExtension {
	private TokenStorageInterface $tokenStorage;
	private PersonDegreeService $degreeService;
    private TranslatorInterface $translator;
    private PrefectureRepository $prefectureRepository;

	public function __construct(
        TokenStorageInterface $tokenStorage,
        PersonDegreeService $degreeService,
        TranslatorInterface $translator,
        PrefectureRepository $prefectureRepository,
    ) {
		$this->tokenStorage = $tokenStorage;
		$this->degreeService = $degreeService;
        $this->translator = $translator;
        $this->prefectureRepository = $prefectureRepository;
	}

    public function getFunctions(): array {
        return [
            new TwigFunction('create_translated_select', [$this, 'createTranslatedSelect'], ['is_safe' => ['html']]),
            new TwigFunction('listen_change_region_prefecture', [$this, 'listenChangeRegionPrefecture'], ['is_safe' => ['html']]),
            new TwigFunction('is_past_date', [$this, 'isPastDate'], ['is_safe' => ['html']])
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
			new TwigFilter('trans_table_format', [$this, 'transTableFormat']),
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
		else if (in_array('ROLE_DIRECTEUR', $roles)) return 'Directeur';
		else if (in_array('ROLE_PRINCIPAL', $roles)) return 'Principal';
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
     * Retour un string formatté de chaque item de la table traduite
     *
     * @param $table
     * @return string
     */
    public function transTableFormat($table): string {
        $result = "" ;
        foreach ($table as $item) {
            $result .=  $this->translator->trans($item) . "\n";
        }
        return $result;
    }

    public function isPastDate($date): string {
        return $date < new \DateTime();
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
        $required = "";
        if($data->vars["required"]) {
            $required = "required";
        }
        $attr = $data->vars["attr"];
        $choices = $data->vars["choices"];
        $selectValues = [];
        if(is_array($data->vars["value"])) {
            $selectValues = $data->vars["value"];
        } else {
            $selectValues[] = $data->vars["value"];
        }
        $multiple = "";
        if($data->vars["multiple"]) {
            $multiple = "multiple = true";
        }

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

        $html = sprintf('<select id="%s" name="%s" %s %s class="%s">',
            $id, $fullName, $multiple, $required, $attr["class"]);

        if (!in_array("select2", $classes)) {
            $html .= sprintf('  <option value="">%s</option>',
                $this->translator->trans('menu.select'));
        }

        foreach ($choices as $choice) {
            $label = $choice->label;

            $transLabel = $this->translator->trans($label);
            if(!$transLabel) {
                $transLabel = $label;
            }

            $labelSelected = false;
            foreach ($selectValues as $selectValue) {
                if ($selectValue == $choice->value) {
                    $labelSelected =true;
                    $html .= sprintf('    <option selected value="%s">%s</option>',
                        $choice->value, ucfirst($transLabel));
                }
            }

            if (!$labelSelected) {
                $html .= sprintf('    <option value="%s">%s</option>',
                    $choice->value, ucfirst($transLabel));
            }
        }

        $html .= sprintf('</select>');
        return $html;
    }

    public function listenChangeRegionPrefecture(FormView $region, FormView $prefecture): string {
        // $choicesRegion = $region->vars["choices"];
        $regionValue = $region->vars["value"];

        $prefectureId = $prefecture->vars["id"];
        $prefectureFullName = $prefecture->vars["full_name"];
        $prefectureRequired = $prefecture->vars["required"];
        $prefectureAttr = $prefecture->vars["attr"];
        $prefectureChoices = $prefecture->vars["choices"];
        $prefectureValue = $prefecture->vars["value"];

        // create select
        $html = sprintf('<select id="%s" name="%s" required="%s" class="%s">',
            $prefectureId, $prefectureFullName, $prefectureRequired, $prefectureAttr["class"]);

        //create option invite to select
        $html .= sprintf('  <option value="">%s</option>',
            $this->translator->trans('menu.select'));

        $prefecture = $this->prefectureRepository->findByRegion($regionValue);

        //create others prefectures option existing in selected region
        foreach ($prefecture as $prefecture) {
            if($prefecture->getId() == $prefectureValue) {
                $html .= sprintf('    <option selected value="%s">%s</option>',
                    $prefecture->getId(), ucfirst($this->translator->trans($prefecture->getName())));

            } else {
                $html .= sprintf('    <option value="%s">%s</option>',
                    $prefecture->getId(), ucfirst($this->translator->trans($prefecture->getName())));
            }
        }

        $html .= sprintf('</select>');
        return $html;
    }
}
