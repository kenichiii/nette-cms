<?php

declare(strict_types=1);

namespace App\Libs\Kenichi\ORM\Column\Primitive;

use App\Libs\Kenichi\ORM\Column\Column;
use App\Libs\Kenichi\ORM\Model;
use App\Libs\Kenichi\ORM\Validation;

class Decimal extends Column
{

    protected string $sqlName = 'DECIMAL';
    protected string $max = '10,2';
    protected string $dibiModificator = '%f';
    
    public function getSqlName(): string
	{
        return $this->sqlName.'('.$this->getMax().')';
    }  
    
    public function setMax($value)
    {
        $this->max = $value;
        
        return $this;
    }

    public function getMax()
    {
        return $this->max;
    }  
}

