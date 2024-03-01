<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\MainModule\UsersModule\Presenters;

use App\AppModule\AdminModule\MainModule\Components\Datagrid\Datagrid;
use App\AppModule\AdminModule\MainModule\Components\Datagrid\DatagridFactory;
use App\AppModule\AdminModule\MainModule\UsersModule\Forms\AddNewUserFormFactory;
use App\AppModule\AdminModule\MainModule\UsersModule\Forms\EditUserFormFactory;
use App\Libs\Repository\App\UserRepository;
use App\Libs\Service\App\SettingsService;
use Nette\Application\UI\Form;

class DefaultPresenter extends \App\AppModule\AdminModule\MainModule\BasePresenter
{
	public function __construct(
		private UserRepository $userRepository,
		private DatagridFactory $datagridFactory,
		protected SettingsService $settingsService,
		private AddNewUserFormFactory $addNewUserFormFactory,
		private EditUserFormFactory $editUserFormFactory,
	)
	{
	}

	public function renderDefault()
	{

	}
	public function actionDelete(int $id)
	{
		$this->userRepository->deleteByPK($id);
		$this->flashMessage('User has been successfully deleted', 'success');
		$this->redirect('default');
	}
	public function createComponentUsersAll(): Datagrid
	{
		return $this->datagridFactory->create(
			$this->userRepository,
			[
				'columns' => [
					'photo' => [
						'type' => 'none',
						'sorting' => false,
						'title' => $this->translator->translate('Photo'),
						'render' => function($record, $photo) {
							return "<img class='img-xs rounded-circle' src='/"
								. ($this->settingsService->getAppConfig()['subdir']."docs/users/"
								. ($record->get($photo->getName())->getValue()
										? $record->get('id')->getValue() ."/" .$record->get($photo->getName())->getValue()
										: "_default/avatar.jpg")
								)."'>";
						},
					],
					'email' => [
						'title' => $this->translator->translate('Email'),
					],
					'role' => [
						'title' => $this->translator->translate('Role'),
					],
					'name' => [
						'title' => $this->translator->translate('Name'),
					],
					'phone' => [
						'title' => $this->translator->translate('Phone'),
					],
				],
				'actions' => [
					'view' => [
						'title' => '<span class="mdi mdi-view-headline" title="'.$this->translator->translate('Show').'"></span>',
						'link' => ':App:Admin:Main:Users:Default:viewUser',
					],
					'edit' => [
						'title' => '<span class="mdi mdi-table-edit" title="'.$this->translator->translate('Edit').'"></span>',
						'link' => ':App:Admin:Main:Users:Default:editUser',
					],
					'delete' => [
						'title' => '<span class="mdi mdi-delete" title="'.$this->translator->translate('Delete').'"></span>',
						'link' => ':App:Admin:Main:Users:Default:delete',
					],
				],
				'newRecord' => [
					'title' => $this->translator->translate('Add new user'),
					'link' => '#add-new-user',
					'args' => [],
				],
				'deleted' => null,
			]
		);
	}

	/**
	 * @return Form
	 */
	protected function createComponentEditUserForm(): Form
	{
		return $this->editUserFormFactory->create(function (bool $succ): void {
			if ($succ) {
				$this->flashMessage($this->translator->translate(
					'User data has been successfully changed'),
					'success'
				);
			}
			$this->getPresenter()->redrawControl('datagrid');
			$this->getPresenter()->redrawControl('datagridWrapper');
			$this->redrawControl('flashMessages');
			$this->redrawControl('contentWrapper');
		}, (int)$this->getParameter('user'));
	}

	/**
	 * @return Form
	 */
	protected function createComponentAddNewUserForm(): Form
	{
		return $this->addNewUserFormFactory->create(function (bool $succ): void {
			if ($succ) {
				$this->flashMessage($this->translator->translate(
					'Page has been successfully changed'),
					'success'
				);
				$this->getPayload()->closeModal = '#addNewUserNewModal';
				$this['addNewUserForm']->setDefaults([], true);
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