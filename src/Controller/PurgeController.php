<?php

namespace App\Controller;

use App\Entity\JobOffer;
use App\Entity\PersonDegree;
use App\Entity\Role;
use App\Entity\School;
use App\Services\SchoolService;
use App\Services\CompanyService;
use App\Services\PersonDegreeService;
use App\Entity\User;
use App\Repository\CountryRepository;
use App\Repository\SchoolRepository;
use App\Repository\CompanyRepository;
use App\Repository\PersonDegreeRepository;
use App\Repository\UserRepository;
use App\Repository\JobOfferRepository;
use App\Tools\Utils;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;

#[Route(path: '/purge')]
#[Security("is_granted('ROLE_ADMIN')")]
class PurgeController extends AbstractController {
	private EntityManagerInterface $em;
	private CountryRepository $countryRepository;
	private SchoolRepository $schoolRepository;
    private CompanyRepository $companyRepository;
	private PersonDegreeRepository $personDegreeRepository;
	private UserRepository $userRepository;
	private JobOfferRepository $jobOfferRepository;
    private SchoolService $schoolService;
    private CompanyService $companyService;
    private PersonDegreeService $personDegreeService;

	public function __construct(
		EntityManagerInterface $em,
        CountryRepository        $countryRepository,
		SchoolRepository        $schoolRepository,
        CompanyRepository       $companyRepository,
        PersonDegreeRepository  $personDegreeRepository,
        SchoolService           $schoolService,
        CompanyService          $companyService,
        PersonDegreeService     $personDegreeService,
		UserRepository          $userRepository,
		JobOfferRepository      $jobOfferRepository
	) {
		$this->em = $em;
		$this->countryRepository = $countryRepository;
		$this->schoolRepository = $schoolRepository;
        $this->companyRepository = $companyRepository;
        $this->personDegreeRepository = $personDegreeRepository;
		$this->userRepository = $userRepository;
		$this->jobOfferRepository = $jobOfferRepository;
        $this->schoolService = $schoolService;
        $this->companyService = $companyService;
        $this->personDegreeService = $personDegreeService;
	}

    #[Route(path: '/', name: 'purge', methods: ['GET'])]
    public function purgeAction(Request $request): Response {
        $session = $request->getSession();
        $idSelectedCountry = "";
        if ($session->has('pays')) {
            $idSelectedCountry = $session->get('pays');
        }
        $countries = $this->countryRepository->findAll();
        return $this->render('purge/purge.html.twig', [
            'countries' => $countries,
            'idSelectedCountry' => $idSelectedCountry,
        ]);
    }

	/**
	 * @throws Exception
	 */
	#[Route(path: '/findActor', name: 'find_actor', methods: ['GET'])]
    public function findActorAction(Request $request): JsonResponse|Response {
        $phone=$request->query->get('userPhone');
        $res = [];
        /* list of Users with a part of phone number */
		if ($phone) {
			$users = $this->userRepository->getByBeginPhoneNumber('%' . $phone . '%');
			$res = [];
			foreach ($users as $user) {
				$res[] = ['id' => $user->getId(), 'name' => $user->getPhone(), 'type' => $this->findOrphansUser($user)];
			}

			/* List only Orphans Users */
        } else {
            $usersCompany = $this->userRepository->getByRole('ROLE_ENTREPRISE');
            //var_dump($usersCompany);die();
            foreach ($usersCompany as $user) {
                $type = $this->findOrphansUser($this->userRepository->find($user['id']));
                if($type != 'Entreprise')
                   $res[] = ['id' => $user['id'], 'name' => $user['phone'], 'type' => $type];
            }

			$usersPersonDegree = $this->userRepository->getByRole(Role::ROLE_DIPLOME);
			foreach ($usersPersonDegree as $user) {
				$type = $this->findOrphansUser($this->userRepository->find($user['id']));
				if ($type != 'Diplômé')
					$res[] = ['id' => $user['id'], 'name' => $user['phone'], 'type' => $type];
			}

			$usersSchool = $this->userRepository->getByRole(Role::ROLE_ETABLISSEMENT);
			foreach ($usersSchool as $user) {
				$type = $this->findOrphansUser($this->userRepository->find($user['id']));
				if ($type != 'Etablissement')
					$res[] = ['id' => $user['id'], 'name' => $user['phone'], 'type' => $type];
			}
		}
        return new JsonResponse($res);
    }

