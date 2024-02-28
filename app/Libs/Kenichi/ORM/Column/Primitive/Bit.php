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
}

