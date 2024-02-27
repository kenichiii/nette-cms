<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\UserModule\Presenters;

use App\AppModule\AdminModule\BasePresenter;
use App\AppModule\AdminModule\UserModule\Forms\SignInFormFactory;
use Nette\Application\UI\Form;
use Nette;

class SignInPresenter extends BasePresenter
{
	#[Nette\Application\Attributes\Persistent]
	public string $backlink = '';

	public function __construct(
		private SignInFormFactory $signInFormFactory,
	)
	{
	}

	public function renderDefault()
	{

	}

	public function renderForgotPassword()
	{

	}

	public function renderRenewPassword()
	{

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
}