<?php

namespace App\Controller;

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
use App\Tools\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/purge')]
#[Security("is_granted('ROLE_ADMIN')")]
class PurgeController extends AbstractController {
	private EntityManagerInterface $em;
	private CountryRepository $countryRepository;
	private SchoolRepository $schoolRepository;
    private CompanyRepository $companyRepository;
	private PersonDegreeRepository $personDegreeRepository;
	private UserRepository $userRepository;
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
		UserRepository          $userRepository
	) {
		$this->em = $em;
		$this->countryRepository = $countryRepository;
		$this->schoolRepository = $schoolRepository;
        $this->companyRepository = $companyRepository;
        $this->personDegreeRepository = $personDegreeRepository;
		$this->userRepository = $userRepository;
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

	#[Route(path: '/findActor', name: 'find_actor', methods: ['GET'])]
    public function findActorAction(Request $request): JsonResponse|Response {
        $phone=$request->query->get('userPhone');
        $res = [];
        /* list of Users with a part of phone number */
        if($phone) {
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

            $usersPersonDegree = $this->userRepository->getByRole('ROLE_DIPLOME');
            foreach ($usersPersonDegree as $user) {
                $type = $this->findOrphansUser($this->userRepository->find($user['id']));
                if($type != 'Diplômé')
                    $res[] = ['id' => $user['id'], 'name' => $user['phone'], 'type' => $type];
            }

            $usersSchool = $this->userRepository->getByRole('ROLE_ETABLISSEMENT');
            foreach ($usersSchool as $user) {
                $type = $this->findOrphansUser($this->userRepository->find($user['id']));
                if($type != 'Etablissement')
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

    #[Route(path: '/purgeJobOffer', name: 'purge_job_offer', methods: ['GET', 'POST'])]
    public function purgeJobOfferAction(Request $request): JsonResponse|Response {

        $datas = $request->getContent();
        $res = [];

        return new JsonResponse($res);
    }

    public function findOrphansUser(User $user) {
        $type = "Orphelin: ";
        if ($user->getCompany()) {
            $type = "Entreprise";
        } elseif ($user->getPersonDegree()) {
            $type = "Diplômé";
        } elseif ($user->getSchool()) {
            $type = "Etablissement";
        } else {
            foreach ($user->getRoles() as $role) {
                if ($role != 'ROLE_USER')
                    $type .= $role . ';';
            }
        }
        return ($type);
    }
}
