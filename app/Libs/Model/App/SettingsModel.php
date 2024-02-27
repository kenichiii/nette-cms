<?php

declare(strict_types=1);

namespace App\Libs\Model\App;

use App\Libs\Kenichi\ORM\Column\Primary\Id;
use App\Libs\Kenichi\ORM\Column\Primary\Pointer;
use App\Libs\Kenichi\ORM\Column\Primitive\Varchar;
use App\Libs\Kenichi\ORM\Model;

class SettingsModel extends Model
{
	public function initModel()
	{
		$this->modeladd(new Id());
		$pointer = new Pointer();
		$pointer->setUnique(true);
		$this->modeladd($pointer);
		$this->modeladd('info', new Varchar());
		$this->modeladd('value', new Varchar());
	}
}