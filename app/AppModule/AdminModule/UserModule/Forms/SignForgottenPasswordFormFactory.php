<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\UserModule\Forms;

use App\AppModule\AdminModule\Forms\FormFactory;
use App\Libs\Exception\Service\App\User\UserNotFoundException;
use App\Libs\Exception\Service\App\User\UserServiceException;
use App\Libs\Repository\App\UserRepository;
use App\Libs\Service\App\SettingsService;
use App\Libs\Service\App\Translator;
use App\Libs\Service\App\UserService;
use App\Libs\Service\MailSender;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;


final class SignForgottenPasswordFormFactory
{
	use Nette\SmartObject;

	public function __construct(
		private FormFactory $factory,
		private UserService $userService,
		private MailSender $mailSender,
		private SettingsService $settings,
		private Translator $translator,
		private UserRepository $userRepository,
	)
	{
		$this->translator->setSection('admin');
	}


	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();

		$form->addEmail('username')
			->setRequired('sign.up.form.email.error');

		$form->addSubmit('send', );

		$form->onSuccess[] = function (Form $form, \stdClass $data) use ($onSuccess): void {

			try {
				$user = $this->userService->getUser($data->username);
			} catch (UserNotFoundException $e) {
				$form->addError('Non existing user');
				return;
			} catch (UserServiceException $e) {
				$form->addError('Server Error');
				return;
			}

			try {
				//generate new token and its expiration in seconds
				$token = bin2hex(random_bytes(10)) . $user['id'];
				$expire = time() + (intval($this->settings['admin_user_forgotten_password_token_expiration']) * 60);

				$user->set('forgottenPasswordToken', $token)
					->set('forgottenPasswordTokenExpiration', $expire)
					->setRepository($this->userRepository)
					->update();

				//send email
				$this->mailSender->sendEmail(
					'admin',
					$data->username,
					$this->translator->translate('Forgotten Password'),
					'../app/AppModule/AdminModule/UserModule/Presenters/templates/SignIn/Email/forgottenPassword.latte',
					[
						'token' => $token,
						'lang' => $this->translator->getLang(),
						'sitename' => $this->settings['site_name'],
						'expiration' => $this->settings['admin_user_forgotten_password_token_expiration']
					],
				);

			} catch (\Throwable $e) {
				Debugger::log($e);
				$form->addError('Server Error');
				return;
			}

			$onSuccess();
		};

		return $form;
	}
}