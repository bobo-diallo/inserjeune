<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\SatisfactionCompany;
use App\Entity\School;
use App\Entity\PersonDegree;
use App\Entity\SatisfactionCreator;
use App\Entity\SatisfactionSalary;
use App\Entity\SatisfactionSearch;
use App\Repository\CityRepository;
use App\Repository\CompanyRepository;
use App\Repository\CountryRepository;
use App\Repository\PersonDegreeRepository;
use App\Repository\RoleRepository;
use App\Repository\SchoolRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use App\Entity\User;

#[Route(path: '/api')]
class ServerUpdateDataController extends AbstractController {

	private SerializerInterface $serializer;
	private EntityManagerInterface $em;
	private SchoolRepository $schoolRepository;
	private CityRepository $cityRepository;
	private CompanyRepository $companyRepository;
	private PersonDegreeRepository $personDegreeRepository;
	private CountryRepository $countryRepository;
	private RoleRepository $roleRepository;
	private UserRepository $userRepository;

	public function __construct(
		EntityManagerInterface $em,
		SerializerInterface    $serializer,
		SchoolRepository       $schoolRepository,
		CityRepository         $cityRepository,
		CompanyRepository      $companyRepository,
		PersonDegreeRepository $personDegreeRepository,
		CountryRepository      $countryRepository,
		RoleRepository         $roleRepository,
		UserRepository $userRepository
	) {
		$this->serializer = $serializer;
		$this->em = $em;
		$this->schoolRepository = $schoolRepository;
		$this->cityRepository = $cityRepository;
		$this->companyRepository = $companyRepository;
		$this->personDegreeRepository = $personDegreeRepository;
		$this->countryRepository = $countryRepository;
		$this->roleRepository = $roleRepository;
		$this->userRepository = $userRepository;
	}

