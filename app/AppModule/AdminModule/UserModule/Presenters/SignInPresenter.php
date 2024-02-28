<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\UserModule\Presenters;

use App\AppModule\AdminModule\BasePresenter;
use App\AppModule\AdminModule\UserModule\Forms\SignForgottenPasswordFormFactory;
use App\AppModule\AdminModule\UserModule\Forms\SignInFormFactory;
use App\AppModule\AdminModule\UserModule\Forms\SignRenewPasswordFormFactory;
use App\Libs\Exception\Service\App\User\UserNotFoundException;
use App\Libs\Exception\Service\App\User\UserServiceException;
use App\Libs\Repository\App\UserRepository;
use Nette\Application\UI\Form;
use Nette;

class SignInPresenter extends BasePresenter
{
	#[Nette\Application\Attributes\Persistent]
	public string $backlink = '';

	public function __construct(
		private SignInFormFactory $signInFormFactory,
		private SignRenewPasswordFormFactory $signRenewPasswordFormFactory,
		private SignForgottenPasswordFormFactory $signForgottenPasswordFormFactory,
		private UserRepository $userRepository,
	)
	{
	}

	public function renderDefault()
	{

	}

	public function renderForgotPassword()
	{

	}

	public function renderRenewPassword(string $id)
	{

			$user = $this->userRepository->getByForgottenPasswordToken($id);
			if ($user === null) {
				$this->getTemplate()->user_error = $this->translator->translate('Not valid token');
			} elseif ($user['forgottenpasswordtokenexpiration']->getValue() < time()) {
				$this->getTemplate()->user_error = $this->translator->translate('Token Expired');
			}
	}

	/**
	 * @return Form
	 */
	protected function createComponentSignInForm(): Form
	{
		return $this->signInFormFactory->create(function (): void {
			$this->restoreRequest($this->backlink);
			$this->redirect(':App:Admin:Main:Dashboard:Default:');
		});
	}

	/**
	 * @return Form
	 */
	protected function createComponentSignRenewPasswordForm(): Form
	{
		return $this->signRenewPasswordFormFactory->create(function (): void {
			$this->flashMessage($this->translator->translate(
				'Your password has been successfully changed. You can login.'),
				'success'
			);
			$this->redirect('default');
		});
	}

	/**
	 * @return Form
	 */
	protected function createComponentSignForgottenPasswordForm(): Form
	{
		return $this->signForgottenPasswordFormFactory->create(function (): void {
			$this->flashMessage($this->translator->translate(
				'Instructions for changing your password have been sent to the email you entered.'),
				'success'
			);
			$this->redirect('this');
		});
	}
}