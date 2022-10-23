<?php

namespace App\Controller;

use App\Entity\JobOffer;
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

                            $err[] = "l'établissement " . $userId . " contient des dipômés et ne peut être supprimé";
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
                    $err[] = "erreur lors de la suppression de l'offre " . $offerId . ": " . $e->getMessage();
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

            $res[] = [
                'id' => $jobOffer->getId(),
                'country' => $jobOffer->getCountry()->getName(),
                'company' => $jobOffer->getCompany()->getName(),
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
}