	/**
	 * Check if PersonDegrees and Companies exist in central database and if updateDate is more recent than the locale
	 */
	#[Route(path: '/serverCheckDataToUpdate', name: 'server_check_data_to_update', methods: ['GET', 'POST'])]
	public function checkDataToUpdate(Request $request): JsonResponse|Response {

		$authUser = $this->checkUser($request);
		if (!$authUser) return new Response('Vous n\'êtes pas autorisé faire à cette opération de mise à jour');

		$datasClient = $this->serializer->deserialize($request->getContent(), 'array', 'json');

		$responseDatas = [];

		$schoolPhone = "";
		$clientSchoolUpdateDate = null;
		$serverSchool = null;

		// recover current school on server
		foreach ($datasClient as $data) {
			if ($data['type'] == "school") {
				$clientSchoolUpdateDate = new \DateTime($data['updateDate']);

				$serverSchool = $this->schoolRepository->findOneByPhoneStandard($data['phone']);

				if (!$serverSchool) {
					try {
						$newCity = $this->cityRepository->findOneById($data['city']);
						$newDate = new \DateTime($data['createDate']);
						$schools = $this->schoolRepository->getByCityAndCreatedDate($newCity, $newDate);
						if (count($schools) > 0) {
							$serverSchool = $schools[0];
							$schoolStatus = "mettre à jour serveur";
							$responseData = [
								'type' => "school",
								'schoolStatus' => $schoolStatus,
							];
							$responseDatas[] = $responseData;
						}
					} catch (\Exception $e) {
					}
				}

				if ($serverSchool) {
					$schoolPhone = $serverSchool->getPhoneStandard();
				}
			}
		}

		if (!$serverSchool) {
			$responseData = [
				'type' => "school",
				'schoolStatus' => "mettre à jour serveur",
			];
			$responseDatas[] = $responseData;
			return new JsonResponse(" L'établissement n'a pas été trouvé sur le serveur");

		} else {
			// check if client School is newer
			$schoolStatus = "Serveur à jour";
			$clientSchoolUpdateDateStr = $clientSchoolUpdateDate->format('Y-m-d H:i:s');
			$serverSchoolUpdateDateStr = $serverSchool->getUpdatedDate()->format('Y-m-d H:i:s');

			if ($serverSchoolUpdateDateStr != $clientSchoolUpdateDateStr) {
				if ($clientSchoolUpdateDate > $serverSchool->getUpdatedDate()) {
					$schoolStatus = "mettre à jour serveur";
				} else {
					$schoolStatus = "Demandez la mise à jour du client";
				}
			}
			$responseData = [
				'type' => "school",
				'schoolStatus' => $schoolStatus,
			];
			$responseDatas[] = $responseData;

			// recover companies and update or create them on server */
			foreach ($datasClient as $data) {
				// initialize datas to send to client
				$serverCompany = null;
				$serverUserName = 'inconnu';
				$phone = $data['phone'];
				$serverUserId = -1;
				$serverCompanyStatus = "mettre à jour serveur";
				$serverSatisfactionStatus = "";

				// import Datas from server
				if ($data['type'] == "company") {
					$serverCompany = $this->companyRepository->findOneByPhoneStandard($data['phone']);

					// find company on server and define it status
					if ($serverCompany) {
						$serverCompanyStatus = "";
						if ($serverCompany->getUser())
							$serverUserId = $serverCompany->getUser()->getId();
						$serverUserName = $serverCompany->getName();

						// compare datas to update or create
						$c = new Company();
						$c->getCreatedDate();
						$serverCompanyUpdateDate = $serverCompany->getCreatedDate();
						if ($serverCompany->getUpdatedDate())
							$serverCompanyUpdateDate = $serverCompany->getUpdatedDate();

						$clientCompanyUpdateDate = new \DateTime($data['updateDate']);

						// if client and server Company have same updateDate
						if ($serverCompanyUpdateDate->format('Y-m-d H:i:s') == $clientCompanyUpdateDate->format('Y-m-d H:i:s')) {
							$serverCompanyStatus = "serveur à jour";

							// if client Company is newer
						} else if ($serverCompanyUpdateDate < $clientCompanyUpdateDate) {
							$serverCompanyStatus = "mettre à jour serveur";

							// if server Company is newer
						} else if ($serverCompanyUpdateDate > $clientCompanyUpdateDate) {
							$serverCompanyStatus = "mettre à jour client";
						}

						// check satisfactions of company and define their status
						$serverSatisfactions = $serverCompany->getSatisfactionCompanies();

						$clientCreateDates = $data['createdSatisfactions'];
						$clientUpdateDates = $data['updatedSatisfactions'];

						// check if satisfaction exist in both sides (server & client)
						for ($i = 0; $i < count($clientCreateDates); $i++) {

							$satisfactionExist = false;
							$clientCreateDate = new \DateTime($clientCreateDates[$i]);
							$clientUpdateDate = new \DateTime($clientUpdateDates[$i]);

							if (!$clientUpdateDate)
								$clientUpdateDate = $clientCreateDate;

							foreach ($serverSatisfactions as $serverSatisfaction) {
								$serverCreateDate = $serverSatisfaction->getCreatedDate();

								if ($clientCreateDate->format('Y-m-d H:i:s') == $serverCreateDate->format('Y-m-d H:i:s')) {
									$satisfactionExist = true;
									break;
								}
							}

							/* if satisfaction exist in both sides */
							if ($satisfactionExist) {
								foreach ($serverSatisfactions as $serverSatisfaction) {
									$serverUpdateDate = $serverSatisfaction->getUpdatedDate();

									if (!$serverUpdateDate)
										$serverUpdateDate = $serverSatisfaction->getCreatedDate();

									// if client satisfaction is newer
									if ($serverUpdateDate->format('Y-m-d H:i:s') == $clientUpdateDate->format('Y-m-d H:i:s')) {
										$serverSatisfactionStatus = 'serveur à jour';

										// if client satisfaction is newer
									} else if ($serverUpdateDate < $clientUpdateDate) {
										$serverSatisfactionStatus = 'mettre à jour serveur';

										// if server satisfaction is newer
									} else if ($serverUpdateDate > $clientUpdateDate) {
										$serverSatisfactionStatus = 'mettre à jour client';
									}
								}
								// if satisfaction with last created date exist only on client
							} else {
								$serverSatisfactionStatus = 'mettre à jour serveur';
							}

							// if satisfaction exist only on client
							if (count($serverSatisfactions) == 0)
								$serverSatisfactionStatus = 'mettre à jour serveur';
						}

						/* if satisfaction exist only on server */
						if ((count($clientCreateDates) == 0) && (count($serverSatisfactions) > 0)) {
							$serverSatisfactionStatus = 'mettre à jour client';
						}
					}

					// make responseData to Client
					$responseData = [
						'type' => "company",
						'serverUserName' => $serverUserName,
						'serverUserId' => $serverUserId,
						'phone' => $phone,
						'schoolPhone' => $schoolPhone,
						'personDegree' => $serverCompanyStatus,
						'satisfaction' => $serverSatisfactionStatus
					];
					$responseDatas[] = $responseData;
				}
			}

			// recover personDegrees and update or create them on server
			foreach ($datasClient as $data) {
				// initialize datas to send to client
				$serverPersonDegree = null;
				$serverPersonDegreeSchoolPhone = $schoolPhone;
				$serverUserName = 'inconnu';
				$phone = $data['phone'];
				$serverUserId = -1;
				$serverPersonDegreeStatus = "";
				$serverSatisfactionStatus = "";

				// import Datas from server
				if ($data['type'] == "personDegree") {
					// Datas init
					$createdate = new \DateTime($data['createDate']);
					$serverPersonDegreeUpdateDate = new \DateTime('2018-01-01');
					$serverPersonDegree = $this->personDegreeRepository->findOneByPhoneMobile1($data['phone']);

					// if personDegree phone is modified, search by Name, birthDate and createdDate
					if (!$serverPersonDegree) {
						$serverPersonDegrees = $this->personDegreeRepository->getByFirstnameAndLastameAndCreatedDate(
							$data['firstname'],
							$data['lastname'], $createdate
						);
						if (count($serverPersonDegrees) > 0) {
							$serverPersonDegree = $serverPersonDegrees[0];
						}
					}

					// find person degree on server and define it status
					if ($serverPersonDegree) {
						if ($serverPersonDegree->getUser())
							$serverUserId = $serverPersonDegree->getUser()->getId();
						$serverUserName = $serverPersonDegree->getName();

						// compare datas to update or create
						$serverPersonDegreeUpdateDate = $serverPersonDegree->getUpdatedDate();
						$clientPersonDegreeUpdateDate = new \DateTime($data['updateDate']);

						// if server and client PersonDegree have same updateDate
						if ($serverPersonDegreeUpdateDate->format('Y-m-d H:i:s') == $clientPersonDegreeUpdateDate->format('Y-m-d H:i:s')) {
							$serverPersonDegreeStatus = "serveur à jour";

							// if client PersonDegree is newer
						} else if ($serverPersonDegreeUpdateDate < $clientPersonDegreeUpdateDate) {
							$serverPersonDegreeStatus = "mettre à jour serveur";

							// if server PersonDegree is newer
						} else if ($serverPersonDegreeUpdateDate > $clientPersonDegreeUpdateDate) {
							$serverPersonDegreeStatus = "mettre à jour client";
						}

						// if personDegree between client and server have different schools
						if ($serverPersonDegree->getSchool()->getPhoneStandard() != $schoolPhone) {
							$serverPersonDegreeStatus = "vérifier l'établissement";
						}

						// check satisfactions of person degree and define their status
						// find last clientSatisfaction with clientLastSatisfactionUpdateDate
						$clientUpdateDate = null;
						$clientCreateDate = null;
						$serverSatisfactionExist = "";
						$serverOK = false;
						$updateServer = false;
						$updateClient = false;

						foreach (["Salaries", "Searches", "Creators"] as $typeSatisfaction) {
							$clientSatisfactionUpdates = $data['updated' . $typeSatisfaction];

							foreach ($clientSatisfactionUpdates as $key => $clientSatisfactionUpdated) {
								$clientUpdateDate = new \DateTime($clientSatisfactionUpdated);
								$serverSatisfaction = null;

								$clientSatisfactionCreates = $data['created' . $typeSatisfaction];
								$clientCreateDate = new \DateTime($clientSatisfactionCreates[$key]);

								// calculate the minimum serverCreateDate : 6 mont before client updateDate
								$minimunServerCreatedDate = new \DateTime($clientSatisfactionUpdated);
								try {
									$dateInterval = new \DateInterval('P6M');
									$dateInterval->invert = 1; //negative interval
									$minimunServerCreatedDate->add($dateInterval);
								} catch (\Exception $e) {
								}

								// compare with same type of server satisfactions
								$getSatisfaction = "getSatisfaction" . $typeSatisfaction;
								$serverSatisfactions = $serverPersonDegree->$getSatisfaction();

								// memorize if serverSatisfactionExist
								if (count($serverSatisfactions) > 0) {
									$serverSatisfactionExist = $typeSatisfaction;
								}

								foreach ($serverSatisfactions as $satisfaction) {
									$serverCreateDate = $satisfaction->getCreatedDate();
									$serverUpdateDate = $satisfaction->getUpdatedDate();

									// if serverSatisfaction and clientSatisfaction have same createdDate
									if ($serverCreateDate->format('Y-m-d H:i:s') == $clientCreateDate->format('Y-m-d H:i:s')) {
										if ($serverUpdateDate->format('Y-m-d H:i:s') == $clientUpdateDate->format('Y-m-d H:i:s')) {
											$serverOK = true;
										} else if ($serverUpdateDate < $clientUpdateDate) {
											$updateServer = true;
										}
									} // else, if serverSatisfaction and clientSatisfaction have less than 6 months difference
									else if (($serverCreateDate > $minimunServerCreatedDate) && ($serverCreateDate < $clientCreateDate)) {
										$updateServer = true;
									} // else, if serverSatisfaction and clientSatisfaction have more than 6 months difference
									else if ($serverCreateDate < $minimunServerCreatedDate) {
										$updateServer = true;
									} // else, if server createDate is older and serveur updateDate is newer
									else if (($serverCreateDate > $clientCreateDate) &&
										($serverUpdateDate < $clientUpdateDate)) {
										$updateServer = true;
									}

									// serverSatisfaction newer than clientSatisfaction
									if ($serverUpdateDate > $clientUpdateDate) {
										$updateClient = true;
									}
								}
							}
						}

						// update status with results ($serverOK, $updateServer and $updateClient)
						if ($serverSatisfactionStatus != 'mettre à jour serveur') {
							if ($updateServer) {
								$serverSatisfactionStatus = 'mettre à jour serveur';
							} else if ($updateClient) {
								$serverSatisfactionStatus = 'mettre à jour client';
							} else if ($serverOK) {
								$serverSatisfactionStatus = 'serveur à jour';
							}
						}

						// case where satisfaction exist only on server
						if ($clientCreateDate == null) {
							// calculate the minimum serverCreateDate : 6 mont before today
							$minimunServerCreatedDate = new \DateTime();
							try {
								$dateInterval = new \DateInterval('P6M');
								$dateInterval->invert = 1; //negative interval
								$minimunServerCreatedDate->add($dateInterval);
							} catch (\Exception $e) {
							}

							$lastSatisfactionCreateDate = new \DateTime('2018-01-01');
							$typeSatisfaction = null;
							foreach (["Salaries", "Searches", "Creators"] as $typeSatisfaction) {
								$getSatisfaction = 'getSatisfaction' . $typeSatisfaction;
								foreach ($serverPersonDegree->$getSatisfaction() as $satisfaction) {
									if ($lastSatisfactionCreateDate < $satisfaction->getUpdatedDate()) {
										$lastSatisfactionCreateDate = $satisfaction->getUpdatedDate();
									}
								}
							}

							if ($lastSatisfactionCreateDate > $minimunServerCreatedDate) {
								$serverSatisfactionStatus = 'mettre à jour client';
							}
						} // cas where no satisfaction on server
						else if ($serverSatisfactionExist == false) {
							$serverSatisfactionStatus = 'mettre à jour serveur';
						}


						// if no personDegree found with phoneMobile1 or Firstname, Lastname and CreatedDate
					} else {
						$serverPersonDegree = new PersonDegree();
						$serverPersonDegree->setSchool($serverSchool);
						$serverPersonDegreeStatus = "mettre à jour serveur";
						$serverSatisfactionStatus = "";
						$serverUserName = $data['lastname'] . " " . $data['firstname'];
					}

					// make responseData to Client
					$responseData = [
						'type' => "personDegree",
						'serverUserName' => $serverUserName,
						'serverUserId' => $serverUserId,
						'phone' => $phone,
						'schoolPhone' => $serverPersonDegreeSchoolPhone,
						'personSchoolPhone' => $serverPersonDegree->getSchool()->getPhoneStandard(),
						'personDegree' => $serverPersonDegreeStatus,
						'personDegreeUpdate' => $serverPersonDegreeUpdateDate->format("Y-m-d H:i:s"),
						'satisfaction' => $serverSatisfactionStatus];
					$responseDatas[] = $responseData;
				}
			}

			// check datas to be removed  on server
			foreach ($serverSchool->getPersonDegrees() as $serverPersonDegree) {
				$serverUserName = $serverPersonDegree->getName();
				$serverPhone = $serverPersonDegree->getPhoneMobile1();
				$serverPersonDegreeUpdate = $serverPersonDegree->getUpdatedDate();
				$serverPersonDegreeStatus = "vérifier";

				// check if personDegree exist on client
				foreach ($datasClient as $data) {
					// check by phoneMobile1
					if ($data['phone'] == $serverPhone) {
						$serverPersonDegreeStatus = "";
					}
					// check by Name and createdDate
					if ($serverPersonDegreeStatus == "vérifier") {
						foreach ($datasClient as $data) {
							// import Datas from server
							if ($data['type'] == "personDegree") {
								$createdate = new \DateTime($data['createDate']);
								if (($serverPersonDegree->getFirstname() == $data['firstname']) &&
									($serverPersonDegree->getLastname() == $data['lastname']) &&
									($serverPersonDegree->getCreatedDate() == $createdate))
									$serverPersonDegreeStatus = "";
							}
						}
					}
				}

				// make responseData to Client
				if ($serverPersonDegreeStatus == "vérifier") {
					// make responseData to Client
					$responseData = [
						'type' => "personDegree",
						'serverUserName' => $serverUserName,
						'serverUserId' => "-1",
						'phone' => $serverPhone,
						'schoolPhone' => $schoolPhone,
						'personSchoolPhone' => $serverPersonDegree->getSchool()->getPhoneStandard(),
						'personDegree' => $serverPersonDegreeStatus,
						'personDegreeUpdate' => $serverPersonDegreeUpdate->format('Y-m-d H:i:s'),
						'satisfaction' => ""];
					$responseDatas[] = $responseData;
				}
			}
			return new JsonResponse($responseDatas);
		}
	}

