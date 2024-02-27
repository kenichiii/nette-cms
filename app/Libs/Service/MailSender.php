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
	 * @param string|array $to
	 * @param string $subject
	 * @param string $latteFile
	 * @param array $params
	 * @return void
	 */
	public function sendEmail(string|array $to, string $subject, string $latteFile, array $params, ?string $from = null): void
	{
		$template = $this->createTemplate();
		$html = $template->renderToString($latteFile, $params);

		$mail = new Nette\Mail\Message;
		$mail->setHtmlBody($html);
		$mail->setFrom($from ?: $this->settings['info_email']);
		$mail->setSubject($subject);

		if (is_array($to)) {
			foreach ($to as $recipient) {
				$mail->addTo($recipient);
			}
		} else {
			$mail->addTo($to);
		}
		$mailer = new Nette\Mail\SendmailMailer;
		$mailer->send($mail);
	}
}
