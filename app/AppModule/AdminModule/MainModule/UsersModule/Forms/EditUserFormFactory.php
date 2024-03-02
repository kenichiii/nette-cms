<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\MainModule\UsersModule\Forms;

use App\AppModule\AdminModule\Forms\FormFactory;
use App\Libs\Model\App\UserModel;
use App\Libs\Repository\App\UserRepository;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;


final class EditUserFormFactory
{
	use Nette\SmartObject;

	public function __construct(
		private FormFactory    $factory,
		private UserRepository $repository,
	)
	{

	}


	public function create(callable $onSuccess, int $id): Form
	{
		$user = $this->repository->getByPk($id);
		$form = $this->factory->create();

		$form->addEmail('email')
			->setRequired('Email cant be empty')
			->setDefaultValue($user->get('email')->getValue());

		$form->addText('name')
			->setDefaultValue($user->get('name')->getValue());

		$form->addText('phone')
			->setDefaultValue($user->get('phone')->getValue());

		$form->addText('role')
			->setDefaultValue($user->get('role')->getValue());

		$form->addText('roles')
			->setDefaultValue($user->get('roles')->getValue())
			->setRequired('Roles cant be empty');

		$form->addSubmit('send', 'Save',);

		$form->onSuccess[] = function (Form $form, array $data) use ($onSuccess, $user): void {
			$succ = false;
			try {

				$user->fromForm($data);

				//$validation = $user->validate(UserModel::FORM_ACTION_EDIT);
				//if ($validation->isSucc()) {
					$user->update();
					$succ = true;
				/*} elseif (count($validation->getErrors())) {
					foreach ($validation->getErrors() as $error) {
						$form->addError($error['mess']);
					}
				}*/
			} catch (\Throwable $e) {
				Debugger::log($e);
				$form->addError('Server Error');
			}

			$onSuccess($succ);
		};

		return $form;
	}
}