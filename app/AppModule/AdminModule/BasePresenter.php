<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule;

use App\Libs\Service\App\Translator;
use App\Libs\Service\App\SettingsService;
use Nette;

class BasePresenter extends  \App\AppModule\BasePresenter
{
	#[Nette\Application\Attributes\Persistent]
	public string $lang = '';

	protected SettingsService $settingsService;
	protected Translator $translator;

	/**
	 * @param Translator $translator
	 * @return void
	 */
	public function injectTranslator(Translator $translator): void
	{
		$this->translator = $translator;
		$this->translator->setSection('admin');
		$this->onStartup[] = function () use ($translator) {
			$this->getTemplate()->setTranslator($translator);
		};
	}

	public function injectSettings(SettingsService $settingsService)
	{
		$this->settingsService = $settingsService;
		$this->onStartup[] = function () {
			$this->getTemplate()->settingsService = $this->settingsService;
		};
	}
}