<?php

namespace App\Services;

use App\Entity\Candidate;
use App\Entity\Company;
use App\Entity\JobOffer;
use App\Entity\PersonDegree;
use App\Entity\School;
use App\Model\CompanyReceiverNotification;
use App\Model\PersonDegreeReceiverNotification;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

class EmailService {
	private ParameterBagInterface $parameterBag;
	private MailerInterface $mailer;
    private TranslatorInterface $translator;

	public function __construct(
		ParameterBagInterface $parameterBag,
		MailerInterface $mailer,
        TranslatorInterface $translator
	) {
		$this->parameterBag = $parameterBag;
		$this->mailer = $mailer;
        $this->translator = $translator;
	}

	public function sendMailPasswd(string $to, string $code, string $title): bool {
		$subject = $title;
		$messageTxt = "Votre code pour modifier votre mot de passe est : " . $code;
		// $messageTxt = $this->translator->trans("email.Your_code_to_change_your_password_is") . $code;

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
		// $messageTxt .= "IFEF vous souhaite la bienvenue sur la plateforme InserJeune ";
		$messageTxt .= $this->translator->trans('email.welcomes_you_to_the_InserJeune_platform');

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
		// $subject = "Candidature via IFEF - $title";
		$subject = $this->translator->trans('email.Application_via') . " - $title";

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
		// $header = "From: \"$title\"<nepasrepondre@inserjeune.francophonie.org>$nextLine";
		$header = "From: \"$title\" " . $this->parameterBag->get('email_from') . "$nextLine";
		// $header .= "Reply-to: \"$title\" <nepasrepondre@inserjeune.francophonie.org>$nextLine";
		$header .= "Reply-to: \"$title\" " . $this->parameterBag->get('email_from') . "$nextLine";
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

	public function sendCandidateMail(Candidate $candidate, JobOffer $jobOffer, ?string $emailCopy): bool {
		$pathCv = $this->parameterBag->get('brochures_directory') . DIRECTORY_SEPARATOR . $candidate->getCvFilename();
		$pathCoverLetter = $this->parameterBag->get('brochures_directory') . DIRECTORY_SEPARATOR . $candidate->getCoverLetterFilename();

        $emailBuilder = (new Email())
            ->from($this->parameterBag->get('email_from'))
            ->to($jobOffer->getPostedEmail());

        if ($emailCopy) {
            $emailBuilder = $emailBuilder->cc($emailCopy);
        }

		$email = $emailBuilder
			->replyTo($this->parameterBag->get('email_from'))
			// ->subject('Candidature via IFEF - ' . $jobOffer->getTitle())
			->subject($this->translator->trans('email.Application_via') . ' - ' . $jobOffer->getTitle())
			->html($candidate->getMessage())
			->attachFromPath($pathCv, 'CV-' . $candidate->getCandidateName() . '.pdf', 'application/pdf')
			->attachFromPath($pathCoverLetter, 'Cover-letter-' . $candidate->getCandidateName() . '.pdf', 'application/pdf')
		;

		try {
			$this->mailer->send($email);
			return true;

		} catch (TransportExceptionInterface $e) {
			return false;
		} finally {
			@unlink($pathCv);
			@unlink($pathCoverLetter);
		}
	}

	public function sendNotificationEnrollmentDegree(PersonDegreeReceiverNotification $personDegree, School $school): void {
        $email = (new TemplatedEmail())
			->from($this->parameterBag->get('email_from'))
			->to($personDegree->email())
			->replyTo($this->parameterBag->get('email_from'))
			->subject($this->translator->trans('email.Enrollment_via'))
			->htmlTemplate('email/enrollement_persondegree.html.twig')
			->context([
				'persondegree_name' => $personDegree->firstname() . ' ' . $personDegree->lastname(),
				'school_name' => $school->getName(),
				'persondegree_login' => $personDegree->phone(),
				'persondegree_password' => $personDegree->temporaryPassword()
			])
		;

		$this->mailer->send($email);
	}

	public function sendNotificationEnrollmentCompany(CompanyReceiverNotification $company, School $school): void {
		$email = (new TemplatedEmail())
			->from($this->parameterBag->get('email_from'))
			->to($company->email())
			->replyTo($this->parameterBag->get('email_from'))
			// ->subject('Enrollement via IFEF')
            ->subject($this->translator->trans('email.Enrollment_via'))
			->htmlTemplate('email/enrollement_company.html.twig')
			->context([
				'company_name' => $company->name(),
				'school_name' => $school->getName(),
				'company_login' => $company->phone(),
				'company_password' => $company->temporaryPassword()
			])
		;

		$this->mailer->send($email);
	}

    public function sendCodeChangePassword(string $to, string $code): void {
        $email = (new TemplatedEmail())
            ->from($this->parameterBag->get('email_from'))
            ->to($to)
            ->replyTo($this->parameterBag->get('email_from'))
            // ->subject('Modification mot de passe inserjeune')
            ->subject($this->translator->trans('email.Changing_your_inserjeune_password'))
            ->htmlTemplate('email/reset_password.html.twig')
            ->context(['code' => $code])
        ;

        $this->mailer->send($email);
    }

    public function sendRelaunchPersonDegree(PersonDegree $personDegree, string $duration, string $created_date): void {
        echo $personDegree->getId() . " | " . $personDegree->getName() . " | " . $duration . " | " . $created_date . " | " . $personDegree->getPhoneMobile1(). " | " . $personDegree->getEmail() . "\n";
        $email = (new TemplatedEmail())
            ->from($this->parameterBag->get('email_from'))
            ->to($personDegree->getEmail())
            ->replyTo($this->parameterBag->get('email_from'))
            ->subject($this->translator->trans('email.AutoRelaunch'))
            ->htmlTemplate('email/relaunch_graduate.html.twig')
            ->context([
                'name' => $personDegree->getFirstname() . ' ' . $personDegree->getLastname(),
                'duration' => $duration,
                'created_date' => $created_date,
                'phone' => $personDegree->getPhoneMobile1()
            ])
        ;

        $this->mailer->send($email);
    }
    public function sendRelaunchCompany(Company $company, string $duration, string $created_date): void {
        echo $company->getId() . " | " . $company->getName() . " | " . $duration . " | " . $created_date . " | " . $company->getPhoneStandard() . " | " . $company->getEmail() . "\n";
        $email = (new TemplatedEmail())
            ->from($this->parameterBag->get('email_from'))
            ->to($company->getEmail())
            ->replyTo($this->parameterBag->get('email_from'))
            ->subject($this->translator->trans('email.AutoRelaunch'))
            ->htmlTemplate('email/relaunch_company.html.twig')
            ->context([
                'name' => $company->getName(),
                'duration' => $duration,
                'created_date' => $created_date,
                'phone' => $company->getPhoneStandard()
            ])
        ;

        $this->mailer->send($email);
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
