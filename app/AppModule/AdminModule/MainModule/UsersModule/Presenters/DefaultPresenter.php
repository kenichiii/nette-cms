<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\MainModule\UsersModule\Presenters;

use App\AppModule\AdminModule\MainModule\Components\Datagrid\Datagrid;
use App\AppModule\AdminModule\MainModule\Components\Datagrid\DatagridFactory;
use App\Libs\Repository\App\UserRepository;
use App\Libs\Service\App\SettingsService;

class DefaultPresenter extends \App\AppModule\AdminModule\MainModule\BasePresenter
{
	public function __construct(
		private UserRepository $userRepository,
		private DatagridFactory $datagridFactory,
		protected SettingsService $settingsService,
	)
	{
	}

	public function renderDefault()
	{

	}
	public function createComponentUsersAll(): Datagrid
	{
		return $this->datagridFactory->create(
			$this->userRepository,
			[
				'columns' => [
					'photo' => [
						'type' => 'none',
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
					'name' => [
						'title' => $this->translator->translate('Name'),
					],
					'role' => [
						'title' => $this->translator->translate('Role'),
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
}