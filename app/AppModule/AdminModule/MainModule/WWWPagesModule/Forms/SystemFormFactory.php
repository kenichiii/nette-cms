<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\MainModule\WWWPagesModule\Forms;

use App\AppModule\AdminModule\Forms\FormFactory;
use App\Libs\Repository\App\PageRepository;
use Nette;
use Nette\Application\UI\Form;


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


		$form->addSubmit('send',);

		$form->onSuccess[] = function (Form $form, array $data) use ($onSuccess, $page): void {

			//try {
				$page->fromForm($data);
				$page->update();
/*
			} catch (\Throwable $e) {
				Debugger::log($e);
				$form->addError('Server Error');
				return;
			}
*/
			$onSuccess();
		};

		return $form;
	}
}