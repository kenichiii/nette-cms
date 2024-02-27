<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\InstallModule\Presenters;

use App\Libs\Service\App\SettingsService;
use Nette\Application\UI\Presenter;

class BasePresenter extends Presenter
{
	protected SettingsService $settingsService;
	public function injectSettings(SettingsService $settingsService)
	{
		$this->settingsService = $settingsService;
		$this->onStartup[] = function () {
			$this->getTemplate()->settingsService = $this->settingsService;
		};
	}
}