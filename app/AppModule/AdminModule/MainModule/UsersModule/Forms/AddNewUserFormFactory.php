<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\MainModule\UsersModule\Forms;

use App\AppModule\AdminModule\Forms\FormFactory;
use App\Libs\Model\App\UserModel;
use App\Libs\Repository\App\UserRepository;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;


final class AddNewUserFormFactory
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
		$form = $this->factory->create();
		$form->addSubmit('send',);

		$form->onSuccess[] = function (Form $form, array $data) use ($onSuccess): void {

			try {
				$user = $this->repository->getModel();
				$user->fromForm($data);
				$validation = $user->validate(UserModel::FORM_ACTION_NEW);
				if ($validation->isSucc()) {
					$page->update();
				} elseif (count($validation->getErrors())) {
					foreach ($validation->getErrors() as $error) {
						$form->addError($error['mess']);
					}
					return;
				}
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