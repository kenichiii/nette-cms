<?php

declare(strict_types=1);

namespace App\Libs\Model;

use App\Libs\Kenichi\ORM\Column\Primary\Created;
use App\Libs\Kenichi\ORM\Column\Primary\Deleted;
use App\Libs\Kenichi\ORM\Column\Primary\Id;
use App\Libs\Kenichi\ORM\Column\Primitive\Text;
use App\Libs\Kenichi\ORM\Column\Primitive\Varchar;
use App\Libs\Kenichi\ORM\Model;

class ContactFormModel extends Model
{
	public function initModel()
	{
		$this->modeladd(new Id());
		$this->modeladd('name', new Varchar());
		$this->modeladd('email', new Varchar());
		$this->modeladd('phone', new Varchar());
		$this->modeladd('subject', new Varchar());
		$this->modeladd('message', new Text());
		$this->modeladd(new Deleted());
		$this->modeladd(new Created());
	}
}