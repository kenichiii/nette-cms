<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\UserModule\Forms;

use App\AppModule\AdminModule\Forms\FormFactory;
use App\Libs\Service\App\SettingsService;
use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;


final class SignInFormFactory
{
	use Nette\SmartObject;

	public function __construct(
		private FormFactory $factory,
		private User $user,
		private SettingsService $settings
	)
	{
	}


	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();
		$form->addText('username')
			->setRequired('sign.in.form.username.error');

		$form->addPassword('password')
			->setRequired('sign.in.form.password.error');

		$form->addCheckbox('remember');

		$form->addSubmit('send');

		$form->onSuccess[] = function (Form $form, \stdClass $data) use ($onSuccess): void {
			try {
				$this->user->setExpiration($data->remember
					? $this->settings['admin_user_expiration_pernament']
					: $this->settings['admin_user_expiration_default']
				);
				$this->user->login($data->username, $data->password);
			} catch (Nette\Security\AuthenticationException $e) {
				if ($e->getCode() === 400) {
					$form->addError('Server Error');
				} else {
					$form->addError('Invalid Credentials');
				}
				return;
			}
			$onSuccess();
		};

		return $form;
	}
}
