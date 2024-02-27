<?php

declare(strict_types=1);

namespace App\Libs\Kenichi\ORM\Column\Primary;

use App\Libs\Kenichi\ORM\Column\Primitive\Bit;


class Deleted extends Bit
{
    protected mixed $default = '0';
    protected mixed $value = 0;

    protected bool $key = true;
    protected bool $notnull = true;
    
    protected bool $isInForm = false;
    
    public function isDefault(): bool
    {
        return true;
    }
}
