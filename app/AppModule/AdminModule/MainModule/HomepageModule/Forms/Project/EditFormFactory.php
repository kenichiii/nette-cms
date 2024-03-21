<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\MainModule\HomepageModule\Forms\Project;

use  App\AppModule\AdminModule\Forms\FormFactory;
use App\Libs\Model\ProjectModel;
use App\Libs\Repository\ProjectRepository;
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
		private ProjectRepository $repository,
		private CacheService     $cacheService,
		private TestimonialRepository $testimonialRepository,
	)
	{

	}


	public function create(callable $onSuccess, int $id): Form
	{
		$model = $this->repository->getByPk($id);

		$testimonials = $this->testimonialRepository->getSelect()
			->addDeletedCond()->addLangCond($model['lang']->getValue())
			->fetchData();
		$testimonialsValues = ["" => "---"];
		foreach ($testimonials as $testimonial) {
			$testimonialsValues [$testimonial['id']->getValue()] = $testimonial['name'].' - '.$testimonial['position'];
		}

		$form = $this->factory->create();
		$form->addHidden('id', $model->get('id')->getValue());

		$form->addSelect('section','', ProjectModel::SECTION)
			->setDefaultValue($model->get('section')->getValue());

		$form->addSelect('testimonial','', $testimonialsValues)
			->setDefaultValue($model->get('testimonial')->getValue() ?: '');


		$form->addText('title')
			->setRequired('Title cant be empty')
			->setDefaultValue($model->get('title')->getValue());


		$form->addText('uri')
			->setRequired('Uri cant be empty')
			->setDefaultValue($model->get('uri')->getValue());

		$form->addText('description')
			->setDefaultValue($model->get('description')->getValue());

		$form->addTextArea('content');

		$form->addCheckbox('active')
			->setDefaultValue((bool)$model->get('active')->getValue());


		$form->addSubmit('send','Save',);

		$form->onSuccess[] = function (Form $form, array $data) use ($onSuccess, $model): void {
			$succ = false;
			try {
				$model->fromForm($data);
				//$validation = $model->validate(HPSliderModel::FORM_ACTION_EDIT);
				//if ($validation->isSucc()) {
				$model->update();
				$this->cacheService->removeKey('project-'.$model['uri']);
				$this->cacheService->removeKey('projects-'.$model['lang']);
				$succ = true;
				/*} elseif (count($validation->getErrors())) {
					foreach ($validation->getErrors() as $error) {
						$form->addError($error['mess']);
					}
				}
				*/
			} catch (\Throwable $e) {
				Debugger::log($e);
				$form->addError('Server Error'.$e->getMessage());
			}

			$onSuccess($succ);
		};

		return $form;
	}

}