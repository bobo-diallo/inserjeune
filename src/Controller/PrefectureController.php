<?php

namespace App\Controller;

use App\Entity\Prefecture;
use App\Entity\Region;
use App\Form\PrefectureType;
use App\Repository\PrefectureRepository;
use App\Repository\RegionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/prefecture')]
#[Security("is_granted('ROLE_ADMIN') or 
            is_granted('ROLE_ADMIN_PAYS') or
            is_granted('ROLE_ADMIN_REGIONS') or
            is_granted('ROLE_DIRECTEUR')")]
class PrefectureController extends AbstractController {
    private EntityManagerInterface $em;
    private PrefectureRepository $prefectureRepository;
    private RegionRepository $regionRepository;
    private TranslatorInterface $translator;

    public function __construct(
        EntityManagerInterface $em,
        PrefectureRepository       $prefectureRepository,
        RegionRepository    $regionRepository,
        TranslatorInterface $translator
    ) {
        $this->em = $em;
        $this->prefectureRepository = $prefectureRepository;
        $this->regionRepository = $regionRepository;
        $this->translator = $translator;
    }

    #[Route(path: '/', name: 'prefecture_index', methods: ['GET'])]
    public function indexAction(): Response {
        $prefecture = $this->prefectureRepository->findAll();

        if ($this->getUser()->hasRole('ROLE_ADMIN_PAYS')) {
            $prefecture = [];
            $userCountry = $this->getUser()->getCountry();
            $regions = $this->regionRepository->findByCountry($userCountry);
            foreach ($regions as $region) {
                $prefecture = array_merge($prefecture, $this->prefectureRepository->findByRegion($region));
            }
        } else if ($this->getUser()->hasRole('ROLE_ADMIN_REGIONS')) {
            $prefecture = [];
            $regions =  $this->getUser()->getAdminRegions();
            foreach ($regions as $region) {
                $prefecture = array_merge($prefecture, $this->prefectureRepository->findByRegion($region));
            }
        }
        return $this->render('prefecture/index.html.twig', array(
            'prefectures' => $prefecture,
        ));
    }
    #[Security("is_granted('ROLE_ADMIN') or 
            is_granted('ROLE_ADMIN_PAYS')")]
    #[Route(path: '/new', name: 'prefecture_new', methods: ['GET', 'POST'])]
    public function newAction(Request $request): RedirectResponse|Response {
        $prefecture = new Prefecture();
        $form = $this->createForm(PrefectureType::class, $prefecture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($prefecture);
            $this->em->flush();

            return $this->redirectToRoute('prefecture_show', ['id' => $prefecture->getId()]);
        }

        return $this->render('prefecture/new.html.twig', [
            'prefecture' => $prefecture,
            'form' => $form->createView(),
        ]);
    }
    #[Security("is_granted('ROLE_ADMIN') or 
            is_granted('ROLE_ADMIN_PAYS')")]
    #[Route(path: '/{id}', name: 'prefecture_show', methods: ['GET'])]
    public function showAction(Prefecture $prefecture): Response {
        return $this->render('prefecture/show.html.twig', [
            'prefecture' => $prefecture
        ]);
    }
    #[Security("is_granted('ROLE_ADMIN') or 
            is_granted('ROLE_ADMIN_PAYS')")]
    #[Route(path: '/{id}/edit', name: 'prefecture_edit', methods: ['GET', 'POST'])]
    public function editAction(Request $request, Prefecture $prefecture): RedirectResponse|Response {

        $editForm = $this->createForm(PrefectureType::class, $prefecture);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->em->flush();

            return $this->redirectToRoute('prefecture_show', array('id' => $prefecture->getId()));
        }

        return $this->render('prefecture/edit.html.twig', [
            'prefecture' => $prefecture,
            'edit_form' => $editForm->createView(),
        ]);
    }
    #[Security("is_granted('ROLE_ADMIN') or 
            is_granted('ROLE_ADMIN_PAYS')")]
    #[Route(path: '/delete/{id}', name: 'prefecture_delete', methods: ['GET'])]
    public function deleteAction(Request $request, ?Prefecture $prefecture): RedirectResponse {
        if (array_key_exists('HTTP_REFERER', $request->server->all())) {
            if ($prefecture) {
                $this->em->remove($prefecture);
                $this->em->flush();
                $this->addFlash('success', $this->translator->trans('flashbag.the_deletion_is_done_successfully'));
            } else {
                $this->addFlash('warning', $this->translator->trans('flashbag.unable_to_delete_prefecture'));
                return $this->redirect($request->server->all()['HTTP_REFERER']);
            }
        }
        return $this->redirectToRoute('prefecture_index');
    }

    // #[Route(path: '/getPrefecturesByRegion', name: 'get_prefectures_by_region', methods: ['GET'])]
    // public function getPrefecturesByRegion(Request $request): JsonResponse|Response {
    //
    //     $idRegion = $request->query->get("idRegion");
    //     $result = [];
    //     $prefectures = $this->prefectureRepository->findByRegion($idRegion);
    //     foreach ($prefectures as $prefecture) {
    //         $result[] = [$prefecture->getId() => $prefecture->getName()];
    //     }
    //     return new JsonResponse($result);
    // }

}
