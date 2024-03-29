<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\MainModule\WWWPagesModule\Presenters;

use App\AppModule\AdminModule\MainModule\WWWPagesModule\Forms\BasicFormFactory;
use App\AppModule\AdminModule\MainModule\WWWPagesModule\Forms\ContentFormFactory;
use App\AppModule\AdminModule\MainModule\WWWPagesModule\Forms\SystemFormFactory;
use App\Libs\Repository\App\PageRepository;
use App\Libs\Service\App\CacheService;
use App\Libs\Utils\Utils;
use Nette\Application\UI\Form;
use Tracy\Debugger;

class DefaultPresenter extends \App\AppModule\AdminModule\MainModule\BasePresenter
{
	public function __construct(
		private PageRepository $pageRepository,
		private BasicFormFactory $basicFormFactory,
		private SystemFormFactory $systemFormFactory,
		private ContentFormFactory $contentFormFactory,
		private CacheService $cacheService,
	)
	{
	}
	/**
	 * @return Form
	 */
	protected function createComponentBasicForm(): Form
	{
		return $this->basicFormFactory->create(function (bool $succ): void {
			if ($succ) {
				$this->flashMessage($this->translator->translate(
					'Page has been successfully changed'),
					'success'
				);
			}
			$this->getPayload()->afterPageForm = true;
			$this->getPayload()->selectTab = '#basic';
			$this->redrawControl('flashMessages');
			$this->redrawControl('contentWrapper');
		}, (int) ($_POST['page'] ?? $this->getParameter('page')));
	}


	protected function createComponentSystemForm(): Form
	{
		return $this->systemFormFactory->create(function (bool $succ): void {
			if  ($succ) {
				$this->flashMessage($this->translator->translate(
					'Page has been successfully changed'),
					'success'
				);
			}
			$this->getPayload()->afterPageForm = true;
			$this->getPayload()->selectTab = '#system';
			$this->redrawControl('flashMessages');
			$this->redrawControl('contentWrapper');
		}, (int) ($_POST['page'] ?? $this->getParameter('page')));
	}

	protected function createComponentContentForm(): Form
	{
		return $this->contentFormFactory->create(function (bool $succ): void {
			if  ($succ) {
				$this->flashMessage($this->translator->translate(
					'Page content has been successfully changed'),
					'success'
				);
			}
			$this->getPayload()->afterPageForm = true;
			$this->getPayload()->selectTab = '#page-content';
			$this->redrawControl('flashMessages');
			$this->redrawControl('contentWrapper');
		}, (int) ($_POST['page'] ?? $this->getParameter('page')));
	}

	public function renderPages()
	{
		if ($this->isAjax()) {
			$id = (int) ($_POST['page'] ?? $this->getParameter('page'));
			$this->getTemplate()->page = $this->pageRepository->getByPk((int) $id);
			$this->getTemplate()->selectTab = $this->getParameter('selectTab') ?? 'basic';
			$this->getPayload()->test = 'test';
			$this->redrawControl('page');
			$this->redrawControl('contentWrapper');
		} elseif ($this->getParameter('page')) {
			$this->getTemplate()->page = $this->pageRepository->getByPk((int) $this->getParameter('page'));
		}
		$this->getTemplate()->selectTab = $this->getParameter('selectTab') ?? '#basic';
	}

	public function renderAddPage()
	{

		$parent = (int) $this->getParameter('parent');
		$title = $this->getParameter('title');
		$lang = $this->getParameter('lang');

		$page = clone $this->pageRepository->getModel();

		if ($parent > 0) {
			$parentPage = $this->pageRepository->getByPk($parent);

			$id = $page->set('id', null)
				->set('parent',$parent)
				->set('pointer','text_'.time())
				->set('menuname',$title)
				->set('title', $title)
				->setRank()
				->set('uri', Utils::nice_uri($title))
				->set('presenter', 'Textpage')
				->set('lang', $lang)
				->set('loggedUser',$parentPage->get('loggedUser')->getValue())
				->set('layout',$parentPage->get('layout')->getValue())
				->insert();

		} else {
			$id = $page->set('parent',$parent)
				->set('pointer','text_'.time())
				->set('menuname',$title)
				->set('title', $title)
				->setRank()
				->set('uri', Utils::nice_uri($title))
				->set('presenter', 'Textpage')
				->set('lang',$lang)
				->insert();
		}

		if ($this->isAjax()) {
			$this->flashMessage('Page was successfully added', 'success');
			$this->getPayload()->id = $id??0;
			$this->redrawControl('flashMessages');
			$this->redrawControl('contentWrapper');
		}
	}

