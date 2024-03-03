<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\MainModule\WWWPagesModule\Forms\Homepage\Slider;

use App\AppModule\AdminModule\Forms\FormFactory;
use App\Libs\Model\SliderModel;
use App\Libs\Repository\SliderRepository;
use App\Libs\Service\App\CacheService;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;


final class EditFormFactory
{
	use Nette\SmartObject;

	public function __construct(
		private FormFactory      $factory,
		private SliderRepository $repository,
		private CacheService     $cacheService,
	)
	{

	}


	public function create(callable $onSuccess, int $id): Form
	{
		$model = $this->repository->getByPk($id);
		$form = $this->factory->create();

		$form->addText('title')
			->setDefaultValue($model->get('title')->getValue());

		$form->addText('link')
			->setDefaultValue($model->get('link')->getValue());

		$form->addTextArea('perex')
			->setDefaultValue($model->get('perex')->getValue());

		$form->onSuccess[] = function (Form $form, array $data) use ($onSuccess, $model): void {
			$succ = false;
			try {
				$model->fromForm($data);
				$validation = $model->validate(SliderModel::FORM_ACTION_EDIT);
				if ($validation->isSucc()) {
					$model->update();
					$this->cacheService->removeKey('hpsliders-'.$model->get('lang')->getValue());
					$succ = true;
				} elseif (count($validation->getErrors())) {
					foreach ($validation->getErrors() as $error) {
						$form->addError($error['mess']);
					}
				}
			} catch (\Throwable $e) {
				Debugger::log($e);
				$form->addError('Server Error');
			}

			$onSuccess($succ);
		};

		return $form;
	}

}