<?php

declare(strict_types=1);

namespace App\Libs\Kenichi\ORM\Column\Primary;

use App\Libs\Kenichi\ORM\Column\Primitive\Number;

class Id extends Number
{
    protected bool $primaryKey = true;
    
    public function isPrimary(): bool
    {
        return true;
    }
}

