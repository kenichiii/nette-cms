<?php

declare(strict_types=1);

namespace App\Libs\NA\Model;

use App\Libs\Kenichi\ORM\Column\Primary\Active;
use App\Libs\Kenichi\ORM\Column\Primary\Deleted;
use App\Libs\Kenichi\ORM\Column\Primary\Email;
use App\Libs\Kenichi\ORM\Column\Primary\Id;
use App\Libs\Kenichi\ORM\Column\Primary\Phone;
use App\Libs\Kenichi\ORM\Column\Primary\Photo;
use App\Libs\Kenichi\ORM\Column\Primary\Uri;
use App\Libs\Kenichi\ORM\Column\Primitive\Text;
use App\Libs\Kenichi\ORM\Column\Primitive\Varchar;
use App\Libs\Kenichi\ORM\Model;

class GroupModel extends Model
{
	public function initModel()
	{
		$this->modeladd(new Id());
		$this->modeladd('lang', new Varchar());
		$this->modeladd('title', new Varchar());
		$this->modeladd('uri', new Uri());
		$this->modeladd(new Photo());
		$this->modeladd(new Email());
		$this->modeladd(new Phone());
		$content = new Text();
		$content->setSanitize(false)
			->setIsInData(false)
		;
		$this->modeladd('content', $content);
		$this->modeladd(new Active());
		$this->modeladd(new Deleted());

		if ($this->isNotParentModel('App\Libs\NA\Model\MeetingModel')) {
			$meetings = new MeetingModel($this);
			$meetings->repositoryFactory(
				$this->getRepository()->getAppConfig(),
				$this->getRepository()->getConn()
			)->setRelationN1('groupid', 'id');
			$this->relationsadd('meetings', $meetings);
		}
	}
}