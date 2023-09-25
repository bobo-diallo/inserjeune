<?php

namespace App\Controller\Security;

use App\Entity\ChangePasswordDTO;
use App\Entity\Region;
use App\Form\ChangePasswordType;
use App\Repository\CountryRepository;
use App\Repository\RegionRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Services\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use App\Entity\Role;
use App\Entity\User;
use App\Form\UserType;
use App\Tools\Utils;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegisterController extends AbstractController {
	private EntityManagerInterface $em;
	private CountryRepository $countryRepository;
	private RegionRepository $regionRepository;
	private UserRepository $userRepository;
	private UserPasswordHasherInterface $hasher;
	private RoleRepository $roleRepository;
	private TokenStorageInterface $tokenStorage;
	private RequestStack $requestStack;
	private EventDispatcherInterface $dispatcher;
	private EmailService $emailService;
	private UserAuthenticatorInterface $authenticator;
	private UserAuthenticatorInterface $userAuthenticator;
	private FormLoginAuthenticator $formLoginAuthenticator;
	private TranslatorInterface $translator;

	public function __construct(
		EntityManagerInterface $em,
		CountryRepository $countryRepository,
		RegionRepository $regionRepository,
		UserRepository $userRepository,
		UserPasswordHasherInterface $hasher,
		RoleRepository $roleRepository,
		TokenStorageInterface $tokenStorage,
		RequestStack $requestStack,
		EventDispatcherInterface $dispatcher,
		EmailService $emailService,
		UserAuthenticatorInterface $authenticator,
		UserAuthenticatorInterface $userAuthenticator,
		FormLoginAuthenticator $formLoginAuthenticator,
		TranslatorInterface $translator
	) {
		$this->em = $em;
		$this->countryRepository = $countryRepository;
		$this->regionRepository = $regionRepository;
		$this->userRepository = $userRepository;
		$this->hasher = $hasher;
		$this->roleRepository = $roleRepository;
		$this->tokenStorage = $tokenStorage;
		$this->requestStack = $requestStack;
		$this->dispatcher = $dispatcher;
		$this->emailService = $emailService;
		$this->authenticator = $authenticator;
		$this->userAuthenticator = $userAuthenticator;
		$this->formLoginAuthenticator = $formLoginAuthenticator;
		$this->translator = $translator;
	}

	#[Route(path: '/createAccount', name: 'register_create_account', methods: ['GET', 'POST'])]
	public function createAccountAction(Request $request): RedirectResponse|Response {
		$user = new User();
		$form = $this->createForm(UserType::class, $user);
        $countries = $this->countryRepository->findByValid(true);
        $allCountries = $this->countryRepository->findAll();

        //Adaptation for DBTA
        if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'true') {
            $form = $this->createForm(UserType::class, $user);
            $countries = $this->regionRepository->findByValid(true);
            $allCountries = $this->regionRepository->findAll();
        }
		$form->handleRequest($request);

		$isValidPhone = false;

		if ($form->isSubmitted() && $form->isValid()) {
			$existUser = $this->userRepository->findOneByPhone($user->getPhone());

			$typePerson = $request->get('userbundle_user')['typePerson'];
			$country = $user->getCountry();
            $residenceCountry = null;

            //Adaptation for DBTA
            if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'true') {
                $country = $user->getRegion()->getCountry();
                $country->setPhoneCode($user->getRegion()->getPhoneCode());
                $country->setPhoneDigit($user->getRegion()->getPhoneDigit());
                $user->setCountry($user->getRegion()->getCountry());
            }

            if ($typePerson != Role::ROLE_ADMIN && !$country) {
				$this->addFlash('danger', $this->translator->trans('flashbag.the_country_field_is_mandatory'));
				return $this->render('user/register.html.twig', [
					'user' => $user,
					'form' => $form->createView(),
					'countries' => $countries,
                    'allCountries' => $allCountries
				]);
			}


            if (isset ($request->get('userbundle_user')['diaspora'])) {
                // $residenceCountryStr = null;
                if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'true') {
                    $residenceCountryStr = $request->get('userbundle_user')['residenceRegion'];
                } else {
                    $residenceCountryStr = $request->get('userbundle_user')['residenceCountry'];
                }

                if( ! $residenceCountryStr) {
                    $this->addFlash('danger', $this->translator->trans('flashbag.the_country_of_residence_field_is_mandatory'));
                    return $this->render('user/register.html.twig', [
                        'user' => $user,
                        'form' => $form->createView(),
                        'countries' => $countries,
                        'allCountries' => $allCountries
                    ]);
                //Récupération du pays de résidence
                } else {
                    $residenceCountry = $this->countryRepository->find($residenceCountryStr);
                    if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'true') {
                        $residenceCountry = $this->regionRepository->find($residenceCountryStr);
                    }
                }
            }

			//verification de l'indicatif pays
			$phoneCode = '+' . $country->getPhoneCode();
            if($residenceCountry) {
                $phoneCode = '+' . $residenceCountry->getPhoneCode();
            }
			$nationalPhone = "";

			//verification du nombre de digits téléphonique du pays
			$phoneDigit = '+' . $country->getPhoneDigit();
            if($residenceCountry) {
                $phoneDigit = '+' . $residenceCountry->getPhoneDigit();
            }

			if (strncmp($phoneCode, $user->getPhone(), strlen($phoneCode)) === 0) {
				$nationalPhone = substr($user->getPhone(), strlen($phoneCode));
				$isValidPhone = true;
			} else {
				$errorMessage = $this->translator->trans('flashbag.the_number_must_start_with_number', ['{number}' => $phoneCode]);
				$this->addFlash('danger', $errorMessage);
			}

			if ($isValidPhone && strlen($nationalPhone) > 0) {
				// suppression du 0 pour le national
				if ($nationalPhone[0] == '0') {
					$nationalPhone = substr($nationalPhone, 1);
				}

				// reconstruit le numéro de téléphone sans le 0 national
				$validPhone = $phoneCode . $nationalPhone;
				if ($validPhone !== $user->getPhone()) {
					$user->setPhone($validPhone);
					$mesgWarn = $this->translator->trans('flashbag.your_login_account_is_renamed_to_name', ['{name}' => $validPhone]);
					$this->addFlash('warning', $mesgWarn);
				}
			}
			// vérification de la conformité du numéro de téléphone
			if ($isValidPhone) {
				if (strlen($nationalPhone) != $phoneDigit) {
					$isValidPhone = false;
					$errorMessage = $this->translator->trans('flashbag.the_number_without_the_country_code_must_have_number_digits', ['{number}' => (int)$phoneDigit]);
					$this->addFlash('danger', $errorMessage);
				}
				if (!ctype_digit($nationalPhone)) {
					$isValidPhone = false;
					$errorMessage = $this->translator->trans('flashbag.wrong_phone_number_syntax');
					$this->addFlash('danger', $errorMessage);
				}
			}

			// vérification de l'intégrité du mot de passe :
			if ($isValidPhone) {
				if (strlen($user->getPlainPassword()) < 6) {
					$isValidPhone = false;
					$errorMessage = $this->translator->trans('flashbag.the_password_must_have_at_least_6_characters');
					$this->addFlash('danger', $errorMessage);
				}
			}

			// ecriture en base
			if ($isValidPhone) {
				if ($existUser == null) {
					$typePerson = $request->get('userbundle_user')['typePerson'];
					$redirection = $this->addRoleAndGererateRedirection($user, $typePerson);

					// Supprime les espaces du numéro de téléphone
					$user->setPhone(preg_replace('/\s/', '', $user->getPhone()));

					// Encode le password
					$user->setPassword($this->hasher->hashPassword($user, $user->getPlainPassword()));

					// synchronise le username avec le phone
					$user->setUsername($user->getPhone());

					// créé l'adresse mail fictive
					$user->setEmail($user->getPhone() . "@domaine.extension");

					// si établissement , alors création de la clé API
					if ($typePerson == 'ROLE_ETABLISSEMENT') {
						$user->setApiToken(bin2hex(random_bytes(32)));
					}

					$this->em->persist($user);
					$this->em->flush();

					// Authentification de l'utilisateur
					$this->authenticator->authenticateUser(
						$user,
						$this->formLoginAuthenticator,
						$request
					);
					return $redirection;
				} else {
					$errorMessage = $this->translator->trans('flashbag.this_phone_number_is_already_in_use');
					$this->addFlash('danger', $errorMessage);
				}
			}
		}

		return $this->render('user/register.html.twig', [
			'user' => $user,
			'form' => $form->createView(),
			'countries' => $countries,
            'allCountries' => $allCountries
		]);
	}

	private function addRoleAndGererateRedirection(User &$user, $typePerson): RedirectResponse {
		switch ($typePerson) {
			case Utils::COMPANY:
				{
					$user->addProfil($this->createRole(Utils::COMPANY));
					$flashBag = $this->translator->trans('flashbag.welcome_before_answering_the_satisfaction_questionnaire_please_complete_your_profile_below');
					$redirect = $this->redirectToRoute('front_company_new');
				}
				break;
			case Utils::PERSON_DEGREE:
				{
					$user->addProfil($this->createRole(Utils::PERSON_DEGREE));
					$flashBag = $this->translator->trans('flashbag.Welcome_please_complete_your_profile_below');
					$redirect = $this->redirectToRoute('front_persondegree_new');
				}
				break;
			case Utils::SCHOOL:
				{
					$user->addProfil($this->createRole(Utils::SCHOOL));
					$flashBag = $this->translator->trans('flashbag.welcome_please_complete_your_profile_below');
					$redirect = $this->redirectToRoute('front_school_new');
				}
				break;
			default:
				throw new NotFoundHttpException($this->translator->trans('flashbag.unable_to_create_an_account'));
		}
		$this->addFlash(Utils::OFB_SUCCESS, $flashBag);
		return $redirect;
	}

	private function createRole(string $roleName): ?Role {
		$role = $this->roleRepository
			->findOneBy(['role' => $roleName]);

		return ($role) ?: new Role($roleName);
	}

	/**
	 * @param Request $request
	 * @param User $user
	 */
	private function authenticateUser(Request $request, User $user) {
		$token = new UsernamePasswordToken($user, 'main');
		$this->tokenStorage->setToken($token);

		$this->requestStack->getSession()->set('_security_main', serialize($token));

		// Déclencher l'événement de connexion manuellement
		$event = new InteractiveLoginEvent($request, $token);
		$this->dispatcher->dispatch($event, 'security.interactive_login');
	}

	/**
	 * Ask to change password.
	 */
	#[Route(path: '/askNewPassword', name: 'register_ask_new_password', methods: ['GET', 'POST'])]
	public function askNewPasswordAction(Request $request): RedirectResponse|Response {
		$user = new User();
		$form = $this->createForm(UserType::class, $user);
		$form->handleRequest($request);

		$submitPhone = $request->get('userbundle_user')['phone'] ?? null;
		$submitEmail = $request->get('userbundle_user')['email'] ?? null;
		$submitCode = $request->get('userbundle_user')['validCode'] ?? null;

		if ($form->isSubmitted() && $form->isValid()) {
			$isValidEmail = false;

			$existUser = $this->userRepository->findOneByPhone($submitPhone);

			if ($existUser) {
				// si user est diplômé
				if ($existUser->getPersonDegree()) {
					$personDegree = $existUser->getPersonDegree();
					$existEmail = $personDegree->getEmail();
					if ($existEmail == $submitEmail) {
						$isValidEmail = true;
					}
					// si user est établissement
				} elseif ($existUser->getSchool()) {
					$school = $existUser->getSchool();
					$existEmail = $school->getEmail();
					if ($existEmail == $submitEmail) {
						$isValidEmail = true;
					}
					// si user est entreprise
				} elseif ($existUser->getCompany()) {
					$company = $existUser->getCompany();
					$existEmail = $company->getEmail();
					if ($existEmail == $submitEmail) {
						$isValidEmail = true;
					}
					// si user est admin ou législateur
				} else {
					$existEmail = $existUser->getEmail();
					if ($existEmail == $submitEmail) {
						$isValidEmail = true;
					}
				}
			}

			// envoie du message email
			if (strlen($submitCode) == 0) {
				if ($isValidEmail) {
					// creation du code d'activation
					$code = rand(1002, 8902);
					$this->requestStack->getSession()->set('code', $code);
					$this->requestStack->getSession()->set('refu', $existUser->getId());

					$this->emailService->sendCodeChangePassword($submitEmail, $code);
					$this->addFlash('success', $this->translator->trans('flashbag.your_code_is_sent_by_email'));

				} elseif (strlen($submitEmail) == 0) {
					$this->addFlash('danger', $this->translator->trans('flashbag.sending_the_code_by_sms_is_not_yet_authorized_please_enter_a_valid_email_address'));
				} else {
					$this->addFlash('danger', $this->translator->trans('flashbag.your_email_does_not_match_your_profile'));
				}

			} elseif ((strlen($submitCode) == 4) && (is_numeric($submitCode))) {
				if ($submitCode == $this->requestStack->getSession()->get('code')) {
					$this->addFlash('success', $this->translator->trans('flashbag.enter_your_new_password'));
					return $this->redirectToRoute('register_change_password', array('user' => $existUser));
				} else {
					$this->addFlash('danger', $this->translator->trans('flashbag.your_code_is_invalid'));
				}
			} else {
				$this->addFlash('danger', $this->translator->trans('flashbag.the_code_must_have_4_digits'));
			}
		}

		return $this->render('user/ask_password.html.twig', [
			'user' => $user,
			'form' => $form->createView(),
			'userphone' => $submitPhone,
			'useremail' => $submitEmail,
		]);
	}

	#[Route(path: '/change_password', name: 'register_change_password', methods: ['POST', 'GET'])]
	public function changePasswordAction(Request $request): RedirectResponse|Response {
		$user = $this->userRepository->findOneById($this->requestStack->getSession()->get('refu'));

		$changePasswordDTO = new ChangePasswordDTO();
		$form = $this->createForm(ChangePasswordType::class, $changePasswordDTO);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$firstPassword = $request->get('userbundle_user')['plainPassword']['first'];
			$secondPassword = $request->get('userbundle_user')['plainPassword']['second'];

			if ($firstPassword === $secondPassword) {
				$plainPassword = $changePasswordDTO->getPlainPassword();

				if (strlen($plainPassword) < 6) {
					$errorMessage = 'Le mot de passe doit avoir au minimum 6 caractères ';
					$this->addFlash('danger', $errorMessage);
				} else {
					$user->setPassword($this->hasher->hashPassword($user, $plainPassword));

					$this->em->persist($user);
					$this->em->flush();

					$this->addFlash('success', $this->translator->trans('flashbag.your_password_has_been_changed'));
					return $this->redirectToRoute('logout');
				}
			}
		}

		return $this->render('user/change_password.html.twig', [
			'user' => $user,
			'form' => $form->createView()
		]);
	}
}
