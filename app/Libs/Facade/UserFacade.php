<?php

declare(strict_types=1);

namespace App\Libs\Facade;

use App\Libs\Exception\Service\App\User\UserNotFoundException;
use App\Libs\Exception\Service\App\User\UserServiceException;
use App\Libs\Service\App\UserService;
use Nette\Security\AuthenticationException;
use Nette\Security\IIdentity;
use Nette;

class UserFacade implements Nette\Security\Authenticator, Nette\Security\IdentityHandler
{
	use Nette\SmartObject;

	public const PasswordMinLength = 6;
	private const DefaultUserRole = 'user';

	public function __construct(
		private UserService        $usersService,
		private Nette\Http\Session $session,
	)
	{
	}


	/**
	 * Performs an authentication.
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(string $username, string $password): Nette\Security\SimpleIdentity
	{
		try {
			$user = $this->usersService->getUser($username);
		} catch (UserServiceException $e) {
			throw new AuthenticationException($e->getMessage(), 400);
		} catch (UserNotFoundException $e) {
			throw new AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);
		}

		//verify user
		if (\App\Libs\Kenichi\ORM\Column\Primary\Password::encode($password) !== $user->get('password')->getValue()) {
			throw new AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
		}

		return new Nette\Security\SimpleIdentity(
			$user->get('id')->getValue(),
			$user->get('roles')->getDecoded(),
			$user->getColumnsValues()
		);
	}

	public function sleepIdentity(IIdentity $identity): IIdentity
	{
		// here you can change the identity before storing after logging in,
		// but we don't need that now
		return $identity;
	}

	public function wakeupIdentity(IIdentity $identity): ?IIdentity
	{
		$user = $this->usersService->getUser($identity->email);

		return new Nette\Security\SimpleIdentity(
			$user->get('id')->getValue(),
			$user->get('roles')->getDecoded(),
			$user->getColumnsValues()
		);
	}
}
