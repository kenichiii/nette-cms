<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\MainModule\HomepageModule\Forms\Project;

use App\AppModule\AdminModule\Forms\FormFactory;
use App\Libs\Model\ProjectModel;
use App\Libs\Repository\ProjectRepository;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;


final class AddFormFactory
{
	use Nette\SmartObject;

	public function __construct(
		private FormFactory      $factory,
		private ProjectRepository $repository,
	)
	{

	}


	public function create(string $lang, callable $onSuccess): Form
	{
		$form = $this->factory->create();

		$form->addHidden('lang', $lang);
		$form->addText('title')
			->setRequired('Title cant be empty');
		$form->addText('uri')
			->setRequired('Uri cant be empty');

		$form->addSelect('section','', ProjectModel::SECTION);

		$form->addText('description');

		$form->addSubmit('send','Save',);

		$form->onSuccess[] = function (Form $form, array $data) use ($onSuccess): void {
			$succ = false;
			try {
				$model = $this->repository->getModel();
				$model->fromForm($data);
				//$validation = $user->validate(UserModel::FORM_ACTION_NEW);
				//if ($validation->isSucc()) {
				$model->setRank();
				$id = $model->insert();
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

			$onSuccess($succ, $id);
		};

		return $form;
	}
}