<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\MainModule\SettingsModule\Presenters;

use App\AppModule\AdminModule\MainModule\Components\Datagrid\Datagrid;
use App\AppModule\AdminModule\MainModule\Components\Datagrid\DatagridFactory;
use App\AppModule\AdminModule\MainModule\SettingsModule\Forms\AddNewSettingFormFactory;
use App\AppModule\AdminModule\MainModule\SettingsModule\Forms\EditSettingFormFactory;
use App\Libs\Repository\App\SettingsRepository;
use Nette\Forms\Form;

class DefaultPresenter extends \App\AppModule\AdminModule\MainModule\BasePresenter
{
	public function __construct(
		private DatagridFactory $datagridFactory,
		private SettingsRepository $settingsRepository,
		private AddNewSettingFormFactory $addNewSettingFormFactory,
		private EditSettingFormFactory $editSettingFormFactory,
	)
	{
	}

	public function renderDefault(?int $id)
	{
		if ($id) {
			$this->getTemplate()->settingModel = $this->settingsRepository->getByPk($id);
			$this->getTemplate()->show = $this->getParameter('show') ?: $_POST['show'] ?? 'view';
			$this->getPayload()->showModal = "#{$this->getParameter('show')}SettingModal";
			$this->redrawControl($this->getParameter('show'));
		}
	}
	public function actionDelete(int $id)
	{
		$this->settingsRepository->deleteByPK($id);

		$this->flashMessage('Setting has been successfully deleted', 'success');
		$this->redirect('default');
	}
	public function createComponentGrid(): Datagrid
	{
		return $this->datagridFactory->create(
			$this->settingsRepository,
			[
				'columns' => [
					'pointer' => [
						'title' => $this->translator->translate('Pointer'),
					],
					'value' => [
						'title' => $this->translator->translate('Value'),
					],
					'info' => [
						'title' => $this->translator->translate('Info'),
					],

				],
				'actions' => [
					'edit' => [
						'title' => '<span class="mdi mdi-table-edit" title="'.$this->translator->translate('Edit').'"></span>',
						'link' => ':App:Admin:Main:Settings:Default:',
						'args' => ['show' => 'edit'],
						'class' => 'ajax',
					],
					'delete' => [
						'title' => '<span class="mdi mdi-delete" title="'.$this->translator->translate('Delete').'"></span>',
						'link' => ':App:Admin:Main:Settings:Default:delete',
					],
				],
				'newRecord' => [
					'title' => $this->translator->translate('Add new setting'),
					'link' => '#add-new-usetting',
					'args' => [],
				],
				'deleted' => null,
				'defaultSorting' => ['id','asc'],
			]
		);
	}

	/**
	 * @return Form
	 */
	protected function createComponentEditSettingForm(): Form
	{
		return $this->editSettingFormFactory->create(function (bool $succ): void {
			if ($succ) {
				$this->flashMessage($this->translator->translate(
					'Setting has been successfully changed'),
					'success'
				);
			}
			$this->getPresenter()->redrawControl('datagrid');
			$this->getPresenter()->redrawControl('datagridWrapper');
			$this->redrawControl('edit');
			$this->redrawControl('flashMessages');
			$this->redrawControl('contentWrapper');
		}, (int)$this->getParameter('id'));
	}

	/**
	 * @return Form
	 */
	protected function createComponentAddNewSettingForm(): Form
	{
		return $this->addNewSettingFormFactory->create(function (bool $succ): void {
			if ($succ) {
				$this->flashMessage($this->translator->translate(
					'Setting has been successfully created'),
					'success'
				);
				$this->getPayload()->closeModal = '#addNewSettingNewModal';
			}
			$this->getPayload()->afterForm = true;

			$this->getPresenter()->redrawControl('datagrid');
			$this->getPresenter()->redrawControl('datagridWrapper');
			$this->redrawControl('addModal');
			$this->redrawControl('flashMessages');
			$this->redrawControl('contentWrapper');
		});
	}
}