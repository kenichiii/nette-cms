<?php

declare(strict_types=1);

namespace App\Libs\Kenichi\ORM\Column\Primary;

use App\Libs\Kenichi\ORM\Model;
use App\Libs\Kenichi\ORM\Validation;

class Lang extends \App\Libs\Kenichi\ORM\Column\Primitive\Varchar
{
	protected bool $notnull = true;
	protected bool $key = true;
}