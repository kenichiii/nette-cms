<?php

declare(strict_types=1);

namespace App\Libs\Kenichi\ORM\Column\Primitive;

use App\Libs\Kenichi\ORM\Column\Column;
use App\Libs\Kenichi\ORM\Model;
use App\Libs\Kenichi\ORM\Validation;

class Varchar extends Column
{
    protected string $sqlName = 'VARCHAR';
    protected int $max = 255;
    protected bool $sanitize = true;
    protected string $dibiModificator = '%s';

	/**
	 * @return string
	 */
    public function getSqlName(): string
	{
        return $this->sqlName.'('.$this->getMax().')';
    }

	/**
	 * @param int $value
	 * @return $this
	 */
    public function setMax(int $value): Varchar
    {
        $this->max = $value;
        
        return $this;
    }

	/**
	 * @return int
	 */
    public function getMax(): int
    {
        return $this->max;
    }

	/**
	 * @param string|null $formAction
	 * @param mixed|null $data
	 * @param Model|null $model
	 * @return Validation
	 */
    public function validate(?string $formAction = null, mixed $data = null, ?Model $model = null): Validation
    {
        $val = new Validation();
        
        $val->add(parent::validate());
        
        if ($val->isSucc())  {
            if ($this->isNotNull() && $this->getValue() === '') {
               $val->addError('notnull' , $this->getColumn());
            } elseif (strlen($this->getValue()) > $this->getMax()) {
                $val->addError('maxlength' , $this->getColumn());
            }
        }
        
        return $val;
    }
         
}