	/**
	 * Update School from RaspBerry
	 */
	#[Route(path: '/serverSchoolUpdate', name: 'server_school_update', methods: ['GET', 'POST'])]
	public function serverSchoolUpdateAction(Request $request): JsonResponse|Response {
		$authUser = $this->checkUser($request);
		if (!$authUser) return new Response('Vous n\'êtes pas autorisé faire à cette opération de mise à jour');
		$responseDataUpdate = "";

		// Unserialize School requested
		$clientSchool = $this->serializer->deserialize($request->getContent(), School::class, 'json');

		// Search if $newSchool already exist in central database
		$serverSchool = $this->schoolRepository->findOneByPhoneStandard($clientSchool->getPhoneStandard());

		/* If phone's number is changed, find School on server with it's name and it's city */
		if (!$serverSchool) {
			$serverSchools = $this->schoolRepository->getByCityAndCreatedDate($clientSchool->getCity(), $clientSchool->getCreatedDate());

			if (count($serverSchools) > 1) {
				return new Response("Erreur, " . count($serverSchools) . " Entreprises " . $clientSchool->getFirstname() . " "
					. $clientSchool->getName()
					. " le numéro de téléphone " . $clientSchool->getPhoneStandard() . " est introubable");
			} elseif (count($serverSchools) == 1) {
				$serverSchool = $serverSchools[0];
			}
		}

		/* Update User, School with associated Satisfactions*/
		if (!$serverSchool) {
			// create new user
			$serverUser = $this->createUser($clientSchool->getUser(), "ROLE_ETABLISSEMENT");
			$this->em->persist($serverUser);

			// create School
			$serverSchool = new School();
			$serverSchool->setUser($serverUser);
			$responseDataUpdate .= " create new School ...";
		}

		// Check profil School updated date : update profil & user of School
		if (($serverSchool->getUpdatedDate() < $clientSchool->getUpdatedDate()) ||
			($serverSchool->getClientUpdateDate() < $clientSchool->getClientUpdateDate())) {
			// update user
			$responseDataUpdate .= " updating Current School User ...";
			$updateData = $this->updateUser($clientSchool->getUser(), $serverSchool->getUser());
			$serverUser = $updateData[0];
			$this->em->persist($serverUser);
			$responseDataUpdate .= $updateData[1];

			$responseDataUpdate .= " updating School ...";
			$updateData = $this->updateActor($clientSchool, $serverSchool, "School");
			$serverSchool = $updateData[0];
			$responseDataUpdate .= $updateData[1];

			//faire control erreur avant persist
			$this->em->persist($serverSchool);
			$this->em->flush();

		}

		return new Response(" Update id School= : " . $serverSchool->getId() . "| " . $responseDataUpdate);
	}

