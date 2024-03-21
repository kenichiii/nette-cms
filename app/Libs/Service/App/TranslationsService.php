<?php

declare(strict_types=1);

namespace App\Libs\Service\App;

use Nette\Utils\Json;

class TranslationsService
{
	public function __construct()
	{

	}

	public function getTranslations(string $lang, string $section): array
	{
		$filePath = './translations/'.$section.'/'.$lang.'.json';
		if (file_exists($filePath)) {
			$json = file_get_contents($filePath);
			$translations = (array) Json::decode($json);
		} else {
			$translations = [];
		}

		return $translations;
	}
}