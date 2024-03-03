<?php

declare(strict_types=1);

namespace App\Libs\Model\App;

use App\Libs\Kenichi\ORM\Column\Primary\Email;
use App\Libs\Kenichi\ORM\Column\Primary\Id;
use App\Libs\Kenichi\ORM\Column\Primary\Password;
use App\Libs\Kenichi\ORM\Column\Primary\Phone;
use App\Libs\Kenichi\ORM\Column\Primary\Photo;
use App\Libs\Kenichi\ORM\Column\Primitive\Json;
use App\Libs\Kenichi\ORM\Column\Primitive\Number;
use App\Libs\Kenichi\ORM\Column\Primitive\Varchar;
use App\Libs\Kenichi\ORM\Model;

class UserModel extends Model
{
	public function initModel()
	{
		$this->modeladd(new Id());
		$this->modeladd(new Email());
		$this->modeladd('name', new Varchar());
		$this->modeladd(new Photo());
		$this->modeladd(new Phone());
		$this->modeladd(new Password());
		$roles = new Json();
		//$roles->setDefault('["user"]');
		$this->modeladd('roles', $roles);
		$this->modeladd('role', new Varchar());
		$this->modeladd('forgottenPasswordToken', new Varchar());
		$this->modeladd('forgottenPasswordTokenExpiration', new Number());

	}
}