	/**
	 * Update PersonDegrees from RaspBerry
	 */
	#[Route(path: '/serverPersonDegreeUpdate', name: 'server_person_degree_update', methods: ['GET', 'POST'])]
	public function serverPersonDegreeUpdateAction(Request $request): JsonResponse|Response {
		$authUser = $this->checkUser($request);
		if (!$authUser) return new Response('Vous n\'êtes pas autorisé faire à cette opération de mise à jour');
		$responseDataUpdate = "";

		// Unserialize PersonDegree requested
		$clientPersonDegree = $this->serializer->deserialize($request->getContent(), PersonDegree::class, 'json');

		// Search if $newPersonDegree already exist in central database
		$serverPersonDegree = $this->personDegreeRepository->findOneByPhoneMobile1($clientPersonDegree->getPhoneMobile1());


		// If phone's number is changed, find PersonDegree on server with it's name and createdDate
		if (!$serverPersonDegree) {
			$serverPersonDegrees = $this->personDegreeRepository->getByFirstnameAndLastameAndCreatedDate(
				$clientPersonDegree->getFirstname(), $clientPersonDegree->getLastname(), $clientPersonDegree->getCreatedDate()
			);

			if (count($serverPersonDegrees) > 0) {
				$serverPersonDegree = $serverPersonDegrees[0];
			}
		}


		// Update User, PersonDegree with associated Satisfactions
		if (!$serverPersonDegree) {
			// create new user
			$serverUser = $this->createUser($clientPersonDegree->getUser(), "ROLE_DIPLOME");
			$this->em->persist($serverUser);

			// create personDegree
			$serverPersonDegree = new PersonDegree();
			$serverPersonDegree->setUser($serverUser);
		}

		// Check profil personDegree updated date : update profil & user of PersonDegree
		if ($serverPersonDegree->getUpdatedDate() < $clientPersonDegree->getUpdatedDate()) {
			$responseDataUpdate .= " updating Current School User ...";
			$updateData = $this->updateUser($clientPersonDegree->getUser(), $serverPersonDegree->getUser());
			$serverUser = $updateData[0];
			$this->em->persist($serverUser);
			$responseDataUpdate .= $updateData[1];

			$responseDataUpdate .= " updating PersonDegree ...";
			$updateData = $this->updateActor($clientPersonDegree, $serverPersonDegree, "PersonDegree");
			$serverPersonDegree = $updateData[0];
			$responseDataUpdate .= $updateData[1];

			// faire control erreur avant persist
			$this->em->persist($serverPersonDegree);
			$this->em->flush();
		}

		// Check Satisfactions Created ans Updated Dates
		// Care : if old satisfaction exist on server and not on client, it will keep (TBD)
		foreach (["getSatisfactionSalaries", "getSatisfactionSearches", "getSatisfactionCreators"] as $getSatisfaction) {
			foreach ($clientPersonDegree->$getSatisfaction() as $clientSatisfaction) {

				$serverSatisfaction = null;
				// find satisfaction on server with same createDate than client
				foreach ($serverPersonDegree->$getSatisfaction() as $satisfaction) {
					if ($clientSatisfaction->getCreatedDate()->format('Y-m-d H:i:s') == $satisfaction->getCreatedDate()->format('Y-m-d H:i:s')) {
						$serverSatisfaction = $satisfaction;
					}
				}

				// find satisfaction on server with older createDate than client and 6 month limit intervalDate with client updateDate
				if (!$serverSatisfaction) {
					// calculate the minimum serverCreateDate : 6 mont before client updateDate
					// ------------------------------------------------------------------------
					$minimunServerCreatedDate = new \DateTime($clientSatisfaction->getUpdatedDate()->format('Y-m-d'));
					try {
						$dateInterval = new \DateInterval('P6M');
						$dateInterval->invert = 1; //negative interval
						$minimunServerCreatedDate->add($dateInterval);
					} catch (\Exception $e) {
					}

					// search $serverSatisfaction in this interval
					// -------------------------------------------
					foreach ($serverPersonDegree->$getSatisfaction() as $satisfaction) {
						if ($satisfaction->getCreatedDate() > $minimunServerCreatedDate) {
							$serverSatisfaction = $satisfaction;
						}
					}
				}

				// update satisfaction
				if ($serverSatisfaction) {
					if ($serverSatisfaction->getUpdatedDate() < $clientSatisfaction->getUpdatedDate()) {
						$responseDataUpdate .= " updating Satisfaction ..." . $getSatisfaction . ":" . $clientSatisfaction->getId();
						$result = $this->updateSatisfactionGeneric([$clientSatisfaction, $serverSatisfaction], $serverPersonDegree, "personDegree");
						$serverSatisfaction = $result[0];
						$responseDataUpdate .= $result[1];
						$this->em->persist($serverSatisfaction);
						$this->em->flush();

						$responseDataUpdate .= " ->(" . $clientSatisfaction->getId() . "|" . $serverSatisfaction->getId() . ")";
					}

					// create Satisfaction
				} else {
					$responseDataUpdate .= " creating Satisfaction ..." . $getSatisfaction . ":" . $clientSatisfaction->getId();
					$serverSatisfaction = null;
					if ($getSatisfaction == "getSatisfactionSalaries") {
						$serverSatisfaction = new SatisfactionSalary();
					} else if ($getSatisfaction == "getSatisfactionSearches") {
						$serverSatisfaction = new SatisfactionSearch();
					} else if ($getSatisfaction == "getSatisfactionCreators") {
						$serverSatisfaction = new SatisfactionCreator();
					}

					$result = $this->updateSatisfactionGeneric([$clientSatisfaction, $serverSatisfaction], $serverPersonDegree, "personDegree");
					$serverSatisfaction = $result[0];
					$responseDataUpdate .= $result[1];
					$this->em->persist($serverSatisfaction);
					$this->em->flush();
				}
			}
		}

		return new Response(" Update id personDegree= : " . $serverPersonDegree->getId() . "| " . $responseDataUpdate);
	}

