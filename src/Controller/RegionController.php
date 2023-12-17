<?php

namespace App\Controller;

use App\Entity\Region;
use App\Form\RegionType;
use App\Repository\RegionRepository;
use App\Repository\CurrencyRepository;
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

#[Route(path: '/region')]
// #[IsGranted('ROLE_ADMIN')]
#[Security("is_granted('ROLE_ADMIN') or 
            is_granted('ROLE_ADMIN_PAYS') or
            is_granted('ROLE_DIRECTEUR')")]
class RegionController extends AbstractController {
	private EntityManagerInterface $em;
	private RegionRepository $regionRepository;
	private CurrencyRepository $currencyRepository;
	private TranslatorInterface $translator;

	public function __construct(
		EntityManagerInterface $em,
		RegionRepository       $regionRepository,
        CurrencyRepository       $currencyRepository,
		TranslatorInterface $translator
	) {
		$this->em = $em;
		$this->regionRepository = $regionRepository;
		$this->currencyRepository = $currencyRepository;
		$this->translator = $translator;
	}

	#[Route(path: '/', name: 'region_index', methods: ['GET'])]
	public function indexAction(): Response {
        //adaptation dbta: mise à jour des currency_id pour les regions importées
        $regions = $this->regionRepository->findAll();
        if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'true') {
            foreach ($regions as $region) {
                if(!$region->getCurrency()) {
                    $region->setCurrency($region->getCountry()->getCurrency());
                    if ($region->getCurrency()) {
                        $this->em->persist($region);
                        $this->em->flush();
                    }
                }
            }
        }
        if ($this->getUser()->hasRole('ROLE_ADMIN_PAYS')) {
            $userCountry = $this->getUser()->getCountry();
            $regions =  $this->regionRepository->findByCountry($userCountry->getId());
        } else if ($this->getUser()->hasRole('ROLE_ADMIN_REGIONS')) {
            $regions =  $this->getUser()->getAdminRegions();
        }

		return $this->render('region/index.html.twig', array(
			'regions' => $regions,
		));
	}
    #[IsGranted('ROLE_ADMIN')]
	#[Route(path: '/new', name: 'region_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$region = new Region();
        $form = $this->createForm(RegionType::class, $region);
        if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'true') {
            $form = $this->createForm(RegionType::class, $region);
        }
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->em->persist($region);
			$this->em->flush();

			return $this->redirectToRoute('region_show', ['id' => $region->getId()]);
		}

		return $this->render('region/new.html.twig', [
			'region' => $region,
			'form' => $form->createView(),
		]);
	}
    #[IsGranted('ROLE_ADMIN')]
	#[Route(path: '/{id}', name: 'region_show', methods: ['GET'])]
	public function showAction(Region $region): Response {
		return $this->render('region/show.html.twig', [
			'region' => $region
		]);
	}
    #[IsGranted('ROLE_ADMIN')]
	#[Route(path: '/{id}/edit', name: 'region_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request, Region $region): RedirectResponse|Response {

        $editForm = $this->createForm(RegionType::class, $region);
        if($_ENV['STRUCT_PROVINCE_COUNTRY_CITY'] == 'true') {
            $editForm = $this->createForm(RegionType::class, $region);
        }
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$this->em->flush();

			return $this->redirectToRoute('region_show', array('id' => $region->getId()));
		}

		return $this->render('region/edit.html.twig', [
			'region' => $region,
			'edit_form' => $editForm->createView(),
		]);
	}
    #[IsGranted('ROLE_ADMIN')]
	#[Route(path: '/delete/{id}', name: 'region_delete', methods: ['GET'])]
	public function deleteAction(Request $request, ?Region $region): RedirectResponse {
		if (array_key_exists('HTTP_REFERER', $request->server->all())) {
			if ($region) {
				$this->em->remove($region);
				$this->em->flush();
				$this->addFlash('success', $this->translator->trans('flashbag.the_deletion_is_done_successfully'));
			} else {
				$this->addFlash('warning', $this->translator->trans('flashbag.unable_to_delete_city'));
				return $this->redirect($request->server->all()['HTTP_REFERER']);
			}
		}
		return $this->redirectToRoute('region_index');
	}

}
