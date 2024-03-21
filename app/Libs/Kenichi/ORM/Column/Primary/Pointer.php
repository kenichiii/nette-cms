<?php

declare(strict_types=1);

namespace App\Libs\Kenichi\ORM\Column\Primary;

use App\Libs\Kenichi\ORM\Model;
use App\Libs\Kenichi\ORM\Validation;

class Pointer extends \App\Libs\Kenichi\ORM\Column\Primitive\Varchar
{
    
    protected bool $notnull = true;
	protected bool $key = true;
	/**
	 * @param string|null $formAction
	 * @param mixed|null $data
	 * @param Model|null $model
	 * @return \App\Libs\Kenichi\ORM\Validation
	 */
	public function validate(?string $formAction = null, mixed $data = null, ?Model $model = null): Validation
	{
        $val = new Validation();
        
        $val->add(parent::validate($formAction, $data, $model));
        
        if ($val->isSucc()) {
            if ($this->getValue() !== '' && !preg_match('/[a-zA-Z\_0-9]/', $this->getValue())) {
				$val->addError('notpointer', $this->getColumn());
			}  elseif ($this->getValue() !== '' && is_numeric(substr($this->getValue(),0,1))) {
				$val->addError('notpointer', $this->getColumn());
			}
        }
        
        return $val;        
    }


    public function isPrimary(): bool
    {
        return true;
    }
}
