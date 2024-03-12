<?php

declare(strict_types=1);

namespace App\Libs\NA\Model;

use App\Libs\Kenichi\ORM\Column\Primary\Active;
use App\Libs\Kenichi\ORM\Column\Primary\Deleted;
use App\Libs\Kenichi\ORM\Column\Primary\File;
use App\Libs\Kenichi\ORM\Column\Primary\Id;
use App\Libs\Kenichi\ORM\Column\Primary\Photo;
use App\Libs\Kenichi\ORM\Column\Primitive\Date;
use App\Libs\Kenichi\ORM\Column\Primitive\Text;
use App\Libs\Kenichi\ORM\Column\Primitive\Varchar;
use App\Libs\Kenichi\ORM\Model;

class ActionModel extends Model
{
	public function initModel()
	{
		$this->modeladd(new Id());
		$lang = new Varchar();
		$lang->setUnique(true)
			->setUniqueWith(['uri']);
		$this->modeladd('lang', $lang);
		$this->modeladd('uri', new Varchar());
		$this->modeladd('dateStart', new Date());
		$this->modeladd('dateEnd', new Date());
		$this->modeladd('title', new Varchar());
		$this->modeladd('perex', new Text());
		$content = new Text();
		$content->setSanitize(false)
			->setIsInData(false)
		;
		$this->modeladd('content', $content);
		$this->modeladd(new Photo());
		$this->modeladd('invitation', new File());
		$this->modeladd(new Active());
		$this->modeladd(new Deleted());

	}
}