<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\MainModule\WWWPagesModule\Forms;

use App\AppModule\AdminModule\Forms\FormFactory;
use App\Libs\Repository\App\PageRepository;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;


final class BasicFormFactory
{
	use Nette\SmartObject;

	public function __construct(
		private FormFactory    $factory,
		private PageRepository $pageRepository,
	)
	{

	}


	public function create(callable $onSuccess, int $id): Form
	{
		$page = $this->pageRepository->getByPk($id);
		$form = $this->factory->create();
		$form->addHidden('page', $id);
		$form->addText('title')
			->setDefaultValue($page['title']->getValue());
		$form->addText('menuname')
			->setDefaultValue($page['menuName']->getValue());
		$form->addTextArea('description')
			->setDefaultValue($page['description']->getValue());
		$form->addText('uri')
			->setDefaultValue($page['uri']->getValue());
		$form->addCheckbox('menu')
			->setDefaultValue((bool) $page['menu']->getValue());
		$form->addCheckbox('active')
			->setDefaultValue((bool) $page['active']->getValue());
		$form->addSubmit('send',);

		$form->onSuccess[] = function (Form $form, array $data) use ($onSuccess, $page): void {

			try {
				$page->fromForm($data);
				$validation = $page->validate();
				if ($validation->isSucc()) {
					$page->update();
				} elseif (count($validation->getErrors())) {
					foreach ($validation->getErrors() as $error) {
						$form->addError($error['mess']);
					}
					return;
				}
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