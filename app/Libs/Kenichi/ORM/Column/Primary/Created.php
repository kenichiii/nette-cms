<?php

declare(strict_types=1);

namespace App\Libs\Kenichi\ORM\Column\Primary;

use App\Libs\Kenichi\ORM\Column\Primitive\Timestamp;

class Created extends Timestamp
{
    protected bool $isInForm = false;
    
    public function isDefault(): bool
    {
        return true;
    }
}
