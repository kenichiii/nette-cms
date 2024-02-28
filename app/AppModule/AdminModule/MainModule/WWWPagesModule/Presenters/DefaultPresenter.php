<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\MainModule\WWWPagesModule\Presenters;

use App\AppModule\AdminModule\MainModule\WWWPagesModule\Forms\BasicFormFactory;
use App\AppModule\AdminModule\MainModule\WWWPagesModule\Forms\SystemFormFactory;
use App\Libs\Repository\App\PageRepository;
use App\Libs\Utils\Utils;
use Nette\Application\UI\Form;

class DefaultPresenter extends \App\AppModule\AdminModule\MainModule\BasePresenter
{
	public function __construct(
		private PageRepository $pageRepository,
		private BasicFormFactory $basicFormFactory,
		private SystemFormFactory $systemFormFactory,
	)
	{
	}
	/**
	 * @return Form
	 */
	protected function createComponentBasicForm(): Form
	{
		return $this->basicFormFactory->create(function (): void {
			$this->flashMessage($this->translator->translate(
				'Page has been successfully changed'),
				'success'
			);
			$this->getPayload()->afterPageForm = true;
			$this->redrawControl('flashMessages');
			$this->redrawControl('contentWrapper');
		}, (int) ($_POST['page'] ?? $this->getParameter('page')));
	}


	protected function createComponentSystemForm(): Form
	{
		return $this->systemFormFactory->create(function (): void {
			$this->flashMessage($this->translator->translate(
				'Page has been successfully changed'),
				'success'
			);
			$this->getPayload()->afterPageForm = true;
			$this->redrawControl('flashMessages');
			$this->redrawControl('contentWrapper');
		}, (int) ($_POST['page'] ?? $this->getParameter('page')));
	}

	public function renderPages()
	{
		if ($this->isAjax()) {
			$id = (int) ($_POST['page'] ?? $this->getParameter('page'));
			$this->getTemplate()->page = $this->pageRepository->getByPk((int) $id);
			$this->getPayload()->test = 'test';
			$this->redrawControl('page');
			$this->redrawControl('contentWrapper');
		} elseif ($this->getParameter('page')) {
			$this->getTemplate()->page = $this->pageRepository->getByPk((int) $this->getParameter('page'));
		}
	}

	public function actionAddPage()
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
				//->set('access',$parentPage->getAccess()->getValue())
				//->set('layoutname',$parentPage->getLayoutname()->getValue())
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
			$this->getPayload()->id = $id;
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
			->orderBy($this->pageRepository->getAlias('rank').' ASC');

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
		$this->flashMessage('Page was successfully deleted', 'success');
		$this->getPayload()->id = $id;
		$this->redrawControl('flashMessages');
		$this->redrawControl('contentWrapper');
		$this->redirect('pages');
	}

	public function actionContent()
	{
		$page = $this->pageRepository->getByPk((int)$_POST['page']);
		$page->set('content', $_POST['content']);
		$page->update();
		$this->flashMessage($this->translator->translate(
			'Page has been successfully changed'),
			'success'
		);
		$this->redirect('pages',['page' => $_POST['page']]);
	}

	public function actionMove()
	{
		try {

			$this->pageRepository->getConn()->query("SET AUTOCOMMIT=0");
			$this->pageRepository->getConn()->query("START TRANSACTION");


			$curr = clone $this->pageRepository->getByPk((int) $this->getParameter('currItemId'));
			$prevItemId = $this->getParameter('prevItemId');
			$nextItemId = $this->getParameter('nextItemId');
			if ($prevItemId !== 'none') {
				$prev = clone $this->pageRepository->getByPk((int) $prevItemId);
				$prev_rank = $prev->get('rank')->getValue();
				$prev_parentid = $prev->get('parent')->getValue();
			} elseif ($nextItemId !== 'none') {
				$next = clone $this->pageRepository->getByPk((int) $nextItemId);
				$prev = true;
				$prev_rank = 0;
				$prev_parentid = $next->get('parent')->getValue();
			}

			if (isset($prev)) {

				$this->pageRepository->getConn()->query(
					"update " . $this->pageRepository->getTableRaw()
					. " set rank=rank+1 where rank > %i and parent=%i",
					$prev_rank, $prev_parentid
				);

				$this->pageRepository->getConn()->query(
					"update " . $this->pageRepository->getTableRaw()
					. " set rank=%i,parent=%i where id=%i",
					$prev_rank + 1, $prev_parentid, $curr->get('id')->getValue()
				);
			} else {

				$this->pageRepository->getConn()->query(
					"update " . $this->pageRepository->getTableRaw()
					. " set rank=%i,parent=%i where id=%i",
					1, $this->getParameter('parentId'), $curr->get('id')->getValue()
				);
			}

			$this->pageRepository->getConn()->query("COMMIT");
			$this->pageRepository->getConn()->query("SET AUTOCOMMIT=1");


			$this->flashMessage('Page has been successfully moved', 'success');
			$this->redrawControl('flashMessages');
			$this->redrawControl('contentWrapper');
		} catch (\Throwable $e) {

			$this->flashMessage('Server Error', 'danger');
			$this->redrawControl('flashMessages');
			$this->redrawControl('contentWrapper');
			$this->pageRepository->getConn()->query("ROLLBACK");
			$this->pageRepository->getConn()->query("SET AUTOCOMMIT=1");
		}
		$this->redirect('pages', ['page' => $curr->get('id')->getValue()]);
	}
}