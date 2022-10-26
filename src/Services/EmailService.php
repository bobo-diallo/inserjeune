<?php

namespace App\Services;

use App\Entity\Candidate;
use App\Entity\JobOffer;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService {
	private ParameterBagInterface $parameterBag;
	private MailerInterface $mailer;

	public function __construct(
		ParameterBagInterface $parameterBag,
		MailerInterface $mailer
	) {
		$this->parameterBag = $parameterBag;
		$this->mailer = $mailer;
	}

	public function sendMailPasswd(string $to, string $code, string $title): bool {
		$subject = $title;
		$messageTxt = "Votre code pour modifier votre mot de passe est : " . $code;

		// Création de la boundary.
		$boundary = sprintf('-----=%s', md5(rand()));
		$boundaryAlt = sprintf('-----=%s', md5(rand()));

		$nextLine = (!preg_match("#^[a-z0-9._-]+@(hotmail|live|msn).[a-z]{2,4}$#", $to)) ? "\r\n" : "\n";

		// Création du header de l'e-mail.
		$header = $this->setHeader('Inserjeune', $boundary, $nextLine);
		$message = $this->setMessage($messageTxt, $boundary, $boundaryAlt, $nextLine);

		return mail($to, $subject, $message, $header);
	}

	public function sendMailConfirmRegistration(string $to, string $firstname, string $title, string $actor, string $phone): bool {
		$subject = $title;
		$messageTxt = "Bonjour " . $firstname . "<br><br>";
		$messageTxt .= "Votre incription en tant que " . $actor . " est confirmé" . "<br>";
		$messageTxt .= "Votre N° de Téléphone de connexion :" . $phone . "<br><br>";
		$messageTxt .= "IFEF vous souhaite la bienvenue sur la plateforme InserJeune ";

		// Création de la boundary.
		$boundary = sprintf('-----=%s', md5(rand()));
		$boundaryAlt = sprintf('-----=%s', md5(rand()));

		$nextLine = (!preg_match("#^[a-z0-9._-]+@(hotmail|live|msn).[a-z]{2,4}$#", $to)) ? "\r\n" : "\n";

		// Création du header de l'e-mail.
		$header = $this->setHeader('Inserjeune', $boundary, $nextLine);
		$message = $this->setMessage($messageTxt, $boundary, $boundaryAlt, $nextLine);

		return mail($to, $subject, $message, $header);
	}

	public function sendMail(Candidate $candidate, string $title): bool {
		$messageTxt = $candidate->getMessage();
		$to = $candidate->getEmailDestination();
		$subject = "Candidature via IFEF - $title";

		// Création de la boundary.
		$boundary = sprintf('-----=%s', md5(rand()));
		$boundaryAlt = sprintf('-----=%s', md5(rand()));

		$nextLine = (!preg_match("#^[a-z0-9._-]+@(hotmail|live|msn).[a-z]{2,4}$#", $to)) ? "\r\n" : "\n";

		// Création du header de l'e-mail.
		$header = $this->setHeader('Inserjeune', $boundary, $nextLine);
		$message = $this->setMessage($messageTxt, $boundary, $boundaryAlt, $nextLine);

		$message .= $this->attachFile($candidate->getCv(), $boundary, $nextLine, $candidate->getCandidateName());
		$message .= $this->attachFile($candidate->getCoverLetter(), $boundary, $nextLine, $candidate->getCandidateName(), 'LM');

		return mail($to, $subject, $message, $header);
	}

	private function setHeader(string $title, string $boundary, string $nextLine): string {
		$header = "From: \"$title\"<nepasrepondre@inserjeune.francophonie.org>$nextLine";
		$header .= "Reply-to: \"$title\" <nepasrepondre@inserjeune.francophonie.org>$nextLine";
		$header .= "MIME-Version: 1.0$nextLine";
		$header .= "Content-Type: multipart/mixed;$nextLine boundary=\"$boundary\"$nextLine";

		return $header;
	}

	private function setMessage(string $messageTxt, string $boundary, string $boundaryAlt, string $nextLine): string {
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

	public function sendCandidateMail(Candidate $candidate, JobOffer $jobOffer): bool {
		$pathCv = $this->parameterBag->get('brochures_directory') . DIRECTORY_SEPARATOR . $candidate->getCvFilename();
		$pathCoverLetter = $this->parameterBag->get('brochures_directory') . DIRECTORY_SEPARATOR . $candidate->getCoverLetterFilename();

		$email = (new Email())
			->from($this->parameterBag->get('email_from'))
			->to($jobOffer->getPostedEmail())
			->replyTo($this->parameterBag->get('email_from'))
			->subject('Candidature via IFEF - ' . $jobOffer->getTitle())
			->html($candidate->getMessage())
			->attachFromPath($pathCv, 'CV-' . $candidate->getCandidateName(), 'application/pdf')
			->attachFromPath($pathCoverLetter, 'Cover-letter-' . $candidate->getCandidateName(), 'application/pdf')
		;

		try {
			$this->mailer->send($email);
			return true;

		} catch (TransportExceptionInterface $e) {
			var_dump($e->getMessage());
			die();
			return false;
		} finally {
			@unlink($pathCv);
			@unlink($pathCoverLetter);
		}
	}

	/**
	 * Permet de joindre un fichier
	 */
	private function attachFile(File $file, $boundary, $nextLine, $nameFile, string $typeDocument = 'CV'): string {
		$mimeType = $file->getMimeType();
		$filename = sprintf('%s_%s.%s', $typeDocument, $nameFile, $file->guessExtension());
		$file->move('uploads', $filename);
		$path = 'uploads' . DIRECTORY_SEPARATOR . $filename;

		// Lecture et mise en forme de la pièce jointe.
		$fichier = fopen($path, "rb");
		$attachement = fread($fichier, filesize($path));
		$attachement = chunk_split(base64_encode($attachement));
		fclose($fichier);

		$message = "Content-Type: $mimeType; name=\"$filename\"$nextLine";
		$message .= "Content-Transfer-Encoding: base64$nextLine";
		$message .= "Content-Disposition: attachment; filename=\"$filename\"$nextLine";
		$message .= $nextLine . $attachement . $nextLine . $nextLine;
		$message .= "--$boundary" . $nextLine;

		@unlink($path);

		return $message;
	}
}
