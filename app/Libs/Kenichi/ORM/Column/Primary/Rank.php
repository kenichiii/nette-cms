<?php

declare(strict_types=1);

namespace App\Libs\Kenichi\ORM\Column\Primary;

use App\Libs\Kenichi\ORM\Column\Primitive\Number;

class Rank extends Number
{

    protected bool $notnull = true;
    protected bool $key = true;
    protected bool $isInForm = false;
    
    protected string $sorting = 'DESC';
    
    public function __construct(string $sorting = 'ASC')
	{
        $this->setSorting($sorting);
    }

    public function isDefault(): bool
    {
        return true;
    }
    
    public function setSorting(string $new): Rank
    {
        $this->sorting = $new;
        return $this;
    }
    
    public function getSorting(): string
    {
        return $this->sorting;
    }
    
}

