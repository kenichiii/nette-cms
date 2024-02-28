<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\MainModule\WWWPagesModule\Forms;

use App\AppModule\AdminModule\Forms\FormFactory;
use App\Libs\Kenichi\ORM\Model;
use App\Libs\Repository\App\PageRepository;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;


final class SystemFormFactory
{
	use Nette\SmartObject;

	public function __construct(
		private FormFactory    $factory,
		private PageRepository $pageRepository,
	)
	{

	}


	public function create(callable $onSuccess,int $id): Form
	{

		$page = $this->pageRepository->getByPk($id);
		$form = $this->factory->create();
		$form->addHidden('page', $id);
		$form->addText('pointer')
			->setDefaultValue($page['pointer']->getValue());
		$form->addText('presenter')
			->setDefaultValue($page['presenter']->getValue());
		$form->addText('action')
			->setDefaultValue($page['action']->getValue());
		$form->addText('layout')
			->setDefaultValue($page['layout']->getValue());
		$form->addText('loggeduser')
			->setDefaultValue((bool) $page['loggedUser']->getValue());

		$form->addSubmit('send',);

		$form->onSuccess[] = function (Form $form, array $data) use ($onSuccess, $page): void {

			//try {
				$page->fromForm($data);
				$validation = $page->validate(Model::FORM_ACTION_EDIT);
				if ($validation->isSucc()) {
					$page->update();
				} elseif (count($validation->getErrors())) {
					foreach ($validation->getErrors() as $error) {
						$form->addError($error['mess']);
					}
					return;
				}
/*
			} catch (\Throwable $e) {
				Debugger::log($e);
				$form->addError('Server Error');
				return;
			}*/

			$onSuccess();
		};

		return $form;
	}
}