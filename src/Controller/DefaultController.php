<?php

namespace App\Controller;

use App\Entity\AvatarDTO;
use App\Entity\User;
use App\Entity\School;
use App\Repository\UserRepository;
use App\Repository\CityRepository;
use App\Repository\RegionRepository;
use App\Repository\PrefectureRepository;
use App\Repository\SchoolRepository;
use App\Form\AvatarType;
use App\Services\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DefaultController extends AbstractController {
    private PrefectureRepository $prefectureRepository;
    private UserRepository $userRepository;
    private CityRepository $cityRepository;
    private RegionRepository $regionRepository;
    private SchoolRepository $schoolRepository;
    private TranslatorInterface $translator;

    public function __construct(
        PrefectureRepository    $prefectureRepository,
        UserRepository         $userRepository,
        CityRepository          $cityRepository,
        RegionRepository        $regionRepository,
        SchoolRepository        $schoolRepository,
        TranslatorInterface     $translator
    ) {
        $this->prefectureRepository = $prefectureRepository;
        $this->userRepository = $userRepository;
        $this->cityRepository = $cityRepository;
        $this->regionRepository = $regionRepository;
        $this->schoolRepository = $schoolRepository;
        $this->translator = $translator;
    }

	#[Route(path: '/', name: 'homepage', methods: ['GET'])]
	public function indexAction(Request $request): RedirectResponse {
		return $this->redirectToRoute('dashboard_index');
	}

	#[Route(path: '/rgpd_informations', name: 'rgpd_informations', methods: ['GET'])]
	public function showRgpdInformationsAction(Request $request): Response {

        try {
            return $this->render('information_rgpd_' . $request->getLocale() . '.html.twig');
        } catch (\Exception $e) {
            return $this->render('information_rgpd.html.twig');
        }
	}

	#[Route(path: '/change_profile', name: 'change_profile', methods: ['GET', 'POST'])]
	#[IsGranted('ROLE_USER')]
	public function newAction(
		Request $request,
		Security $security,
		EntityManagerInterface $em,
		FileUploader $fileUploader
	): RedirectResponse|Response {
		$avatar = new AvatarDTO();

		$form = $this->createForm(AvatarType::class, $avatar);
		$form->handleRequest($request);
		/** @var User $user */
		$user = $security->getUser();

		if ($form->isSubmitted() && $form->isValid()) {
			$avatarDescription = $form->get('file')->getData();
			if ($avatarDescription) {
				$avatarDescriptionFileName = $fileUploader->uploadAvatar($avatarDescription, $user->getImageName());
				$user->setImageName($avatarDescriptionFileName);
			}

			$em->persist($user);
			$em->flush();
		}
		return $this->render('user/avatar.html.twig', [
			'avatar' => $avatar,
			'form' => $form->createView(),
			'username' => $user->getUserIdentifier(),
		]);
	}

	#[Route(path: '/get_js_translation', name: 'get_js_translation', methods: ['GET'])]
	public function getJsTranslation(Request $request): JsonResponse {
		// Read xml file
		$xmlFile = $this->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . 'translations' . DIRECTORY_SEPARATOR . 'messages.' . $request->getLocale() . '.xlf';
		$content = simplexml_load_file($xmlFile);
		$result = array();

		foreach ($content as $files) {
			foreach ($files as $file)
				foreach ($file as $body)
					if ((strpos($body->source, "js.") > -1) ||
						(strpos($body->source, "time.") > -1) ||
						(strpos($body->source, "country.") > -1) ||
						(strpos($body->source, "sectors.") > -1) ||
						(strpos($body->source, "sub_sectors.") > -1) ||
						(strpos($body->source, "legal_status.") > -1) ||
						(strpos($body->source, "raisons_no_job.") > -1) ||
						(strpos($body->source, "diplomas.") > -1)
					) {
						$src = (string)$body->source;
						$target = str_replace("'", "\"", $body->target);

						if (strlen($target) == 0) {
							$target = $src;
						}
						$result[$src] = $target;
					}
		}

		return new JsonResponse($result);
	}

    #[Route(path: '/getRegionsByCountry', name: 'get_regions_by_country', methods: ['GET'])]
    public function getRegionsByCountry(Request $request): JsonResponse|Response {
        $idCountry = $request->query->get("countryId");
        $regions = [];
        $cities = [];
        $regionsRepos = $this->regionRepository->findByCountry($idCountry);
        foreach ($regionsRepos as $region) {
            $repos = $this->cityRepository->findByRegion($region->getId());
            foreach ($repos as $city) {
                $cities[$city->getId()] = $city->getName();
            }
            $regions[$region->getId()] = $this->translator->trans($region->getName());
        }
        return new JsonResponse(["regions"=>$regions, "cities"=>$cities]);
    }

    #[Route(path: '/getPrefecturesByRegion', name: 'get_prefectures_by_region', methods: ['GET'])]
    public function getPrefecturesByRegion(Request $request): JsonResponse|Response {
        $idRegion = $request->query->get("regionId");
        $prefectures[] = [];
        $cities[] = [];
        $prefecturesRepos = $this->prefectureRepository->findByRegion($idRegion);
        $citiesRepos = $this->cityRepository->findByRegion($idRegion);
        foreach ($citiesRepos as $city) {
            $cities[$city->getId()] = $city->getName();
        }
        foreach ($prefecturesRepos as $prefecture) {
            $prefectures[$prefecture->getId()] = $prefecture->getName();
        }
        return new JsonResponse(["prefectures"=>$prefectures, "cities"=>$cities]);
    }

    #[Route(path: '/getCitiesByPrefecture', name: 'get_cities_by_prefecture', methods: ['GET'])]
    public function getCitiesByPrefecture(Request $request ): JsonResponse|Response {
        $idPrefecture = $request->query->get("prefectureId");
        $cities[] = [];
        $repos = $this->cityRepository->findByPrefecture($idPrefecture);
        foreach ($repos as $city) {
            $cities[$city->getId()] = $city->getName();
        }

        return new JsonResponse(["cities"=>$cities]);
    }

    #[Route(path: '/getCitiesByCountry', name: 'get_cities_by_country', methods: ['GET'])]
    public function getCitiesByCountry(Request $request ): JsonResponse|Response {
        $idCountry = $request->query->get("countryId");
        $regionsRepos = $this->regionRepository->findByCountry($idCountry);
        $citiesRepos = [];
        foreach ($regionsRepos as $region) {
            $citiesRepos = array_merge($citiesRepos, $this->cityRepository->findByRegion($region->getId()));
        }
        $cities[] = [];
        foreach ($citiesRepos as $city) {
            $cities[$city->getId()] = $city->getName() . '-' . $this->translator->trans($city->getRegion()->getName());
        }

        return new JsonResponse($cities);
    }
    #[Route(path: '/getCitiesByRegion', name: 'get_cities_by_region', methods: ['GET'])]
    public function getCitiesByRegion(Request $request ): JsonResponse|Response {
        $idRegion = $request->query->get("regionId");
        $citiesRepos = $this->cityRepository->findByRegion($idRegion);
        $cities[] = [];
        foreach ($citiesRepos as $city) {
            $cities[$city->getId()] = $city->getName() . '-' . $this->translator->trans($city->getRegion()->getName());
        }

        return new JsonResponse($cities);
    }

    #[Route(path: '/getRegionAndPrefectureByCity', name: 'get_region_and_prefecture_by_city', methods: ['GET'])]
    public function getRegionAndPrefecturesByCity(Request $request): JsonResponse|Response {
        $idCity = $request->query->get("cityId");
        $city = $this->cityRepository->find($idCity);
        $prefectureId = null;
        $prefectureName = null;
        $city->getPrefecture() ? $prefectureId = $city->getPrefecture()->getId() : null;
        $city->getPrefecture() ? $prefectureName = $city->getPrefecture()->getName() : null;

        return new JsonResponse(["regionId"=>$city->getRegion()->getId(),
            "regionName"=>$city->getRegion()->getName(),
            "prefectureId"=>$prefectureId,
            "prefectureName"=>$prefectureName
            ]);
    }

    #[Route(path: '/getSchoolsByCountry', name: 'get_schools_by_country', methods: ['GET'])]
    public function getSchoolsByCountry(Request $request): JsonResponse|Response {
        $idCountry = $request->query->get("countryId");
        $schoolsRepos = $this->schoolRepository->findByCountry($idCountry);
        $schools = [];
        foreach ($schoolsRepos as $school) {
            // $schools[] = ["id"=>$school->getId(), "name"=>($school->getName(). " (" . $school->getCity()->getName()). ")"];
            $schools[$school->getId()] = $school->getName(). " (" . $school->getCity()->getName(). ")";
        }

        return new JsonResponse($schools);
    }

    #[Route(path: '/getSchoolsByRegion', name: 'get_schools_by_region', methods: ['GET'])]
    public function getSchoolsByRegion(Request $request): JsonResponse|Response {
        $idRegion = $request->query->get("regionId");
        // var_dump($idRegion);die();
        $schoolsRepos = $this->schoolRepository->findByRegion($idRegion);
        $schools = [];
        foreach ($schoolsRepos as $school) {
            // $schools[] = ["id"=>$school->getId(), "name"=>($school->getName(). " (" . $school->getCity()->getName()). ")"];
            $schools[$school->getId()] = $school->getName(). " (" . $school->getCity()->getName(). ")";
        }

        return new JsonResponse($schools);
    }

    #[Route(path: '/getSchoolsByPrefecture', name: 'get_schools_by_prefecture', methods: ['GET'])]
    public function getSchoolsByPrefecture(Request $request): JsonResponse|Response {
        $idPrefecture = $request->query->get("prefectureId");
        $cities = $this->cityRepository->findByPrefecture($idPrefecture);
        $schoolsRepos = [];
        foreach ($cities as $city) {
            $schoolsRepos = array_merge($schoolsRepos, $this->schoolRepository->findBycity($city));
        }

        $schools = [];
        foreach ($schoolsRepos as $school) {
            // if (!array_key_exists('id', $schools)) {
            //     $schools[] = ["id"=>$school->getId(), "name"=>($school->getName(). " (" . $school->getCity()->getName()). ")"];
                $schools[$school->getId()] = $school->getName(). " (" . $school->getCity()->getName(). ")";
            // }
        }

        return new JsonResponse($schools);
    }
    #[Route(path: '/getSchoolsByCity', name: 'get_schools_by_city', methods: ['GET'])]
    public function getSchoolsBycity(Request $request): JsonResponse|Response {
        $idCity = $request->query->get("cityId");
        $schoolsRepos = $this->schoolRepository->findBycity($idCity);

        $schools = [];
        foreach ($schoolsRepos as $school) {
            // $schools[] = ["id"=>$school->getId(), "name"=>($school->getName(). " (" . $school->getCity()->getName()). ")"];
            $schools[$school->getId()] = $school->getName(). " (" . $school->getCity()->getName(). ")";
        }

        return new JsonResponse($schools);
    }

    /**
     * check in phoneNumber, pseudo or email already used in user
     * @param Request $request
     * @return JsonResponse|Response
     */
    #[Route(path: '/getExistUser', name: 'get_exist_user', methods: ['GET'])]
    public function getExistUser(Request $request): JsonResponse|Response {

        $userId = null;
        $dbUserPhone = $request->query->get("dbUserPhone");
        $userPhone = $request->query->get("userPhone");
        $userPseudo = $request->query->get("userPseudo");
        $userEmail = $request->query->get("userEmail");
        $result = [];

        if($dbUserPhone) {
            $users = $this->userRepository->findByPhone($dbUserPhone);
            if($users) {
                $userId = $users[0]->getId();
            }
        }

        $users = $this->userRepository->findByPhone($userPhone);
        if($users) {
            if($userId != $users[0]->getId()) {
                $result["phone"] = $this->translator->trans("js.error_phone_number_already_used");
                $userId = $users[0]->getId();
            }
        }

        if(!$result) {
            $users = $this->userRepository->findByUsername($userPseudo);
            if ($users) {
                if ($userId != $users[0]->getId()) {
                    if ($users[0]->getId() != $userId) {
                        $result["pseudo"] = $this->translator->trans("js.error_pseudo_already_used");
                    }
                }
            }
        }

        if(!$result) {
            $users = $this->userRepository->findByEmail($userEmail);
            if ($users) {
                if ($userId != $users[0]->getId()) {
                    if ($users[0]->getId() != $userId) {
                        $result["email"] = $this->translator->trans("js.error_email_already_used");
                    }
                }
            }
        }

        return new JsonResponse($result);
    }
}
