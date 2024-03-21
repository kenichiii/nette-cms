<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\MainModule\ContactFormModule\Presenters;

use App\AppModule\AdminModule\MainModule\Components\Datagrid\Datagrid;
use App\AppModule\AdminModule\MainModule\Components\Datagrid\DatagridFactory;
use App\Libs\Repository\ContactFormRepository;
use App\Libs\Utils\Utils;

class DefaultPresenter extends \App\AppModule\AdminModule\MainModule\BasePresenter
{
	public function __construct(
		private DatagridFactory  $datagridFactory,
		private ContactFormRepository $repository,
	)
	{
	}

	public function renderDefault(?int $id)
	{
		if ($id) {
			$show = $this->getParameter('show') ?: ($_POST['show'] ?? 'view');
			$model = $this->repository->getByPk($id);
			$this->getTemplate()->model = $model;
			$this->getTemplate()->show = $show;

			$this->getPayload()->showModal = "#{$show}FormModal";
			$this->redrawControl($show. 'Modal');
		}
	}

	public function actionDelete(int $id)
	{
		//if ($this->isAjax()) {
		$model = $this->repository->getByPk($id);
		$model->set('deleted', 1)->update();
		$this->flashMessage('Record was successfully deleted', 'success');
		$this->redrawControl('flashMessages');
		$this->redrawControl('contentWrapper');
		//}
		$this->redirect('default');
	}


	public function createComponentGrid(): Datagrid
	{
		return $this->datagridFactory->create(
			$this->repository,
			[
				'conditions' => [
					[
						'column' => 'deleted',
						'op' => '=',
						'value' => 0,
					],
				],
				'columns' => [
					'created' => [
						'title' => $this->translator->translate('Date'),
						'type' => 'datetime',
					],
					'subject' => [
						'title' => $this->translator->translate('Subject'),
					],
					'name' => [
						'title' => $this->translator->translate('Name'),
					],
					'email' => [
						'title' => $this->translator->translate('Email'),
					],
				],
				'actions' => [
					'view' => [
						'title' => '<span class="mdi mdi-view-headline" title="'.$this->translator->translate('Show').'"></span>',
						'link' => ':App:Admin:Main:ContactForm:Default:',
						'args' => ['show' => 'view'],
						'class' => 'ajax',
					],
					'delete' => [
						'title' => '<span class="mdi mdi-delete" title="'.$this->translator->translate('Delete').'"></span>',
						'link' => ':App:Admin:Main:ContactForm:Default:delete',
					],
				],
				'deleted' => null,
				'defaultSorting' => ['id','desc'],
			]
		);
	}
}