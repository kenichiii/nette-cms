<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\MainModule\HomepageModule\Forms\Slider;

use App\AppModule\AdminModule\Forms\FormFactory;
use App\Libs\Repository\SliderRepository;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;


final class AddFormFactory
{
	use Nette\SmartObject;

	public function __construct(
		private FormFactory      $factory,
		private SliderRepository $repository,
	)
	{

	}


	public function create(string $section, string $lang, callable $onSuccess): Form
	{
		$form = $this->factory->create();

		$form->addHidden('lang', $lang);
		$form->addHidden('section', $section);

		$form->addText('title');

		$form->addText('link');
		$form->addText('linktext');

		$form->addTextArea('perex');

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