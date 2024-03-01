<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\MainModule\UsersModule\Forms;

use App\AppModule\AdminModule\Forms\FormFactory;
use App\Libs\Model\App\UserModel;
use App\Libs\Repository\App\UserRepository;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;


final class NewUserFormFactory
{
	use Nette\SmartObject;

	public function __construct(
		private FormFactory    $factory,
		private UserRepository $repository,
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
			->setDefaultValue('["user","admin"]');

		$form->addSubmit('send',);

		$form->onSuccess[] = function (Form $form, array $data) use ($onSuccess): void {
			$succ = false;
			try {
				$user = $this->repository->getModel();
				$user->fromForm($data);
				//$validation = $user->validate(UserModel::FORM_ACTION_NEW);
				//if ($validation->isSucc()) {
					$user->insert();
					$succ = true;

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