<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\MainModule\HomepageModule\Forms\Testimonial;

use App\AppModule\AdminModule\Forms\FormFactory;
use App\Libs\Model\TestimonialModel;
use App\Libs\Repository\TestimonialRepository;
use App\Libs\Service\App\CacheService;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;


final class EditFormFactory
{
	use Nette\SmartObject;

	public function __construct(
		private FormFactory      $factory,
		private TestimonialRepository $repository,
		private CacheService     $cacheService,
	)
	{

	}


	public function create(callable $onSuccess, int $id): Form
	{
		$model = $this->repository->getByPk($id);

		$form = $this->factory->create();
		$form->addHidden('id', $model->get('id')->getValue());

		$form->addCheckbox('active')
			->setDefaultValue((bool)$model->get('active')->getValue());

		$form->addText('name')
			->setDefaultValue($model->get('name')->getValue());

		$form->addText('position')
			->setDefaultValue($model->get('position')->getValue());

		$form->addTextArea('content')
			->setDefaultValue($model->get('content')->getValue());

		$form->addSubmit('send','Save',);

		$form->onSuccess[] = function (Form $form, array $data) use ($onSuccess, $model): void {
			$succ = false;
			try {
				$model->fromForm($data);
				//$validation = $model->validate(HPSliderModel::FORM_ACTION_EDIT);
				//if ($validation->isSucc()) {
				$model->update();
				$this->cacheService->removeKey(
					'testimonials-'.'-'.$model->get('lang')->getValue()
				);
				$succ = true;
				/*} elseif (count($validation->getErrors())) {
					foreach ($validation->getErrors() as $error) {
						$form->addError($error['mess']);
					}
				}
				*/
			} catch (\Throwable $e) {
				Debugger::log($e);
				$form->addError('Server Error');
			}

			$onSuccess($succ);
		};

		return $form;
	}

}