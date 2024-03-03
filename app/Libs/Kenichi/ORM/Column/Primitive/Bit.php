<?php

declare(strict_types=1);

namespace App\Libs\Kenichi\ORM\Column\Primitive;

use App\Libs\Kenichi\ORM\Column\Column;

class Bit extends Column
{
    protected string $sqlName = 'TINYINT';
    protected int $max = 1;
    protected string $dibiModificator = '%i';
	protected bool $sanitize = false;

	public function setfromform(mixed $value): Column
	{
		$this->isChange = true;

		if ($this->isSanitize()) {
			$this->value = (int) $value;
		} else {
			$this->value = is_string($value) ? intval($value) : $value;
		}
		return $this;
	}
}

