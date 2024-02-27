<?php

declare(strict_types=1);

namespace App\Libs\Kenichi\ORM\Column\Primitive;

use App\Libs\Kenichi\ORM\Column\Column;
use App\Libs\Kenichi\ORM\Model;
use App\Libs\Kenichi\ORM\Validation;

class Datetime extends Column
{
    protected string $sqlName = 'DATETIME';
    protected string $dibiModificator = '%t';

    public function getInDate($f='j.n.Y G:i:s')
    {
        if(!$this->getValue()) return null;
        return \Date( $f, $this->getInTime($this->getValue()));
    }
    
    public function getInTime()
    {
        if(!$this->getValue()) return null;
        return strtotime($this->getValue());
    }     
}
