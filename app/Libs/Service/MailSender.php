<?php

declare(strict_types=1);

namespace App\Libs\Service;

use App\Libs\Service\App\SettingsService;
use App\Libs\Service\App\Translator;
use Nette;

final class MailSender
{

	public function __construct(
		private Nette\Application\LinkGenerator $linkGenerator,
		private Nette\Bridges\ApplicationLatte\TemplateFactory $templateFactory,
		private SettingsService $settings,
		private Translator $translator,
	)
	{
	}

	private function createTemplate(): Nette\Application\UI\Template
	{
		$template = $this->templateFactory->createTemplate();
		$template->getLatte()->addProvider('uiControl', $this->linkGenerator);
		$template->setTranslator($this->translator);
		return $template;
	}

	/**
	 * @param string $translatorSection
	 * @param string|array $to
	 * @param string $subject
	 * @param string $latteFile
	 * @param array $params
	 * @param string|null $from
	 * @return void
	 */
	public function sendEmail(string $translatorSection, string|array $to, string $subject, string $latteFile, array $params, ?string $from = null): void
	{
		$this->translator->setSection($translatorSection);
		$template = $this->createTemplate();
		$html = $template->renderToString($latteFile, $params);

		$mail = new \PHPMailer\PHPMailer\PHPMailer(true);

		$mail->IsMail();
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		if (is_file('../private/key.private')) {
			$mail->DKIM_domain = $_SERVER['HTTP_HOST'];
			$mail->DKIM_private = '../private/key.private'; // Make sure to protect the key from being publicly accessible!
			$mail->DKIM_selector = $this->settings['dkim_selector'];
			$mail->DKIM_passphrase = $this->settings['dkim_password'];
			$mail->DKIM_identity = $mail->From;
		}

		$mail->setFrom($from ?: $this->settings['info_email']);


		if (is_array($to)) {
			foreach ($to as $recipient) {
				$mail->addAddress($recipient);               //Name is optional
			}
		} else {
			$mail->addAddress($to);
		}

		$mail->addReplyTo($from ?: $this->settings['info_email']);

		//Content
		$mail->isHTML(true);                                  //Set email format to HTML
		$mail->Subject = $subject;
		$mail->Body    = $html;
		//$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

		$mail->send();
	}
}
