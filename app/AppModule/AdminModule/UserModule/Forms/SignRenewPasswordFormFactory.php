<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\UserModule\Forms;

use App\Libs\Exception\User\UserNotFoundException;
use App\Libs\Exception\User\UserServiceException;
use App\Libs\Model;
use App\Libs\Model\Service\Auth\ExternalAuth;
use Nette;
use Nette\Application\UI\Form;



final class SignRenewPasswordFormFactory
{
	use Nette\SmartObject;

	public function __construct(
		private FormFactory $factory,
		private Model\UserFacade $userFacade,
		private Nette\Http\Session $session,
	)
	{
	}


	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();

		$form->addPassword('password', 'sign.up.form.password.label')
			->setOption('description', $form->getTranslator()->translate(
				'sign.up.form.password.option',
				minChars: $this->userFacade::PasswordMinLength
			))
			->setRequired('sign.up.form.password.error')
			->addRule($form::MIN_LENGTH, null, $this->userFacade::PasswordMinLength);

		$form->addPassword('passwordagain', 'sign.up.form.passwordAgain.label')
			->setRequired('sign.up.form.passwordAgain.error');

		$form->addSubmit('send', 'sign.renewPassword.form.submit');

		$form->onSuccess[] = function (Form $form, \stdClass $data) use ($onSuccess): void {

			if ($data->password !== $data->passwordagain) {
				$form->addError('sign.up.form.error.diffrentPasswords');
				return;
			}

			try {
				$session = $this->session->getSection('_renewPassword');
				$token = $session->get('token');
				$session->remove('token');
				$user = $this->userFacade->getUserByForgottenPasswordToken($token);
			} catch (UserNotFoundException | UserServiceException $e) {
				$form->addError('exception.remoteServer');
				return;
			}

			try {
				$this->userFacade->update($user['uniqueHash'],[
					'password' => $data->password,
				]);
			} catch (UserServiceException $e) {
				$form->addError('exception.remoteServer');
				return;
			}

			$onSuccess();
		};

		return $form;
	}
}