<?php

declare(strict_types=1);

namespace App\Libs\Kenichi\ORM\Column\Primitive;

use App\Libs\Kenichi\ORM\Column\Column;
use App\Libs\Kenichi\ORM\Model;
use App\Libs\Kenichi\ORM\Validation;

class Json extends Text
{
	public function getDecoded(): mixed
	{
		if ($this->value) {
			return \Nette\Utils\Json::decode($this->value);
		} else {
			return null;
		}
	}
}