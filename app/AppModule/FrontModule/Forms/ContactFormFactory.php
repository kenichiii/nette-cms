<?php

declare(strict_types=1);

namespace App\AppModule\FrontModule\Forms;

use App\Libs\Repository\ContactFormRepository;
use App\Libs\Service\App\MailSender;
use App\Libs\Service\App\SettingsService;
use App\Libs\Service\App\Translator;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;


final class ContactFormFactory
{
	use Nette\SmartObject;

	public function __construct(
		private MailSender $mailSender,
		private SettingsService $settingsService,
		private Translator $translator,
		private ContactFormRepository $contactFormRepository,
	)
	{
	}


	public function create(callable $onSuccess, callable $onError): Form
	{
		$form = new Form();

		$form->setTranslator($this->translator);

		$form->addEmail('email')
			->setRequired('Prosím vyplňte email')
		;

		$form->addText('name')
			->setRequired('Prosím vyplňte jméno')
		;

		$form->addText('phone')
		;

		$form->addText('subject')
			->setRequired('Prosím vyplňte předmět');

		$form->addTextArea('message')
			->setRequired('Prosím vyplňte zprávu');

		$form->addSubmit('send');

		$form->onSuccess[] = function (Form $form, array $data) use ($onSuccess): void {
			$succ = false;
			try {

				//send email
				$this->mailSender->sendEmail(
					'front',
					$this->settingsService['contact_email'],
					$data['subject'],
					'../app/AppModule/FrontModule/Presenters/templates/Emails/contact.latte',
					[
						'message' => str_replace("\n", '<br>', $data['message']),
						'data' => $data,
					],
					$data['email']
				);


				$model = $this->contactFormRepository->getModel();
				$model->fromForm($data)
					->insert();

				$succ = true;

			} catch (\Throwable $e) {
				Debugger::log($e);
				$form->addError('Nepodařilo se odeslat email');
				return;
			}

			$form->setValues([], true);

			$onSuccess($succ);
		};

		$form->onError[] = function (Form $form) use ($onError): void {
			$onError();
		};

		return $form;
	}
}