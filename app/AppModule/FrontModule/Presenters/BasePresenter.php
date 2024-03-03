<?php

declare(strict_types=1);

namespace App\AppModule\FrontModule\Presenters;

use App\Libs\Service\App\PageService;
use App\Libs\Service\App\SettingsService;
use App\Libs\Service\App\Translator;
use Nette\Http\Session;

class BasePresenter extends \App\AppModule\BasePresenter
{
	protected Translator $translator;
	protected SettingsService $settingsService;
	protected PageService $pageService;
	protected string $lang;

	public function injectPages(PageService $pageService)
	{
		$this->pageService = $pageService;
		$this->lang = $this->pageService->getLang();
		$this->onStartup[] = function () {
			$this->setLayout($this->pageService->getCurrentPage()['layout']->getValue());
			$this->getTemplate()->pageService = $this->pageService;
			$this->getTemplate()->lang = $this->pageService->getLang();
		};
	}

	public function injectSettings(SettingsService $settingsService)
	{
		$this->settingsService = $settingsService;
		$this->onStartup[] = function () {
			$this->getTemplate()->settingsService = $this->settingsService;
		};
	}

	/**
	 * @param Translator $translator
	 * @return void
	 */
	public function injectTranslator(Translator $translator): void
	{
		$this->translator = $translator;
		$this->onStartup[] = function () use ($translator) {
			$this->getTemplate()->setTranslator($translator);
		};
	}

}