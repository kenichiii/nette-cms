<?php

declare(strict_types=1);

namespace App\Libs\NA\Model;

use App\Libs\Kenichi\ORM\Column\Primary\Active;
use App\Libs\Kenichi\ORM\Column\Primary\Deleted;
use App\Libs\Kenichi\ORM\Column\Primary\Id;
use App\Libs\Kenichi\ORM\Column\Primitive\Bit;
use App\Libs\Kenichi\ORM\Column\Primitive\Number;
use App\Libs\Kenichi\ORM\Column\Primitive\Text;
use App\Libs\Kenichi\ORM\Column\Primitive\Varchar;
use App\Libs\Kenichi\ORM\Model;

class MeetingModel extends Model
{
	public function initModel()
	{
		$this->modeladd(new Id());
		$groupid = new Number();
		$groupid->setKey(true);
		$this->modeladd('groupid', $groupid);
		$this->modeladd('lang', new Varchar());
		$this->modeladd('place', new Varchar());
		$this->modeladd('street', new Varchar());
		$this->modeladd('zoom', new Bit());
		$this->modeladd('skype', new Bit());
		$this->modeladd('day', new Number());
		$this->modeladd('time', new Varchar());
		$content = new Text();
		$content->setSanitize(false)
			->setIsInData(false)
		;
		$this->modeladd('content', $content);
		$this->modeladd(new Active());
		$this->modeladd(new Deleted());


		if ($this->isNotParentModel('App\Libs\NA\Model\GroupModel')) {
			$group = new GroupModel($this);
			$group->repositoryFactory(
				$this->getRepository()->getAppConfig(),
				$this->getRepository()->getConn()
			)->setJoin('id', 'groupid');
			$this->relationsadd('group', $group);

		}

	}
}