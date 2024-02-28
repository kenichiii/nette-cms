<?php

declare(strict_types=1);

namespace App\Libs\Kenichi\ORM\Column\Primitive;

use App\Libs\Kenichi\ORM\Column\Column;
use App\Libs\Kenichi\ORM\Model;
use App\Libs\Kenichi\ORM\Validation;

class Timestamp extends Datetime 
{
    public function getValue(bool $fresh = false): string
	{
        if ($fresh || $this->value === null) {
			$this->value = date('Y-m-d G:i:s');
		}

        return $this->value;
    }

	public function setfromdb(mixed $value): Column
	{
		$this->value = (string) $value;
		return $this;
	}
}