<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\InstallModule\Presenters;

use App\AppModule\AdminModule\InstallModule\Service\InstallService;

class DatabasePresenter extends BasePresenter
{
	public function __construct(
		private InstallService $installService,
	)
	{
	}

	public function renderDefault()
	{
		try {
			$this->getTemplate()->message = $this->installService->settings();
			$this->getTemplate()->message .= $this->installService->pages();
			$this->getTemplate()->message .= $this->installService->users();
		} catch (\Throwable $e) {
			$this->getTemplate()->message = $e->getMessage();
		}
	}


}