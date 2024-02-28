<?php

declare(strict_types=1);

namespace App\Libs\Repository\App;

use App\Libs\Kenichi\ORM\Repository;
use App\Libs\Model\App\UserModel;

class UserRepository extends Repository
{
	public function getByForgottenPasswordToken(string $token): ?UserModel
	{
		$select = $this->getSelect();
		return $select->andWhere('forgottenpasswordtoken', $token)
			->fetchSingle();
	}
}