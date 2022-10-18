<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{

	private TokenStorageInterface $tokenStorage;
	private RouterInterface $router;

	public function __construct(
		TokenStorageInterface $tokenStorage,
		RouterInterface $router
	) {
		$this->tokenStorage = $tokenStorage;
		$this->router = $router;
	}

	#[Route('/login', name: 'login')]
	public function index(AuthenticationUtils $authenticationUtils): Response
	{
		$error = $authenticationUtils->getLastAuthenticationError();
		$lastUsername = $authenticationUtils->getLastUsername();

		return $this->render('login/index.html.twig', [
			'last_username' => $lastUsername,
			'error' => $error
		]);
	}

	#[Route('/logout', name: 'logout', methods: ['GET'])]
	public function logout(Request $request)
	{
		$request->getSession()->invalidate();
		$this->tokenStorage->setToken();
		$this->redirectToRoute('login');
		// throw new \Exception('Don\'t forget to activate logout in security.yaml');
	}

	#[Route('/access_denied', name: 'access_denied', methods: ['GET'])]
	public function accessDeniedHandler(): Response
	{
		return $this->render('error/access_denied.html.twig', [
			'response' => 'Vous n’êtes pas autorisé à accéder à cette page',
		]);
	}

	#[Route('/change_locale/{locale}', name: 'change_locale', methods: ['GET'])]
	public function changeLocale(string $locale, Request $request): Response
	{
		$route = $this->router->matchRequest(Request::create($request->headers->get('referer')));
		$request->getSession()->set('_locale', $locale);
		$request->setLocale($locale);
		$routePath = $this->router->generate($route['_route'], ['_locale' => $locale]);

		return new RedirectResponse($routePath);
	}
}
