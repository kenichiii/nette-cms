<?php

declare(strict_types=1);

namespace App\Libs\Kenichi\ORM\Column\Primitive;

use App\Libs\Kenichi\ORM\Column\Column;
use App\Libs\Kenichi\ORM\Model;
use App\Libs\Kenichi\ORM\Validation;

class OptionList extends Varchar
{
    protected array $types = [];
    
    protected bool $notNull = true;


	/**
	 * @param string $key
	 * @param string $title
	 * @return $this
	 */
    public function setType(string $key, string $title): OptionList
    {
        $this->types[$key] = $title;
        
        return $this;
    }

	/**
	 * @param array $types
	 * @return $this
	 */
    public function setTypes(array $types): OptionList
    {
        $this->types = $types;
        
        return $this;
    }    
    
    public function getTypes(): array
    {
        return $this->types;
    }
    
    public function getTypesKeys(): array
    {
        return array_keys($this->types);
    }
    
    public function getType(string $key): string
    {
        return $this->types[$key];
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
        
        if ($val->isSucc()) {
            if (!in_array($this->getValue(), $this->getTypesKeys())) {
               $val->addError('notenumtype' , $this->getColumn());
            }
        }
        
        return $val;
    }    
}

