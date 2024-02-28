<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\UserModule\Forms;

use App\AppModule\Forms\FormFactory;
use App\Libs\Exception\Service\App\User\UserNotFoundException;
use App\Libs\Exception\Service\App\User\UserServiceException;
use App\Libs\Repository\App\UserRepository;
use Nette;

final class SignRenewPasswordFormFactory
{
	use Nette\SmartObject;

	public function __construct(
		private FormFactory $factory,
		private UserRepository $userRepository,
		private Nette\Http\Session $session,
	)
	{
	}


	public function create(callable $onSuccess): Nette\Application\UI\Form
	{
		$form = $this->factory->create();

		$form->addPassword('password', )
			->setOption('description', $form->getTranslator()->translate(
				'at least [:minChars:] chars',
				minChars: $this->userRepository->getModel()['password']->getMinLength()
			))
			->setRequired('sign.up.form.password.error')
			->addRule($form::MIN_LENGTH, null, $this->userRepository->getModel()['password']->getMinLength());

		$form->addPassword('passwordagain')
			->setRequired('sign.up.form.passwordAgain.error');

		$form->addSubmit('send');

		$form->onSuccess[] = function (Nette\Application\UI\Form $form, \stdClass $data) use ($onSuccess): void {

			if ($data->password !== $data->passwordagain) {
				$form->addError('Passwords are different.');
				return;
			}

			try {
				$session = $this->session->getSection('_renewPassword');
				$token = $session->get('token');
				$session->remove('token');
				$user = $this->userRepository->getByForgottenPasswordToken($token);
				if (!$user) {
					$form->addError('Not valid token');
					return;
				}
				$user->set('password', $data->password)
					->update();
			} catch (UserServiceException $e) {
				$form->addError('Server Error');
				return;
			}

			$onSuccess();
		};

		return $form;
	}
}