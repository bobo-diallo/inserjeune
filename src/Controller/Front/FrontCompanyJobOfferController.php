<?php

namespace App\Controller\Front;

use App\Entity\JobOffer;
use App\Form\JobOfferType;
use App\Repository\JobOfferRepository;
use App\Services\CompanyService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/front/company/jobOffer')]
#[IsGranted('ROLE_ENTREPRISE')]
class FrontCompanyJobOfferController extends AbstractController {
	private EntityManagerInterface $em;
	private CompanyService $companyService;
	private JobOfferRepository $jobOfferRepository;

	public function __construct(
		EntityManagerInterface $em,
		CompanyService         $companyService,
		JobOfferRepository     $jobOfferRepository
	) {
		$this->em = $em;
		$this->companyService = $companyService;
		$this->jobOfferRepository = $jobOfferRepository;
	}

	#[Route(path: '/', name: 'front_company_jobOffer_index', methods: ['GET'])]
	public function indexAction(): Response {
		$company = $this->companyService->getCompany();
		$jobOffers = $this->jobOfferRepository->findByCompany($company);
		$othersJobOffers = $this->jobOfferRepository->othersJobs($company);

		return $this->render('frontCompanyJobOffer/index.html.twig', [
			'jobOffers' => $jobOffers,
			'othersJobs' => $othersJobOffers,
		]);
	}

	#[Route(path: '/new', name: 'front_company_jobOffer_new', methods: ['GET', 'POST'])]
	public function newAction(Request $request): RedirectResponse|Response {
		$company = $this->companyService->getCompany();

		$jobOffer = new JobOffer();
		$jobOffer->setCompany($company);
		$form = $this->createForm(JobOfferType::class, $jobOffer);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$jobOffer->setCompany($company);
			$this->em->persist($jobOffer);
			$this->em->flush();

			return $this->redirectToRoute('front_company_jobOffer_show', ['id' => $jobOffer->getId()]);
		}

		return $this->render('frontCompanyJobOffer/new.html.twig', [
			'company' => $company,
			'form' => $form->createView(),

		]);
	}

	#[Route(path: '/{id}', name: 'front_company_jobOffer_show', methods: ['GET'])]
	public function showAction(JobOffer $jobOffer): Response {
		return $this->render('frontCompanyJobOffer/show.html.twig', [
			'jobOffer' => $jobOffer,
			'company' => $this->companyService->getCompany()
		]);
	}

	#[Route(path: '/{id}/edit', name: 'front_company_jobOffer_edit', methods: ['GET', 'POST'])]
	public function editAction(Request $request, JobOffer $jobOffer): RedirectResponse|Response {
		$company = $this->companyService->getCompany();

		if ($jobOffer->getCompany() !== $company)
			throw new NotFoundHttpException('Aucune offre trouvée');

		$editForm = $this->createForm(JobOfferType::class, $jobOffer);
		$editForm->handleRequest($request);

		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$jobOffer->setCompany($company);
			$this->em->flush();

			return $this->redirectToRoute('front_company_jobOffer_show', ['id' => $jobOffer->getId()]);
		}

		return $this->render('frontCompanyJobOffer/edit.html.twig', [
			'company' => $this->companyService->getCompany(),
			'edit_form' => $editForm->createView(),
		]);
	}

	#[Route(path: '/delete/{id}', name: 'front_company_jobOffer_delete', methods: ['GET'])]
	public function deleteAction(Request $request, ?JobOffer $jobOffer): RedirectResponse {
		if ($jobOffer->getCompany() !== $this->companyService->getCompany())
			throw new NotFoundHttpException('Aucune offre trouvée');

		if (array_key_exists('HTTP_REFERER', $request->server->all())) {
			if ($jobOffer) {
				$this->em->remove($jobOffer);
				$this->em->flush();
				$this->addFlash('success', 'La suppression est faite avec success');
			} else {
				$this->addFlash('warning', 'Impossible de suppression l\'offre');
				return $this->redirect($request->server->all()['HTTP_REFERER']);
			}
		}
		return $this->redirectToRoute('front_company_jobOffer_index');
	}

	public function send(): bool {
		$to = 'bobo.diallo@m2t.biz'; // Déclaration de l'adresse de destination.

		// Déclaration des messages au format texte et au format HTML.
		$messageTxt = "Bonjour M. Diallo, un candidat vient de postuler à l'offre: Développeur PHP/Symfony.";

		$filename = 'img/legend_bar_satisfactions.jpg';
		$file = new File($filename);
		dump($file->getMimeType());
		dump($file->getFilename());
		dump($file->guessExtension());
		dump($file->getPath());
		die();

		// Lecture et mise en forme de la pièce jointe.
		$fichier = fopen($filename, "r");
		$attachement = fread($fichier, filesize($filename));
		$attachement = chunk_split(base64_encode($attachement));
		fclose($fichier);

		// Création de la boundary.
		$boundary = sprintf('-----=%s', md5(rand()));
		$boundaryAlt = sprintf('-----=%s', md5(rand()));

		$nl = "\n";

		// Création du header de l'e-mail.
		$header = $this->setHeader('IFEF Pilote', $boundary, $nl);
		$message = $this->setMessage($messageTxt, $boundary, $boundaryAlt, $nl);
		// Ajout de la pièce jointe.
		$message = $this->attachFile($message, 'image/jpeg', $filename, $attachement, $boundary, $nl);

		return mail($to, 'Candidature via IFEF', $message, $header);
	}

	private function setHeader(string $title, string $boundary, string $nextLine): string {
		$header = "From: \"$title\"<jobs@ifef.fr>$nextLine";
		$header .= "Reply-to: \"$title\" <jobs@ifef.fr>$nextLine";
		$header .= "MIME-Version: 1.0$nextLine";
		$header .= "Content-Type: multipart/mixed;$nextLine boundary=\"$boundary\"$nextLine";

		return $header;
	}

	public function setMessage(string $messageTxt, string $boundary, string $boundaryAlt, string $nextLine) {
		$messageHtml = "<p>$messageTxt</p>";

		$message = "$nextLine--$boundary" . $nextLine;
		$message .= "Content-Type: multipart/alternative;$nextLine boundary=\"$boundaryAlt\"$nextLine";
		$message .= "$nextLine--$boundaryAlt" . $nextLine;

		// Ajout du message au format texte.
		$message .= "Content-Type: text/plain; charset=\"UTF-8\"$nextLine";
		$message .= "Content-Transfer-Encoding: 8bit$nextLine";
		$message .= $nextLine . $messageTxt . $nextLine;
		$message .= "$nextLine--$boundaryAlt" . $nextLine;

		// Ajout du message au format HTML.
		$message .= "Content-Type: text/html; charset=\"UTF-8\"$nextLine";
		$message .= "Content-Transfer-Encoding: 8bit$nextLine";
		$message .= $nextLine . $messageHtml . $nextLine;

		// On ferme la boundary alternative.
		$message .= "$nextLine--$boundaryAlt--$nextLine";
		$message .= "$nextLine--$boundary" . $nextLine;

		return $message;
	}

	public function attachFile(
		$message,
		string $fileType = 'image/jpeg',
		$filename = null,
		$attachement = null,
		$boundary = null,
		$nextLine = null
	): string {
		$message .= "Content-Type: $fileType; name=\"$filename\"$nextLine";
		$message .= "Content-Transfer-Encoding: base64$nextLine";
		$message .= "Content-Disposition: attachment; filename=\"$filename\"$nextLine";
		$message .= $nextLine . $attachement . $nextLine . $nextLine;
		$message .= "$nextLine--$boundary--$nextLine";

		return $message;
	}

}