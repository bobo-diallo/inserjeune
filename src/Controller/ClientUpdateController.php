<?php

namespace App\Controller;

use App\Entity\PersonDegree;
use App\Entity\SatisfactionSalary;
use App\Repository\CompanyRepository;
use App\Repository\PersonDegreeRepository;
use App\Services\SchoolService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/front/school/clientUpdate')]
class ClientUpdateController extends AbstractController {
	private SchoolService $schoolService;
	private SerializerInterface $serializer;
	private PersonDegreeRepository $personDegreeRepository;
	private CompanyRepository $companyRepository;
	private EntityManagerInterface $em;
	private TranslatorInterface $translator;

	public function __construct(
		EntityManagerInterface $em,
		SchoolService $schoolService,
		SerializerInterface $serializer,
		PersonDegreeRepository $personDegreeRepository,
		CompanyRepository $companyRepository,
		TranslatorInterface $translator
	) {
		$this->schoolService = $schoolService;
		$this->serializer = $serializer;
		$this->personDegreeRepository = $personDegreeRepository;
		$this->companyRepository = $companyRepository;
		$this->em = $em;
		$this->translator = $translator;
	}

	#[Route('/', name: 'client_school_update', methods: ['GET'])]
	public function updateSchoolServerAction(): Response {
		$school = $this->schoolService->getSchool();

		return $this->render('school/serverupdate.html.twig', array(
			'school' => $school,
		));
	}

