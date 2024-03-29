<?php

declare(strict_types=1);

namespace App\Libs\Service\App;

use Nette\Localization\Translator as NetteTranslator;

final class AdminTranslator implements NetteTranslator
{
	private static array|null $repository = null;

	private string $lang;
	private string $section = 'Admin';

	public function __construct(
		private PageService $pageService,
		private TranslationsService $translationsService,
	)
	{
		$this->lang = $this->pageService->getLang();
	}

	public function setSection(string $section): void
	{
		$this->section = $section;
	}

	public function translate(mixed $message, ...$params): string
	{

		if (self::$repository === null) {
			self::$repository = $this->translationsService->getTranslations($this->lang, $this->section);
		}

		$translation = array_key_exists($message, self::$repository) ? self::$repository[$message] : $message;

		foreach ($params as $key => $param) {
			$translation = str_replace("[:{$key}:]", (string) $param, $translation);
		}

		return $translation ?: $message;
	}

	public function getLang(): string
	{
		return $this->lang;
	}
}