    #[Route(path: '/removeActors', name: 'remove_actors', methods: ['GET'])]
    public function removeActors(Request $request): JsonResponse|Response
    {
        $ids = $request->query->all();
        $userIds = explode(',',$ids['ids']);
        $res=[];
        $err=[];
        /* list of Users with a part of phone number */
        foreach ($userIds as $userId) {

            $user = $this->userRepository->find((int)$userId);

            if ($user) {
                try {
                    if ($user->getSchool()) {
                        // echo(count($user->getSchool()->getPersonDegrees()));die();
                        if(count($user->getSchool()->getPersonDegrees()) >0) {

                            $err[] = "js.error_school_contains_graduates_and_cannot_be_deleted_part1" . " " . $userId . " " . "js.error_school_contains_graduates_and_cannot_be_deleted_part2";
                        } else {
                            $this->schoolService->removeRelations($user);
                        }
                    }
                    if ($user->getCompany())
                        $this->companyService->removeRelations($user);
                    if ($user->getPersonDegree())
                        $this->personDegreeService->removeRelations($user);

                    if(count($err)==0) {
                        $this->em->remove($user);
                        $this->em->flush();
                    }

                    $res[] = $userId;
                } catch (Exception $e){
                    $err[] = "erreur lors de la suppression du user " . $userId . ": " . $e->getMessage();
                }
            } else {
                $res[] = $userId . ' non trouvé ';
            }
        }
        return new JsonResponse([$res, $err]);
    }

    #[Route(path: '/removeOffers', name: 'remove_offers', methods: ['GET'])]
    public function removeOffers(Request $request): JsonResponse|Response
    {
        $ids = $request->query->all();
        $offerIds = explode(',',$ids['ids']);
        $res=[];
        $err=[];

        foreach ($offerIds as $offerId) {
            $offer = $this->jobOfferRepository->find((int)$offerId);

            if ($offer) {
                try {
                     if(count($err)==0) {
                        $this->em->remove($offer);
                        $this->em->flush();
                    }

                    $res[] = $offerId;
                } catch (Exception $e){
                    $err[] = "js.error_while_deleting_the_offer" . " " . $offerId . ": " . $e->getMessage();
                }
            } else {
                $res[] = $offerId . ' non trouvé ';
            }
        }
        return new JsonResponse([$res, $err]);
    }

    #[Route(path: '/findJobOffer', name: 'find_job_offer', methods: ['GET', 'POST'])]
    public function findJobOfferAction(Request $request): JsonResponse|Response {

        $query = $request->query->all();
        $action = "";
        if($query)
            $action = $query['action'];

        $res = [];
        $err = [];
        $check = [];
        $jobOffers = [];

        $jobOffers = $this->jobOfferRepository->findAll();

        if($action == "closedDate") {
            $jobOffers = $this->jobOfferRepository->getByEmptyEndedDate();
            foreach ($jobOffers as $jobOffer) {
                if ($jobOffer->getUpdatedDate() == null) {
                    $jobOffer->setUpdatedDate($jobOffer->getCreatedDate());
                }
                if ($jobOffer->getClosedDate() == null) {
                    $date = clone $jobOffer->getUpdatedDate();
                    $date = $date->add(new \DateInterval('P6M'));
                    $jobOffer->setClosedDate($date->format(Utils::FORMAT_FR));
                }
                if(count($err)==0) {
                    $this->em->persist($jobOffer);
                    $this->em->flush();
                }
            }
        }

        if($action == "findObsoleteOffers") {
            foreach ($jobOffers as $jobOffer) {
                $closeDateStr = str_replace("/", "-", $jobOffer->getClosedDate());
                $closeDate = new DateTime($closeDateStr);

                //date courante moins 6 mois
                $deleteDate = (new DateTime())->sub(new \DateInterval('P6M'));

                if($closeDate < $deleteDate) {
                    //var_dump("asup      " . $closeDate->format('Y-m-d') . ' | ' . $deleteDate->format('Y-m-d'));
                    $check[] = $jobOffer->getId();
                }
                // else {
                //     var_dump("conserver " . $closeDate->format('Y-m-d') . ' | ' . $deleteDate->format('Y-m-d'));
                // }
            }
        }

        foreach ($jobOffers as $jobOffer) {
            $closeDate = "null";
            if($jobOffer->getClosedDate() != null) {
                $closeDateStr = str_replace("/", "-", $jobOffer->getClosedDate());
                $closeDate = (new DateTime($closeDateStr))->format('Y-m-d');
            }

            $name = null;
            $type = null;
            if($jobOffer->getCompany()) {
                $name = $jobOffer->getCompany()->getName();
                $type = "company";
            } elseif ($jobOffer->getSchool()) {
                $name = $jobOffer->getSchool()->getName();
                $type = "school";
            }
            $res[] = [
                'id' => $jobOffer->getId(),
                'country' => $jobOffer->getCountry()->getName(),
                'type' => $type,
                'name' => $name,
                'title' => $jobOffer->getTitle(),
                'updateDate' => $jobOffer->getUpdatedDate()->format('Y-m-d'),
                'closedDate' => $closeDate
            ];
        }
        return new JsonResponse([$res, $err, $check]);
    }