	/**
	 * Check if each of the PersonDegrees exists and if the update date is higher than the local one
	 */
	#[Route('/checkDataToUpdate', name: 'client_check_data_to_update', methods: ['GET'])]
	public function checkDataToUpdate(): JsonResponse|Response {
		$authUser = $this->checkClientUser();
		if (!$authUser) {
			$schoolDatas = [];
			$responseData = [
				'type' => "flash",
				'color' => "alert-danger",
				'message' => 'Vous n\'êtes pas autorisé faire à cette opération de mise à jour'];
			$schoolDatas[] = $responseData;

			$serverResponse = $this->serializer->serialize($schoolDatas, 'json');
			return new Response($serverResponse);
		}

		$school = $this->schoolService->getSchool();

		// current school and all personDegrees and companies attached
		$datas = [];

		// write school information
		$data = [
			'type' => 'school',
			'phone' => $school->getPhoneStandard(),
			'updateDate' => $school->getUpdatedDate(),
			'createDate' => $school->getCreatedDate(),
			'city' => $school->getCity()->getId(),
		];
		$datas[] = $data;

		// write personDegrees information with their Satisfactions
		/** @var PersonDegree $personDegree */
		foreach ($school->getPersonDegrees() as $personDegree) {
			$lastSatisfactionUpdateDate = new \DateTime('2018-01-01');
			/** @var SatisfactionSalary $satisfaction */
			foreach ($personDegree->getSatisfactionSalaries() as $satisfaction) {
				if ($lastSatisfactionUpdateDate < $satisfaction->getUpdatedDate()) {
					$lastSatisfactionUpdateDate = $satisfaction->getUpdatedDate();
				}
			}
			foreach ($personDegree->getSatisfactionSearches() as $satisfaction) {
				if ($lastSatisfactionUpdateDate < $satisfaction->getUpdatedDate()) {
					$lastSatisfactionUpdateDate = $satisfaction->getUpdatedDate();
				}
			}
			foreach ($personDegree->getSatisfactionCreators() as $satisfaction) {
				if ($lastSatisfactionUpdateDate < $satisfaction->getUpdatedDate()) {
					$lastSatisfactionUpdateDate = $satisfaction->getUpdatedDate();
				}
			}

			// create tables of all satisfactions created and updated dates
			$createdSalaries = [];
			$createdSearches = [];
			$createdCreators = [];
			$updatedSalaries = [];
			$updatedSearches = [];
			$updatedCreators = [];
			foreach ($personDegree->getSatisfactionSalaries() as $satisfaction) {
				$createdSalaries[] = $satisfaction->getCreatedDate();
				if ($satisfaction->getUpdatedDate()) {
					$updatedSalaries[] = $satisfaction->getUpdatedDate();
				} else {
					$updatedSalaries[] = $satisfaction->getCreatedDate();
				}
			}
			foreach ($personDegree->getSatisfactionSearches() as $satisfaction) {
				$createdSearches[] = $satisfaction->getCreatedDate();
				if ($satisfaction->getUpdatedDate()) {
					$updatedSearches[] = $satisfaction->getUpdatedDate();
				} else {
					$updatedSearches[] = $satisfaction->getCreatedDate();
				}
			}
			foreach ($personDegree->getSatisfactionCreators() as $satisfaction) {
				$createdCreators[] = $satisfaction->getCreatedDate();
				if ($satisfaction->getUpdatedDate()) {
					$updatedCreators[] = $satisfaction->getUpdatedDate();
				} else {
					$updatedCreators[] = $satisfaction->getCreatedDate();
				}
			}

			// create data to send to server
			$data = ['type' => 'personDegree',
				'phone' => $personDegree->getPhoneMobile1(),
				'firstname' => $personDegree->getFirstname(),
				'lastname' => $personDegree->getLastname(),
				'createDate' => $personDegree->getCreatedDate(),
				'updateDate' => $personDegree->getUpdatedDate(),
				'serverUserId' => -1,
				'lastSatisfactionUpdateDate' => $lastSatisfactionUpdateDate,
				'createdSalaries' => $createdSalaries,
				'updatedSalaries' => $updatedSalaries,
				'createdSearches' => $createdSearches,
				'updatedSearches' => $updatedSearches,
				'createdCreators' => $createdCreators,
				'updatedCreators' => $updatedCreators,
			];
			$datas[] = $data;
		}

		// write companies information with their Satisfactions
		foreach ($school->getCompanies() as $company) {
			// find the last modified Satisfaction
			$lastSatisfactionUpdateDate = new \DateTime('2018-01-01');
			$companyUpdateDate = $company->getUpdatedDate();
			if (!$companyUpdateDate) $companyUpdateDate = $company->getCreatedDate();

			foreach ($company->getSatisfactionCompanies() as $satisfaction) {
				if ($lastSatisfactionUpdateDate < $satisfaction->getUpdatedDate()) {
					$lastSatisfactionUpdateDate = $satisfaction->getUpdatedDate();
				}
			}

			// create tables of all satisfactions created and updated dates
			$createdSatisfactions = [];
			$updatedSatisfactions = [];
			foreach ($company->getSatisfactionCompanies() as $satisfaction) {
				$createdSatisfactions[] = $satisfaction->getCreatedDate();
				if ($satisfaction->getUpdatedDate()) {
					$updatedSatisfactions[] = $satisfaction->getUpdatedDate();
				} else {
					$updatedSatisfactions[] = $satisfaction->getCreatedDate();
				}
			}

			// create data to send to server
			$data = [
				'type' => 'company',
				'phone' => $company->getPhoneStandard(),
				'updateDate' => $companyUpdateDate,
				'serverUserId' => -1,
				'lastSatisfactionUpdateDate' => $lastSatisfactionUpdateDate,
				'createdSatisfactions' => $createdSatisfactions,
				'updatedSatisfactions' => $updatedSatisfactions,
			];
			$datas[] = $data;
		}

		// Envoi des données par Curl, Guzzle ou HttpClient
		$url = $this->getParameter('inserjeune_path') . '/serverCheckDataToUpdate';
		$method = "POST";
		$timeout = 0;
		$headers = array(
			"Content-Type: application/json",
			"apikey: " . $this->getUser()->getApiToken()
		);

		$jsonData = $this->serializer->serialize($datas, 'json');
		$serverResponse = $this->callUpdateServer($url, $method, $timeout, $headers, $jsonData);

		// Analyse of server response before return to view */
		// Check to remove or update personDegree who are no longer attached to the school
		try {
			$schoolDatas = $this->serializer->deserialize($serverResponse, 'array', 'json');

			for ($i = 0; $i < count($schoolDatas); $i++) {
				if (($schoolDatas[$i]['type'] == "personDegree") || ($schoolDatas[$i]['type'] == 'company')) {
					$clientPersonDegree = $this->personDegreeRepository->findOneByPhoneMobile1($schoolDatas[$i]['phone']);
					$serverPersonDegreeUpdateDate = new \DateTime($schoolDatas[$i]["personDegreeUpdate"]);

					if ($schoolDatas[$i]["personDegree"] == "vérifier") {

						//check if personDegree is attached on the same school on server
						if ($schoolDatas[$i]["personSchoolPhone"] == $school->getPhoneStandard()) {

							// check if personDegree has changed school
							if ($clientPersonDegree) {
								if ($clientPersonDegree->getUpdatedDate() > $serverPersonDegreeUpdateDate) {
									$schoolDatas[$i]["personDegree"] = 'mettre à jour serveur';
								} else {
									$schoolDatas[$i]["personDegree"] = 'mettre à jour client';
								}

							} else {
								$schoolDatas[$i]['personDegree'] = "A supprimer par le diplômé sur" . "<br>" . "inserjeune.francophonie.org";
							}

							// check if personDegree on server is newer than on client
						} else if ($clientPersonDegree->getUpdatedDate() < $serverPersonDegreeUpdateDate) {
							$schoolDatas[$i]["personDegree"] = 'mettre à jour client';

							//remove data, personDegree is not attached on the same school on server
						} else {
							array_splice($schoolDatas, $i, 1);
							$i--;
						}
					}
				}
			}
			$serverResponse = $this->serializer->serialize($schoolDatas, 'json');

		} catch (\Exception $e) {
			$schoolDatas = [];
			$responseData = [
				'type' => "flash",
				'color' => "alert-danger",
				'message' => "Les données de l'établissement ne sont pas accessibles sur le serveur "];
			$schoolDatas[] = $responseData;
			$serverResponse = $this->serializer->serialize($schoolDatas, 'json');
		}

		return new Response($serverResponse);
	}

