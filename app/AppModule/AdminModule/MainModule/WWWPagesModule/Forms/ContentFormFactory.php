<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\MainModule\WWWPagesModule\Forms;

use App\AppModule\AdminModule\Forms\FormFactory;
use App\Libs\Kenichi\ORM\Model;
use App\Libs\Repository\App\PageRepository;
use App\Libs\Service\App\CacheService;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;


final class ContentFormFactory
{
	use Nette\SmartObject;

	public function __construct(
		private FormFactory    $factory,
		private PageRepository $pageRepository,
		private CacheService $cacheService,
	)
	{

	}


	public function create(callable $onSuccess,int $id): Form
	{

		$page = $this->pageRepository->getByPk($id);
		$form = $this->factory->create();
		$form->addHidden('page', $id);
		$form->addTextArea('content');

		$form->addSubmit('send', 'Save');

		$form->onSuccess[] = function (Form $form, array $data) use ($onSuccess, $page): void {

			//try {
			$succ = false;
			$page->fromForm($data);
			$page->update();
			$this->cacheService->removeKey('page-content'.$page->get('id')->getValue());
			$succ = true;
			$onSuccess($succ);
		};

		return $form;
	}
}