	public function findOrphansUser(User $user): string {
		$type = 'Orphelin: ';
		if ($user->getCompany()) {
			$type = 'Entreprise';
		} elseif ($user->getPersonDegree()) {
			$type = 'Diplômé';
		} elseif ($user->getSchool()) {
			$type = 'Etablissement';
		} else {
			foreach ($user->getRoles() as $role) {
				if ($role != 'ROLE_USER')
					$type .= $role . ';';
			}
		}
		return ($type);
	}

    #[Route(path: '/getAllActorsWithoutCoordinate', name: 'get_all_actors_without_coordinate', methods: ['GET'])]
    public function getAllActorsWithoutCoordinate(Request $request): JsonResponse {
        $actorType = $request->get('actor');
        $result= [];
        if($actorType == 'persondegree') {
            $actors = $this->personDegreeRepository->getWithoutCoordinate();
            foreach ($actors as $actor) {
                $persondegree = $this->personDegreeRepository->find($actor);
                $city = null; $country = null; $createdDate=null; $updatedDate=null;
                if($persondegree->getAddressCity())
                    $city = $persondegree->getAddressCity()->getName();
                if($persondegree->getCountry())
                    $country = $persondegree->getCountry()->getName();
                if($persondegree->getCreatedDate())
                    $createdDate = $persondegree->getCreatedDate()->format(Utils::FORMAT_FR);
                if($persondegree->getUpdatedDate())
                    $updatedDate = $persondegree->getUpdatedDate()->format(Utils::FORMAT_FR);

                $result[] = [
                    'id'=>$persondegree->getId(),
                    'country'=>$country,
                    'city'=>$city,
                    'actor'=>$actorType,
                    'error'=>"No Coordinate",
                    'created_date'=>$createdDate,
                    'updated_date'=>$updatedDate,
                ];
            }
        } elseif ($actorType == 'school') {
            $actors = $this->schoolRepository->getWithoutCoordinate();
            foreach ($actors as $actor) {
                $school = $this->schoolRepository->find($actor);
                $city = null; $country = null; $createdDate=null; $updatedDate=null;
                if($school->getCity())
                    $city = $school->getCity()->getName();
                if($school->getCountry())
                    $country = $school->getCountry()->getName();
                if($school->getCreatedDate())
                    $createdDate = $school->getCreatedDate()->format(Utils::FORMAT_FR);
                if($school->getUpdatedDate())
                    $updatedDate = $school->getUpdatedDate()->format(Utils::FORMAT_FR);

                $result[] = [
                    'id'=>$school->getId(),
                    'country'=>$country,
                    'city'=>$city,
                    'actor'=>$actorType,
                    'error'=>"No Coordinate",
                    'created_date'=>$createdDate,
                    'updated_date'=>$updatedDate,
                ];
            }
        } elseif ($actorType == 'company') {
            $actors = $this->companyRepository->getWithoutCoordinate();
            foreach ($actors as $actor) {
                $company = $this->companyRepository->find($actor);
                $city = null; $country = null; $createdDate=null; $updatedDate=null;
                if($company->getCity())
                    $city = $company->getCity()->getName();
                if($company->getCountry())
                    $country = $company->getCountry()->getName();
                if($company->getCreatedDate())
                    $createdDate = $company->getCreatedDate()->format(Utils::FORMAT_FR);
                if($company->getUpdatedDate())
                    $updatedDate = $company->getUpdatedDate()->format(Utils::FORMAT_FR);

                $result[] = [
                    'id'=>$company->getId(),
                    'country'=>$country,
                    'city'=>$city,
                    'actor'=>$actorType,
                    'error'=>"No Coordinate",
                    'created_date'=>$createdDate,
                    'updated_date'=>$updatedDate,
                ];
            }
        }
        return new JsonResponse([$result]);
    }

