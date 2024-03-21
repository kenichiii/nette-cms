<?php

declare(strict_types=1);

namespace App\AppModule\FrontModule\Presenters;

use App\AppModule\FrontModule\Forms\ContactFormFactory;
use App\Libs\Service\App\PageService;
use App\Libs\Service\App\SettingsService;
use App\Libs\Service\App\Translator;
use Nette\Forms\Form;
use Nette\DI\Attributes\Inject;

class BasePresenter extends \App\AppModule\BasePresenter
{
	protected Translator $translator;
	protected SettingsService $settingsService;
	protected PageService $pageService;
	protected string $lang;

	#[Inject]
	public ContactFormFactory $contactFormFactory;

	public function redirectByPointer(string $pointer, ?array $params = null): void
	{
		header(
			'Location: ' . $this->pageService->getPageUrl($this->pageService->getPageByPointer($pointer), $params),
			true,
			301
		);
		exit;
	}

	public function getParam(string $name): ?string
	{
		$params = $this->getParameter('params');
		if (isset($params[$name])) {
			return $params[$name];
		}

		return null;
	}

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

	public function createComponentContactForm(): Form
	{
		$form = $this->contactFormFactory->create(function (bool $succ): void {
			$this->getTemplate()->contactFormMessage = [[
				'message' => $this->translator->translate('Vaše zpráva byla v pořádku odeslána'),
				'type' => 'success',
			]];
			$this->getPayload()->show = '#contact';
			$this->redrawControl('contactFormWrapper');
			//$this->redirect('this#contact', ['contactForm' => 'success']);
		}, function (): void {
			/*
			$this->getTemplate()->contactFormMessage = [[
				'message' => $this->translator->translate('Nepodařilo se odeslat zprávu!'),
				'type' => 'danger',
			]];
			*/
		//	$this->getPayload()->show = '#contact';
			$this->redrawControl('contactFormWrraper');
			//$this->redirect('this#contact', ['contactForm' => 'error']);
		});
		$form->setAction('#');
		return $form;
	}
}