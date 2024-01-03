<?php

namespace App\Controller;

use App\Entity\Country;
use App\Form\CountryType;
use App\Repository\CountryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/country')]
// #[IsGranted('ROLE_ADMIN')]
#[Security("is_granted('ROLE_ADMIN') or 
            is_granted('ROLE_DIRECTEUR')")]
class CountryController extends AbstractController {
	private EntityManagerInterface $em;
	private CountryRepository $countryRepository;
	private TranslatorInterface $translator;

	public function __construct(
		EntityManagerInterface $em,
		CountryRepository $countryRepository,
		TranslatorInterface $translator
	) {
		$this->em = $em;
		$this->countryRepository = $countryRepository;
		$this->translator = $translator;
	}

	#[Route(path: '/', name: 'country_index', methods: ['GET'])]
	public function indexAction(): Response {
		return $this->render('country/index.html.twig', [
			'countries' => $this->countryRepository->findAll()
		]);
	}
    #[IsGranted('ROLE_ADMIN')]
	#[Route(path: '/new', name: 'country_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$country = new Country();

        // Adaptation for specific provinces and countries adaptation
        if ($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'true') {
            // $country->setName("country for province");
            $country->setIsoCode("");
            $country->setPhoneDigit(0);
            $country->setPhoneCode(0);
            $country->setValid(true);
        }

		$form = $this->createForm(CountryType::class, $country);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->em->persist($country);
			$this->em->flush();

			return $this->redirectToRoute('country_show', ['id' => $country->getId()]);
		}

		return $this->render('country/new.html.twig', [
			'country' => $country,
			'form' => $form->createView(),
		]);
	}
    #[IsGranted('ROLE_ADMIN')]
	#[Route(path: '/{id}', name: 'country_show', methods: ['GET'])]
	public function showAction(Country $country): Response {
		return $this->render('country/show.html.twig', array(
			'country' => $country,
		));
	}
    #[IsGranted('ROLE_ADMIN')]
	#[Route(path: '/{id}/edit', name: 'country_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request, Country $country): RedirectResponse|Response {
		$editForm = $this->createForm(CountryType::class, $country);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$this->em->flush();
			return $this->redirectToRoute('country_show', ['id' => $country->getId()]);
		}

		return $this->render('country/edit.html.twig', [
			'country' => $country,
			'edit_form' => $editForm->createView(),
		]);
	}
    #[IsGranted('ROLE_ADMIN')]
	#[Route(path: '/delete/{id}', name: 'country_delete', methods: ['GET'])]
	public function deleteAction(Request $request, ?Country $country): RedirectResponse {
		if (array_key_exists('HTTP_REFERER', $request->server->all())) {
			if ($country) {
				$this->em->remove($country);
				$this->em->flush();
				$this->addFlash('success', $this->translator->trans('flashbag.the_deletion_is_done_successfully'));
			} else {
				$this->addFlash('warning', $this->translator->trans('flashbag.unable_to_delete_the_country'));
				return $this->redirect($request->server->all()['HTTP_REFERER']);
			}
		}
		return $this->redirectToRoute('country_index');
	}
}
