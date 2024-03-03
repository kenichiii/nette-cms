<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\MainModule\UsersModule\Forms;

use App\AppModule\AdminModule\Forms\FormFactory;
use App\Libs\Repository\App\UserRepository;
use App\Libs\Service\App\MailSender;
use App\Libs\Service\App\SettingsService;
use App\Libs\Service\App\Translator;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;


final class NewUserFormFactory
{
	use Nette\SmartObject;

	public function __construct(
		private FormFactory    $factory,
		private UserRepository $repository,
		private MailSender $mailSender,
		private Translator $translator,
		private SettingsService $settings,
	)
	{

	}


	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();;

		$form->addEmail('email')
			->setRequired('Email cant be empty');

		$form->addText('name');

		$form->addText('phone');

		$form->addText('role');

		$form->addText('roles')
			->setDefaultValue('["user","admin"]')
			->setRequired('Roles cant be empty');

		$form->addSubmit('send','Save');

		$form->onSuccess[] = function (Form $form, array $data) use ($onSuccess): void {
			$succ = false;
			try {
				$user = $this->repository->getModel();
				$user->fromForm($data);
				//$validation = $user->validate(UserModel::FORM_ACTION_NEW);
				//if ($validation->isSucc()) {
				$token = bin2hex(random_bytes(10)) . $user['id'];
				$expire = time() + (intval($this->settings['admin_user_registration_password_token_expiration'])
						* 60 * 60 * 24);

				$user->set('forgottenPasswordToken', $token)
					->set('forgottenPasswordTokenExpiration', $expire)
					;

					$user->insert();

             try {
				 $this->mailSender->sendEmail(
					 'admin',
					 $data['email'],
					 $this->translator->translate('New account registered'),
					 '../app/AppModule/AdminModule/MainModule/UsersModule/Presenters/templates/Default/Email/newregistration.latte',
					 [
						 'token' => $token,
						 'lang' => $this->translator->getLang(),
						 'sitename' => $this->settings['site_name'],
						 'expiration' => date('d.m.Y G:i', $expire),
					 ],
				 );
				 $succ = true;
				 $form->setDefaults([], true);
			 } catch (\Throwable $e) {
				 $form->addError('Cant sent email. User has been created');
			 }

			//	} elseif (count($validation->getErrors())) {
			//		foreach ($validation->getErrors() as $error) {
			//			$form->addError($error['mess']);
			//		}

			//	}

			} catch (\Throwable $e) {
				Debugger::log($e);
				$form->addError('Server Error');
			}

			$onSuccess($succ);
		};

		return $form;
	}
}