	/**
	 * Update Companies from RaspBerry
	 */
	#[Route(path: '/serverCompanyUpdate', name: 'server_company_update', methods: ['GET', 'POST'])]
	public function serverCompanyUpdateAction(Request $request): JsonResponse|Response {
		$authUser = $this->checkUser($request);
		if (!$authUser) return new Response('Vous n\'êtes pas autorisé faire à cette opération de mise à jour');
		$responseDataUpdate = "";


		// Unserialize Company requested
		$clientCompany = $this->get('jms_serializer')->deserialize($request->getContent(), Company::class, 'json');

		// Search if $newCompany already exist in central database
		$serverCompany = $this->companyRepository->findOneByPhoneStandard($clientCompany->getPhoneStandard());

		// If phone's number is changed, find Company on server with it's name and it's city
		if (!$serverCompany) {
			$serverCompanies = $this->companyRepository->getByCountryAndCreatedDate(
				$clientCompany->getCountry(), $clientCompany->getCreatedDate()
			);

			if (count($serverCompanies) > 0) {
				$clientCompany = $serverCompanies[0];
			}
		}

		/* Update User, Company with associated Satisfactions*/
		if (!$serverCompany) {

			// create new user
			$serverUser = $this->createUser($clientCompany->getUser(), "ROLE_ENTREPRISE");
			$this->em->persist($serverUser);

			// create company
			$serverCompany = new Company();
			$serverCompany->setUser($serverUser);
			$responseDataUpdate .= " create new Company ...";
		}

		// Check profil Company updated date : update profil & user of Company
		if ($serverCompany->getUpdatedDate() < $clientCompany->getUpdatedDate()) {
			$responseDataUpdate .= " updating Company ...";
			$updateData = $this->updateActor($clientCompany, $serverCompany, "Company");
			$serverCompany = $updateData[0];
			$responseDataUpdate .= $updateData[1];

			//faire control erreur avant persist
			$this->em->persist($serverCompany);
			$this->em->flush();
		}

		// Check Satisfactions Created and Updated Dates
		// Care : if old satisfaction exist on server and not on client, it will keep (TBD)
		foreach ($clientCompany->getSatisfactionCompanies() as $clientSatisfaction) {

			$serverSatisfaction = null;
			foreach ($serverCompany->getSatisfactionCompanies() as $satisfaction) {
				if ($clientSatisfaction->getCreatedDate()->format('Y-m-d H:i:s') == $satisfaction->getCreatedDate()->format('Y-m-d H:i:s')) {
					$serverSatisfaction = $satisfaction;
				}
			}

			// update satisfaction
			if ($serverSatisfaction) {
				if ($serverSatisfaction->getUpdatedDate() < $clientSatisfaction->getUpdatedDate()) {
					$responseDataUpdate .= " updating SatisfactionCompany ...:" . $clientSatisfaction->getId();
					$result = $this->updateSatisfactionGeneric([$clientSatisfaction, $serverSatisfaction], $serverCompany, "company");
					$serverSatisfaction = $result[0];
					$responseDataUpdate .= $result[1];
					$this->em->persist($serverSatisfaction);
					$this->em->flush();

					$responseDataUpdate .= " ->(" . $clientSatisfaction->getId() . "|" . $serverSatisfaction->getId() . ")";
				}

				// create Satisfaction
			} else {
				$responseDataUpdate .= " creating SatisfactionCompany ...:" . $clientSatisfaction->getId();
				$serverSatisfaction = new SatisfactionCompany();
				$result = $this->updateSatisfactionGeneric([$clientSatisfaction, $serverSatisfaction], $serverCompany, "company");
				$serverSatisfaction = $result[0];
				$responseDataUpdate .= $result[1];
				$this->em->persist($serverSatisfaction);
				$this->em->flush();
			}
		}

		return new Response(" Update id company= : " . $serverCompany->getId() . "| " . $responseDataUpdate);
	}

