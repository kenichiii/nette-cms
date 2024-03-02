<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\MainModule\SettingsModule\Forms;

use App\AppModule\AdminModule\Forms\FormFactory;
use App\Libs\Model\App\UserModel;
use App\Libs\Repository\App\SettingsRepository;
use App\Libs\Repository\App\UserRepository;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;


final class AddNewSettingFormFactory
{
	use Nette\SmartObject;

	public function __construct(
		private FormFactory    $factory,
		private SettingsRepository $repository,
	)
	{

	}


	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();;

		$form->addText('pointer')
			->setRequired('Pointer cant be empty');

		$form->addText('info');

		$form->addText('value');;

		$form->addSubmit('send','Save',);

		$form->onSuccess[] = function (Form $form, array $data) use ($onSuccess): void {
			$succ = false;
			try {
				$model = $this->repository->getModel();
				$model->fromForm($data);
				//$validation = $user->validate(UserModel::FORM_ACTION_NEW);
				//if ($validation->isSucc()) {
					$model->insert();
					$succ = true;

			//	} elseif (count($validation->getErrors())) {
			//		foreach ($validation->getErrors() as $error) {
			//			$form->addError($error['mess']);
			//		}

			//	}
					$form->setDefaults([], true);
			} catch (\Throwable $e) {
				Debugger::log($e);
				$form->addError('Server Error');
			}

			$onSuccess($succ);
		};

		return $form;
	}
}