<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\MainModule\UserModule\Forms;

use App\AppModule\AdminModule\Forms\FormFactory;
use App\Libs\Model\App\UserModel;
use App\Libs\Repository\App\UserRepository;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;


final class AccountSettingsFormFactory
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
		$user = $this->repository->getByPk($this->user->getId());
		$form = $this->factory->create();

		$form->addEmail('email')
			->setRequired('Email cant be empty')
			->setDefaultValue($user->get('email')->getValue());

		$form->addText('name')
			->setDefaultValue($user->get('name')->getValue());

		$form->addText('phone')
			->setDefaultValue($user->get('phone')->getValue());

		$form->addSubmit('send',);

		$form->onSuccess[] = function (Form $form, array $data) use ($onSuccess, $user): void {

			try {
				$user->fromForm($data);
				//$validation = $user->validate(UserModel::FORM_ACTION_EDIT);
				//if ($validation->isSucc()) {
					$user->update();

				/*} elseif (count($validation->getErrors())) {
					foreach ($validation->getErrors() as $error) {
						$form->addError($error['mess']);
					}
					return;
				}
				*/
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