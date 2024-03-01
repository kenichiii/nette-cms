<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\MainModule\UserModule\Forms;

use App\AppModule\AdminModule\Forms\FormFactory;
use App\Libs\Model\App\UserModel;
use App\Libs\Repository\App\UserRepository;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;


final class ChangePasswordFormFactory
{
	use Nette\SmartObject;

	public function __construct(
		private FormFactory    $factory,
		private UserRepository $repository,
		private Nette\Security\User $user,
	)
	{

	}


	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();

		$form->addPassword('password')
			->setRequired('Password cant be empty');

		$form->addPassword('passwordagain');

		$form->addPassword('old')
			->setRequired('Old Password cant be empty');

		$form->addSubmit('send',);

		$form->onSuccess[] = function (Form $form, array $data) use ($onSuccess): void {

			try {
				$user = $this->repository->getByPk($this->user->getId());
				$succ = false;
				if ($data['password'] !== $data['passwordagain']) {
					$form->addError('Different Passwords');
				} elseif ($user->get('password')->getValue() !== $user->get('password')::encode($data['old'])) {
					$form->addError('Not valid old password');
				} else {
					$user->fromForm($data);
					$user->update();
					$succ = true;
				}



			} catch (\Throwable $e) {
				Debugger::log($e);
				$form->addError('Server Error');
				return;
			}

			$onSuccess($succ);
		};

		return $form;
	}
}