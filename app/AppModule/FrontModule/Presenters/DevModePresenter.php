<?php

declare(strict_types=1);

namespace App\AppModule\FrontModule\Presenters;

use App\Libs\Service\App\SettingsService;
use Nette\Application\UI\Presenter;
use Nette\Http\Session;

class DevModePresenter extends Presenter
{
	public function __construct(private SettingsService $settingsService, private Session $session)
	{

	}

	public function renderDefault()
	{
		if (isset($_GET['basic_auth'])
			&& $_GET['basic_auth'] === $this->settingsService->getAppConfig()['devModePwd']
		) {
			$this->session->getSection('_dev_mode')->set('pwd', true);
			header(
				'Location: /' . $this->settingsService->getAppConfig()['subdir']
									. '?basic_auth=' . $this->settingsService->getAppConfig()['devModePwd'],
				true,
				302
			);
		}
		$this->setLayout('emptyLayout');
	}

}