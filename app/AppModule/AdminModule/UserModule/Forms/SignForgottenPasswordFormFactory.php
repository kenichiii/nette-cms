<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\UserModule\Forms;

use App\AppModule\Forms\FormFactory;
use App\Libs\Exception\Service\App\User\UserNotFoundException;
use App\Libs\Exception\Service\App\User\UserServiceException;
use App\Libs\Facade\UserFacade;
use App\Libs\Service\App\AdminTranslator;
use App\Libs\Service\App\SettingsService;
use App\Libs\Service\MailSender;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;


final class SignForgottenPasswordFormFactory
{
	use Nette\SmartObject;

	public function __construct(
		private FormFactory $factory,
		private UserFacade $userFacade,
		private MailSender $mailSender,
		private SettingsService $settings,
		private AdminTranslator $translator,
	)
	{
	}


	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();

		$form->addEmail('username', 'sign.up.form.email.label')
			->setRequired('sign.up.form.email.error');

		$form->addSubmit('send', 'sign.forgottenPassword.form.submit');

		$form->onSuccess[] = function (Form $form, \stdClass $data) use ($onSuccess): void {

			try {
				$user = $this->userFacade->getUser($data->username);
			} catch (UserNotFoundException $e) {
				$form->addError('sign.forgottenPassword.form.error.nonExistingUser');
				return;
			} catch (UserServiceException $e) {
				$form->addError('exception.remoteServer');
				return;
			}

			try {
				//generate new token and its expiration in seconds
				$token = bin2hex(random_bytes(10)) . $user['id'];
				$expire = time() + (intval($this->settings->getSettings()['admin_user_forgotten_password_token_expiration']) * 60);

				$this->userFacade->update($user['uniqueHash'], [
					'forgottenPasswordToken' => $token,
					'forgottenPasswordTokenExpiration' => $expire,
				]);

				//send email
				$this->mailSender->sendEmail(
					$data->username,
					$this->translator->translate('email.forgottenPassword.subject'),
					'../app/Presenters/templates/Emails/forgottenPassword.latte',
					[
						'token' => $token,
						'lang' => $this->translator->getLang(),
						'sitename' => $this->translator->translate('sitename'),
						'expiration' => $this->settings->getSettings()['admin_user_forgotten_password_token_expiration']
					],
				);



			} catch (UserServiceException $e) {
				$form->addError('exception.remoteServer');
				return;
			} catch (\Throwable $e) {
				Debugger::log($e);
				$form->addError('exception.runtimeException');
				return;
			}

			$onSuccess();
		};

		return $form;
	}
}