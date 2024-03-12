<?php

declare(strict_types=1);

namespace App\Libs\Service\App;

use Nette\Localization\Translator as NetteTranslator;

final class Translator implements NetteTranslator
{
	private array|null $repository = null;

	private string $lang;
	private string $section = 'front';

	public function __construct(
		private PageService $pageService,
		private LanguageDetector $languageDetector,
		private TranslationsService $translationsService,
	)
	{

	}

	public function setSection(string $section): Translator
	{
		$this->section = $section;
		return $this;
	}

	public function setLang(string $lang): Translator
	{
		$this->lang = $lang;
		return $this;
	}

	public function getLang(): string
	{
		if (!isset($this->lang) && $this->section === 'front') {
			$this->lang = $this->pageService->getLang();
		} elseif (!isset($this->lang)) {
			$this->lang = $this->languageDetector->getLang();
		}
		return $this->lang;
	}

	public function translate(mixed $message, ...$params): string
	{
		if ($message === null) {
			return '';
		}

		if ($this->repository === null) {
			$this->repository = $this->translationsService->getTranslations($this->getLang(), $this->section);
		}

		$translation =  array_key_exists($message, $this->repository) ? $this->repository[$message] : $message;

		foreach ($params as $key => $param) {
			$translation = str_replace("[:{$key}:]", (string) $param, $translation);
		}

		return (string)($translation ?: $message);
	}
}