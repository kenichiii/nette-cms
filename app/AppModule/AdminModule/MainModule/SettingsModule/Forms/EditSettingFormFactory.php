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


final class EditSettingFormFactory
{
	use Nette\SmartObject;

	public function __construct(
		private FormFactory    $factory,
		private SettingsRepository $repository,
	)
	{

	}


	public function create(callable $onSuccess, int $id): Form
	{
		$model = $this->repository->getByPk($id);
		$form = $this->factory->create();

		$form->addText('pointer')
			->setRequired('Pointer cant be empty')
			->setDefaultValue($model->get('pointer')->getValue());

		$form->addText('info')
			->setDefaultValue($model->get('info')->getValue());


		$form->addText('value')
			->setDefaultValue($model->get('value')->getValue());

		$form->addSubmit('send','Save',);

		$form->onSuccess[] = function (Form $form, array $data) use ($onSuccess, $model): void {
			$succ = false;
			try {

				$model->fromForm($data);

				//$validation = $user->validate(UserModel::FORM_ACTION_EDIT);
				//if ($validation->isSucc()) {
					$model->update();
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