<?php

declare(strict_types=1);

namespace App\Libs\Kenichi\ORM\Column\Primitive;

use App\Libs\Kenichi\ORM\Column\Column;
use App\Libs\Kenichi\ORM\Model;
use App\Libs\Kenichi\ORM\Validation;

class Number extends Column
{

    protected string $sqlName = 'INT';
    protected int $max = 11;
    protected string $dibiModificator = '%i';
    
    public function getSqlName(): string
	{
        return $this->sqlName.'('.$this->getMax().')';
    }  
    
    public function setMax(int $value): Number
    {
        $this->max = $value;
        
        return $this;
    }

    public function getMax(): int
    {
        return $this->max;
    }    
}

