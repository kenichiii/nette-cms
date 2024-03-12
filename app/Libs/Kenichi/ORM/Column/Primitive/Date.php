<?php

declare(strict_types=1);

namespace App\Libs\Kenichi\ORM\Column\Primitive;

use App\Libs\Kenichi\ORM\Column\Column;
use App\Libs\Kenichi\ORM\Model;
use App\Libs\Kenichi\ORM\Validation;
    
class Date extends Column
{

    protected string $sqlName = 'DATE';
    protected string $dibiModificator = '%d';
    
    public function setfromform(mixed $value): Date
	{
        if ($value) {
            $this->value = \Date('Y-m-d',  strtotime($value));
            $this->isChange = true;
        }

        return $this;
    }

	public function getValue(): mixed
	{
		if (is_object($this->value)) {
			return $this->value->__toString();
		}

		return $this->value;
	}

	public function getToDate($f='j.n.Y')
    {
        if(!$this->getValue()) return null;
        return \Date( $f, $this->getToTime());
    }
    
    public function getToTime()
    {
        if(!$this->getValue()) return null;
        return strtotime($this->getValue());
    }    
}

