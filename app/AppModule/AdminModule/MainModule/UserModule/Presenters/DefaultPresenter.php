<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\MainModule\UserModule\Presenters;

class DefaultPresenter extends \App\AppModule\AdminModule\MainModule\BasePresenter
{
	public function actionLogout()
	{
		$this->getUser()->logout();
		$this->flashMessage('logout successful', 'success');
		$this->redirect(':App:Admin:User:SignIn:');
	}

	public function renderSettings()
	{

	}

	public function renderAccountSettings()
	{

	}

	public function renderChangePassword()
	{

	}
}