<?php

declare(strict_types=1);

namespace App\Libs\NA\Model;

use App\Libs\Kenichi\ORM\Column\Primary\Id;
use App\Libs\Kenichi\ORM\Column\Primitive\Number;
use App\Libs\Kenichi\ORM\Column\Primitive\Text;
use App\Libs\Kenichi\ORM\Column\Primitive\Varchar;
use App\Libs\Kenichi\ORM\Model;

class DailyThoughModel extends Model
{
	public function initModel()
	{
		$this->modeladd(new Id());
		$lang = new Varchar();
		$lang->setUnique(true)
			->setUniqueWith(['day','month']);
		$this->modeladd('lang', $lang);
		$this->modeladd('day', new Number());
		$this->modeladd('month', new Number());
		$this->modeladd('title', new Varchar());
		$this->modeladd('perex', new Text());
		$this->modeladd('source', new Varchar());
		$this->modeladd('though', new Text());
		$this->modeladd('tip', new Text());
	}
}