    #[Route(path: '/updateLocalizationActors', name: 'update_localization_actors', methods: ['GET'])]
    public function updateLocalizationActors(Request $request): JsonResponse {
        $actorIds = explode(',',$request->get('ids'));
        $coos = explode(';',$request->get('coos'));
        $actorType = $request->get('actorType');
        $result = [];
        $error = [];

        if(count($coos) == count($actorIds)) {
            for ($i = 0; $i < count($actorIds); $i++) {
                $actor=null;
                if ($actorType == 'persondegree') {
                    $actor = $this->personDegreeRepository->find($actorIds[$i]);
                } elseif ($actorType == 'school') {
                    $actor = $this->schoolRepository->find($actorIds[$i]);
                } elseif ($actorType == 'company') {
                    $actor = $this->companyRepository->find($actorIds[$i]);
                }

                $coo = explode(',', $coos[$i]);
                $newCoos = $this->getNewCoordinatesNearActorsInCountry($actorType, $actorIds[$i], $coo[0], $coo[1]);

                // var_dump($coo[0], $coo[1]);
                // var_dump($newCoos['latitude'],$newCoos['longitude']);

                if($newCoos['latitude'] && $newCoos['longitude']) {
                    $actor->setLatitude($newCoos['latitude']);
                    $actor->setLongitude($newCoos['longitude']);
                } else {
                    $actor->setLatitude($coo[0]);
                    $actor->setLongitude($coo[1]);
                }
                $actor->setLocationMode(false);

                $this->em->persist($actor);
                $this->em->flush();

                $result[] = $actor->getId();
                // die();
            }
            return new JsonResponse([$result,$error]);
        }
        return new JsonResponse("Incorrect Data Input");
    }
    private function getNewCoordinatesNearActorsInCountry (
        string $actorType,
        int $currentId,
        float $currentLatitude,
        float $currentLongitude): array {
            $gap = 0.0001;
            $newLatitude = $currentLatitude;
            $newLongitude = $currentLongitude;
            $coordinates = [];

            // recherche en base les coordonnées des acteurs de la ville
            if($actorType == "persondegree") {
                $currentPersondegree = $this->personDegreeRepository->find($currentId);
                $coordinates = $this->personDegreeRepository->getPersondegreesByCityForCoordinates($currentPersondegree->getAddressCity());
            } else if($actorType == "school") {
                $currentSchool = $this->schoolRepository->find($currentId);
                $coordinates = $this->schoolRepository->getSchoolsByCityForCoordinates($currentSchool->getCity());
            } else if($actorType == "company") {
                $currentCompany = $this->companyRepository->find($currentId);
                $coordinates = $this->companyRepository->getCompaniesByCityForCoordinates($currentCompany->getCity());
            }

            //boucle sur 300 gaps de longitude
            $maxDuplicateLongitude = 300;
            $maxDuplicateLatitude = 16;
            for ($j = 0; $j < $maxDuplicateLongitude; $j++) {
                // printf("\n---$j = %d----------------------------------------------------\n",$j);

                // boucle sur 16 gaps de latitude (s'il existe un acteur dans les 20 gaps, on passe à la longitude supérieure)
                $actorExist = [];
                for ($i = 1; $i < $maxDuplicateLatitude; $i++) {
                    // printf("-------$i = %d-------------------------------------------------\n",$i);
                    // echo("current =" .$currentId. ": " .$currentLatitude. ";" .$currentLongitude); printf("\n");

                    $actorExist[$i] = "free";
                    for ($k = 0; $k < count($coordinates); $k++) {
                        $actorId = intval($coordinates[$k]['id']);

                        if ($actorId != $currentId) {
                            $actorLatitude = floatval($coordinates[$k]['latitude']);
                            $actorLongitude = floatval($coordinates[$k]['longitude']);

                            if(($actorLatitude > $currentLatitude + $gap * ($i-1)) &&
                               ($actorLatitude <= $currentLatitude + $gap * $i) &&
                               ($actorLongitude >= $currentLongitude + $gap * ($j)) &&
                               ($actorLongitude <= $currentLongitude + $gap * ($j+1))) {
                                    // printf("actor =%s : %.6f; %.6f\n", $actorId, $actorLatitude, $actorLongitude );
                                    $actorExist[$i] = "used";
                            }
                        }
                    }
                }
                if (in_array("free", $actorExist)) {
                    // debugg : affiche les cases libres
                    // printf("\n");
                    // for ($i = 1; $i < count($actorExist)+1; $i++) {
                    //     echo $actorExist[$i] . " | ";
                    // }
                    // printf("\n");
                    for ($i = 1; $i < count($actorExist)+1; $i++) {
                        if($actorExist[$i] == "free") {
                            $newLatitude = $currentLatitude + $gap * $i;
                            $newLongitude = $currentLongitude + $gap * $j;
                            $i = count($actorExist);
                        }
                    }
                    // printf(" new pos = %.7f; %.7f\n", $newLatitude, $newLongitude );
                    $j = $maxDuplicateLongitude;
                }
            }

            return ['latitude' => $newLatitude, 'longitude' => $newLongitude];
        }

    // private function getByDuplicatesCoordinates (
    #[Route(path: '/getAllActorsWithDuplicateCoordinate', name: 'get_all_actors_with_duplicate_coordinate', methods: ['GET'])]
    public function getAllActorsWithDuplicateCoordinate(Request $request): JsonResponse {
        $actorType = $request->get('actor');
        $actors = [];
        $result = [];
        $errors = [];

        if($actorType == 'persondegree') {
            $actors = $this->personDegreeRepository->getSameCordinates();
        } elseif ($actorType == 'school') {
            $actors = $this->schoolRepository->getSameCordinates();
        } elseif ($actorType == 'company') {
            $actors = $this->companyRepository->getSameCordinates();
        }

        return new JsonResponse([$actors, $errors]);
    }
}
