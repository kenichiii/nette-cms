<?php

declare(strict_types=1);

namespace App\Libs\Kenichi\ORM\Column\Primary;

use App\Libs\Kenichi\ORM\Column\Column;
use App\Libs\Kenichi\ORM\Model;
use App\Libs\Kenichi\ORM\Validation;

class Content extends \App\Libs\Kenichi\ORM\Column\Primitive\Text
{
	protected bool $sanitize = false;
	public function getValue(): mixed
	{
		//hack tinymce
		return str_replace('../../..','', (string)$this->value);
	}
}