	/**
	 * Regenerate user on server based on imported local user
	 * @param User $clientUser
	 * @param string $roleStr
	 * @return User
	 * @throws
	 */
	public function createUser(User $clientUser, string $roleStr): User {
		$newUser = clone($clientUser);
		$country = $this->countryRepository->findOneById($clientUser->getCountry()->getId());
		$newUser->setCountry($country);

		$role = $this->roleRepository->findOneByRole($roleStr);
		$newUser->setRoles([]);
		$newUser->addProfil($role);

		return $newUser;
	}

	/**
	 * Regenerate user on server based on imported local user
	 * @param User $clientUser
	 * @param User $serverUser
	 * @return ArrayCollection
	 * @throws
	 */
	public function updateUser(User $clientUser, User $serverUser): ArrayCollection {
		$response = "";
		try {
			$actorClass = new \ReflectionClass($serverUser);
			$metadata = $this->em->getClassMetadata($actorClass->getName());
			$properties = $metadata->getFieldNames();

			/* Update singles properties of current class */
			foreach ($properties as $property) {
				if (($property != 'id') &&
					($property != 'roles')) {
					$result = $this->updateProperty($property, [$clientUser, $serverUser], $actorClass);
					$serverUser = $result[0];
					$response .= $result[1];
				}
			}
		} catch (\Exception $e) {
			$response .= $e->getMessage();
		}

		return new ArrayCollection([$serverUser, $response]);
	}


