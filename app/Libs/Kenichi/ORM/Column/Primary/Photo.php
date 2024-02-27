<?php

declare(strict_types=1);

namespace App\Libs\Kenichi\ORM\Column\Primary;

class Photo extends File
{
	protected $allowedExt = array('jpg','png','gif');
}