	/**
	 * Update PersonDegrees from RaspBerry to Server
	 */
	#[Route('/clientDataUpdate', name: 'client_data_update', methods: ['POST'])]
	public function clientDataUpdate(Request $request): JsonResponse|Response {
		// check rights user
		$authUser = $this->checkClientUser();
		if (!$authUser) return new Response('Vous n\'êtes pas autorisé faire à cette opération de mise à jour');

		// unserialize phone list of PersonDegrees
		$jsonData = $request->getContent();
		$dataList = $this->serializer->deserialize($jsonData, 'array', 'json');
		$responses = [];

		// Find PersonDegrees from local database
		$personDegrees = [];
		foreach ($dataList as $idPersonDegree) {
			$personDegree = $this->personDegreeRepository->findOneByPhoneMobile1($idPersonDegree);
			if ($personDegree)
				$personDegrees[] = $personDegree;
		}

		// send to server each PersonDegrees to compare and/or update
		foreach ($personDegrees as $personDegree) {
			// Send datas by Curl, Guzzle or HttpClient
			$url = $this->getParameter('inserjeune_path') . '/serverPersonDegreeUpdate';
			$method = "POST";
			$timeout = 25;
			$headers = array(
				"Content-Type: application/json",
				"apikey: " . $this->getUser()->getApiToken()
			);
			// call server to update the PersonDegree
			$jsonData = $this->serializer->serialize($personDegree, 'json');
			$serverResponse = $this->callUpdateServer($url, $method, $timeout, $headers, $jsonData);

			// create a collection of Result of updated PersonDegrees
			$responses[] = $serverResponse;
		}

		// Find Companies from local database
		$companies = [];
		foreach ($dataList as $phoneCompany) {
			$company = $this->companyRepository->findOneByPhoneStandard($phoneCompany);
			if ($company)
				$companies[] = $company;
		}

		// send to server each Company to compare and/or update
		foreach ($companies as $company) {
			// Send datas by Curl, Guzzle or HttpClient
			$url = $this->getParameter('inserjeune_path') . '/serverCompanyUpdate';
			$method = 'POST';
			$timeout = 25;
			$headers = array(
				"Content-Type: application/json",
				"apikey: " . $this->getUser()->getApiToken()
			);
			// call server to update the PersonDegree
			$jsonData = $this->serializer->serialize($company, 'json');
			$serverResponse = $this->callUpdateServer($url, $method, $timeout, $headers, $jsonData);

			// create a collection of Result of updated PersonDegrees
			$responses[] = $serverResponse;
		}

		// call server to update current school profile
		$school = $this->getUser()->getSchool();

		// initialize ClientUpdateDate
		$school->setClientUpdateDate(new \DateTime());
		$this->em->persist($school);
		$this->em->flush();

		// Send datas by Curl, Guzzle or HttpClient
		$url = $this->getParameter('inserjeune_path') . '/serverSchoolUpdate';
		$method = "POST";
		$timeout = 50;
		$headers = array(
			"Content-Type: application/json",
			"apikey: " . $this->getUser()->getApiToken()
		);

		// call server to update the PersonDegree
		$jsonData = $this->serializer->serialize($school, 'json');
		$serverResponse = $this->callUpdateServer($url, $method, $timeout, $headers, $jsonData);

		// create a collection of Result of updated PersonDegrees
		$responses[] = $serverResponse;

		// return all results to view (called by Ajax post mode)
		$jsonresponse = $this->serializer->serialize($responses, 'json');
		return new JsonResponse($jsonresponse);
	}

	/**
	 * Check if request user has apikey and is a school
	 * @return boolean
	 */
	public function checkClientUser(): bool {
		$apiKey = $this->getUser()->getApiToken();

		if (!$apiKey) {
			return false;
		}

		if (!$this->getUser()->getSchool()) {
			return false;
		}
		return true;
	}

	/**
	 * Call Server with Curl
	 * @param string $url
	 * @param string $method
	 * @param int $timeout
	 * @param array $headers
	 * @param array $data
	 * @return Response
	 */
	public function callUpdateServer(
		string $url,
		string $method,
		int    $timeout,
		array  $headers,
		array  $data): Response {
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => $timeout,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_HTTPHEADER => $headers
		));

		$result = curl_exec($curl);
		curl_close($curl);

		return $result;
	}
}