	/**
	 * Update personDegree, company or school on server based on imported local data
	 * Return an Array with updated personDegree, company or school and operations comments
	 * @param Company | PersonDegree | School
	 * @param Company | PersonDegree | School
	 * @param string $typeActor
	 * @return ArrayCollection
	 */
	public function updateActor($clientActor, $serverActor, string $typeActor): ArrayCollection {
		$response = "";
		try {
			$actorClass = new \ReflectionClass($serverActor);
			$metadata = $this->em->getClassMetadata($actorClass->getName());
			$properties = $metadata->getFieldNames();
			$associations = $metadata->getAssociationNames();

			/* Update singles properties of current class */
			foreach ($properties as $property) {
				if ($property != 'id') {
					$result = $this->updateProperty($property, [$clientActor, $serverActor], $actorClass);
					$serverActor = $result[0];
					$response .= $result[1];
				}
			}

			/* Update associations of current class */
			/* ------------------------------------ */
			foreach ($associations as $key => $value) {
				// recovery association object
				$asso = $metadata->getAssociationTargetClass($value);
				// recovery class of association object
				$assoClass = new \ReflectionClass($asso);

				// Create relations
				// ----------------
				if ($value != "user") {
					if (!str_contains($value, 'satisfaction')) {
						if ($metadata->isCollectionValuedAssociation($value)) {
							$result = $this->createMultiRelation($value, $assoClass, [$clientActor, $serverActor], $actorClass);
							$serverActor = $result[0];
							$response .= $result[1];

							// create all OneToOne & ManyToOne relations
						} else {
							$result = $this->createSingleRelation($value, $assoClass, [$clientActor, $serverActor], $actorClass);
							$serverActor = $result[0];
							$response .= $result[1];
						}
					}
				}
			}

		} catch (\Exception $e) {
			$response .= " Erreur update" . $typeActor . " Id=" . $clientActor->getId();
		}

		return new ArrayCollection([$serverActor, $response]);
	}

	/**
	 * Regenerate satisfaction on server based on imported local personDegree
	 * Return an ArrayCollection with newSatisfaction and comments about results
	 */
	public function updateSatisfactionGeneric(ArrayCollection $satisfactions, $parent = null, string $type = null): ArrayCollection {
		$response = "";
		$clientSatisfaction = $satisfactions[0];
		$serverSatisfaction = $satisfactions[1];

		try {
			$satisfactionClass = new \ReflectionClass($clientSatisfaction);

			$metadata = $this->em->getClassMetadata($satisfactionClass->getName());
			$properties = $metadata->getFieldNames();
			$associations = $metadata->getAssociationNames();

			/* Update singles properties of current class */
			foreach ($properties as $property) {
				if ($property != 'id') {
					$result = $this->updateProperty($property, [$clientSatisfaction, $serverSatisfaction], $satisfactionClass);
					$serverSatisfaction = $result[0];
					$response .= $result[1];
				}
			}

			/* Update all relations in central Database */
			foreach ($associations as $key => $value) {
				// recovery association object
				$asso = $metadata->getAssociationTargetClass($value);

				// recovery class of association object
				$assoClass = new \ReflectionClass($asso);

				// update all ManyToMany relations
				if ($metadata->isCollectionValuedAssociation($value)) {
					$result = $this->createMultiRelation($value, $assoClass, [$clientSatisfaction, $serverSatisfaction], $satisfactionClass);
					$serverSatisfaction = $result[0];
					$response .= $result[1];

					// update all OneToOne & ManyToOne relations
				} else if ($value != "personDegree") {
					$result = $this->createSingleRelation($value, $assoClass, [$clientSatisfaction, $serverSatisfaction], $satisfactionClass);
					$serverSatisfaction = $result[0];
					$response .= $result[1];
				}
			}
		} catch (\Exception $e) {
			$response = "Erreur UpdateSatisfaction=" . $clientSatisfaction->getId();
		}

		if ($parent) {
			if ($type == "company") {
				$serverSatisfaction->setCompany($parent);
				$response .= " Parent Type = Company";
			} else if ($type == "personDegree") {
				$serverSatisfaction->setPersonDegree($parent);
				$response .= " Parent Type = PersonDegree";
			}
		}

		return new ArrayCollection([$serverSatisfaction, $response]);
	}

	public function updateProperty(string $property, $objects, \ReflectionClass $objectClass): ArrayCollection {
		$clientObject = $objects[0];
		$serverObject = $objects[1];
		$response = "";

		$getPropertyMethod = $this->checkGetMethod($property, $objectClass);
		$setPropertyMethod = $this->checkSetMethod($property, $objectClass);

		if ($getPropertyMethod && $setPropertyMethod) {
			$typeofField = $this->em->getClassMetadata($objectClass->getName())->getTypeOfField($property);

			if ($typeofField != "datetime") {
				$serverObject->$setPropertyMethod($clientObject->$getPropertyMethod());

				// Chapter for Exception on converted date Ex: Person.php -> birthDate or when Date is unset
			} else {
				if (!is_null($clientObject->$getPropertyMethod())) {

					// when date is string like Person.php -> birthDate
					if (is_string($clientObject->$getPropertyMethod())) {
						$dateStr = str_replace('-', '/', $clientObject->$getPropertyMethod());
						$serverObject->$setPropertyMethod($dateStr);

						// when date is dateTime
					} else if (is_a($clientObject->$getPropertyMethod(), 'DateTime')) {
						$serverObject->$setPropertyMethod($clientObject->$getPropertyMethod());
					}

				} else {
				}
			}
		}
		return new ArrayCollection([$serverObject, $response]);
	}

	public function createSingleRelation(string $association, \ReflectionClass $assoClass, $objects, \ReflectionClass $objectClass): ArrayCollection {
		$clientObject = $objects[0];
		$serverObject = $objects[1];
		$response = "";

		// check if getters & setters exists in Object class
		$getValueMethod = $this->checkGetMethod($association, $objectClass);
		$setValueMethod = $this->checkSetMethod($association, $objectClass);

		if ($getValueMethod != null && $setValueMethod != null) {
			$relation = $this->em->getRepository($assoClass->getName())->findOneById($clientObject->$getValueMethod());
			$serverObject->$setValueMethod($relation);
			$response .= " One: " . $association . " OK " . $relation;
		} else {
			$response .= " One: " . $association . " NOK ";
		}
		return new ArrayCollection([$serverObject, $response]);
	}

