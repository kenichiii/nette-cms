<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\MainModule\UtilsModule\Presenters;

use App\AppModule\AdminModule\MainModule\Components\Datagrid\Datagrid;
use App\AppModule\AdminModule\MainModule\Components\Datagrid\DatagridFactory;
use App\AppModule\AdminModule\MainModule\UsersModule\Forms\AddNewSettingFormFactory;
use App\AppModule\AdminModule\MainModule\UsersModule\Forms\EditSettingFormFactory;
use App\Libs\Repository\App\UserRepository;
use App\Libs\Service\App\SettingsService;
use App\Libs\Utils\Utils;
use Nette\Application\UI\Form;

class DefaultPresenter extends \App\AppModule\AdminModule\MainModule\BasePresenter
{
	public function __construct(
	)
	{
	}

	public function actionDeleteCache()
	{

			//Utils::removeDir('../temp/cache');

			$this->getPayload()->succ = true;
			$this->flashMessage('Cache has been successfully removed', 'success');
			$this->redrawControl('flashMessages');
			$this->redrawControl('contentWrapper');


	}
}