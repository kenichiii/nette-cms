<?php

declare(strict_types=1);

namespace App\Libs\Service\App;

use App\Libs\Exception\Service\App\User\UserNotFoundException;
use App\Libs\Model\App\UserModel;
use App\Libs\Repository\App\UserRepository;

class UserService
{
	public function __construct(protected UserRepository $userRepository)
	{

	}

	/**
	 * @param string $username
	 * @return UserModel
	 * @throws \App\Libs\Kenichi\ORM\Exception
	 * @throws \Dibi\Exception
	 */
	public function getUser(string $username): UserModel
	{
		$select = $this->userRepository->getSelect();
		$user = $select->andWhere('email', $username)->fetchSingle();
		if (!$user) {
			throw new UserNotFoundException();
		}
		return $user;
	}
}