	public function createMultiRelation(string $association, \ReflectionClass $assoClass, $objects, \ReflectionClass $objectClass): ArrayCollection {
		$clientObject = $objects[0];
		$serverObject = $objects[1];
		$response = "";

		// check if getters & setters exists in Object class (Attention aux valeurs particulières !!)
		$getValuesMethod = $this->checkGetMethod($association, $objectClass);
		$addValueMethod = $this->checkAddMethod($association, $assoClass, $objectClass);
		$removeValueMethod = $this->checkRemoveMethod($association, $assoClass, $objectClass);

		if ((strpos($getValuesMethod, "erreur") == -1) || (strpos($addValueMethod, "erreur") == -1) || (strpos($removeValueMethod, "erreur") == -1)) {
			$response .= " Erreur de méthode dans  " . $assoClass->getName() . ", vérifier les cas particuliers ";

		} else if ($getValuesMethod != null && $addValueMethod != null && $removeValueMethod != null) {
			// save relations
			$serverRelations = $serverObject->$getValuesMethod();
			$clientRelations = $clientObject->$getValuesMethod();

			if (count($clientRelations) > 0) {
				// save relations Ids in table

				$relationsIds = [];
				// save Ids'relations used by client (Ids activities are sames between server and client databases)
				foreach ($clientRelations as $relation) {
					$relationsIds[] = $relation->getId();
					$response .= $relation->getId();
				}

				// remove current relations in objects
				foreach ($serverRelations as $relation) {
					$serverObject->$removeValueMethod($relation);
				}

				// recreate relation from central database
				foreach ($relationsIds as $relationsId) {
					$newRelation = $this->em->getRepository($assoClass->getName())->findOneById($relationsId);
					if ($newRelation) {
						$serverObject->$addValueMethod($newRelation);
					}
				}

				$response .= " Many: " . $association . " OK S:" . count($serverObject->$getValuesMethod()) . " C:" . count($clientObject->$getValuesMethod());
			} else {
				$response .= " Many: " . $association . " NOK ";
			}
		}

		return new ArrayCollection([$serverObject, $response]);
	}

	#[Route(path: '/', name: 'sectorarea_index', methods: ['GET'])]
	public function checkGetMethod(string $attributeName, \ReflectionClass $class): ?string {
		$getMethod = null;
		$getMethodName = "get" . ucfirst($attributeName);
		$isMethodName = "is" . ucfirst($attributeName);

		foreach ($class->getMethods() as $method) {
			if ($method->getShortName() == $getMethodName) {
				$getMethod = $method;
			}
		}
		foreach ($class->getMethods() as $method) {
			if ($method->getShortName() == $isMethodName) {
				$getMethod = $method;
			}
		}

		if ($getMethod == null) {
			return null;
		}

		return $getMethod->getName();
	}

	public function checkSetMethod(string $attributeName, \ReflectionClass $class): string {
		$setMethod = null;
		$setMethodName = "set" . ucfirst($attributeName);

		foreach ($class->getMethods() as $method) {
			if ($method->getShortName() == $setMethodName) {
				$setMethod = $method;
			}
		}

		return $setMethod->getName();

	}

	public function checkAddMethod(string $attributeName, \ReflectionClass $attrClass, \ReflectionClass $class): ?string {
		$addMethod = null;
		$addMethodName = "add" . ucfirst($attrClass->getShortName());

		// particular cases
		switch ($attributeName) {
			case "technicianActivities" :
				$addMethodName = "addTechnicianActivity";
				break;
			case "workerActivities" :
				$addMethodName = "addWorkerActivity";
				break;
			case "activities1" :
				$addMethodName = "addActivities1";
				break;
			case "activities2" :
				$addMethodName = "addActivities2";
				break;
			case "activities3" :
				$addMethodName = "addActivities3";
				break;
			case "activities4" :
				$addMethodName = "addActivities4";
				break;
			case "activities5" :
				$addMethodName = "addActivities5";
				break;
			case "activities6" :
				$addMethodName = "addActivities6";
				break;
		}

		foreach ($class->getMethods() as $method) {
			if ($method->getShortName() == $addMethodName) {
				$addMethod = $method;
			}
		}

		if ($addMethod == null) {
			return " erreur " . $addMethodName;
		}

		return $addMethod->getName();
	}

	public function checkRemoveMethod(string $attributeName, \ReflectionClass $attrClass, \ReflectionClass $class): ?string {
		$removeMethod = null;
		$removeMethodName = "remove" . ucfirst($attrClass->getShortName());

		// particular cases
		switch ($attributeName) {
			case "technicianActivities" :
				$removeMethodName = "removeTechnicianActivity";
				break;
			case "workerActivities" :
				$removeMethodName = "removeWorkerActivity";
				break;
			case "activities1" :
				$removeMethodName = "removeActivities1";
				break;
			case "activities2" :
				$removeMethodName = "removeActivities2";
				break;
			case "activities3" :
				$removeMethodName = "removeActivities3";
				break;
			case "activities4" :
				$removeMethodName = "removeActivities4";
				break;
			case "activities5" :
				$removeMethodName = "removeActivities5";
				break;
			case "activities6" :
				$removeMethodName = "removeActivities6";
				break;
		}

		foreach ($class->getMethods() as $method) {
			if ($method->getShortName() == $removeMethodName) {
				$removeMethod = $method;
			}
		}

		if ($removeMethod == null) {
			return " erreur " . $removeMethodName;
		}

		return $removeMethod->getName();
	}

	public function checkUser(Request $request): bool {
		if ($request->isMethod('post')) {
			$apiKey = $request->headers->get('apikey');
		} else {
			$apiKey = $request->query->get('apikey');
		}
		$userRequest = $this->userRepository->findOneByApiToken($apiKey);

		if (!$userRequest) {
			return false;
		}

		if (!$userRequest->getSchool()) {
			return false;
		}
		return true;
	}
}