	public function actionPagesTree()
	{
		$parentid = isset($_REQUEST['id'])?$_REQUEST['id']:0;
		$lang = isset($_REQUEST['lang'])?$_REQUEST['lang']:'en';


		$select = $this->pageRepository->getSelect()
			->addDeletedCond()
			->where(' and '.$this->pageRepository->getAlias('parent').'=%i',$parentid)
			->where(' and ( '.$this->pageRepository->getAlias('lang').'=%s or '.$this->pageRepository->getAlias('lang')."='uni' )",$lang)
			->orderBy('rank','ASC');

		$return = [];
		foreach ($select->fetchData() as $key => $page) {
			$obj = new \stdClass();
			$attr = new \stdClass();
			$attr->id = 'node_'.$page->get('id')->getValue();
			$attr->rel = 'page';
			$obj->attr = $attr;
			$obj->data = $page->get('menuName')->getValue();
			$obj->state = 'closed';
			$return []= $obj;
		}

		$this->sendJson($return);
	}

	public function actionDelete()
	{
		$id = (int) $this->getParameter('pageId');
		$page = $this->pageRepository->getByPk($id);
		$page->set('deleted', 1)->update();
		$this->cacheService->removeKey('pages-active-'.$page->get('lang')->getValue());
		$this->flashMessage('Page was successfully deleted', 'success');
		$this->getPayload()->id = $id;
		$this->redrawControl('flashMessages');
		$this->redrawControl('contentWrapper');
		$this->redirect('pages');
	}

	public function actionMove()
	{
		try {

			$this->pageRepository->getConn()->query("SET AUTOCOMMIT=0");
			$this->pageRepository->getConn()->query("START TRANSACTION");

			$currId = $this->getParameter('currItemId');



			$curr = $this->pageRepository->getByPk((int) $currId);


			$prevItemId = $this->getParameter('prevItemId');
			$nextItemId = $this->getParameter('nextItemId');
			if ($prevItemId !== 'none') {
				$prev = $this->pageRepository->getByPk((int) $prevItemId);
				$prev_rank = $prev->get('rank')->getValue();
				$prev_parentid = $prev->get('parent')->getValue();

			} elseif ($nextItemId !== 'none') {
				$next = $this->pageRepository->getByPk((int) $nextItemId);
				$prev = true;
				$prev_rank = 0;
				$prev_parentid = $next->get('parent')->getValue();
			}

			if (isset($prev)) {

				$this->pageRepository->getConn()->query(
					"update [" . $this->pageRepository->getTableRaw()
					. "] set [rank]=[rank]+1 where [rank] > %i and [parent]=%i",
					$prev_rank, $prev_parentid
				);

				$curr->set('rank', $prev_rank + 1)
					->set('parent', $prev_parentid)
					->update();
			} else {

				$this->pageRepository->getConn()->query(
					"update " . $this->pageRepository->getTableRaw()
					. " set [rank]=%i,[parent]=%i where [id]=%i",
					1, $this->getParameter('parentId'), $curr->get('id')->getValue()
				);
			}

			$this->pageRepository->getConn()->query("COMMIT");
			$this->pageRepository->getConn()->query("SET AUTOCOMMIT=1");


			$this->flashMessage('Page has been successfully moved', 'success');
			$this->redrawControl('flashMessages');
			$this->redrawControl('contentWrapper');
		} catch (\Throwable $e) {
			Debugger::log($e);
			$this->flashMessage('Server Error', 'danger');
			$this->redrawControl('flashMessages');
			$this->redrawControl('contentWrapper');
			$this->pageRepository->getConn()->query("ROLLBACK");
			$this->pageRepository->getConn()->query("SET AUTOCOMMIT=1");
		}
		if (!$this->isAjax()) {
			$this->redirect('pages');
		}
	}
}