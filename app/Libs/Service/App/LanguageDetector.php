<?php

declare(strict_types=1);

namespace App\Libs\Service\App;

use Nette\Http\Request;

final class LanguageDetector
{
	private string $lang;

	public function __construct(private Request $request)
	{
		$this->lang = $this->request->getQuery('lang') ?: 'en';
	}

	/**
	 * @return string
	 */
	public function getLang(): string
	{
		return $this->lang;
	}
}