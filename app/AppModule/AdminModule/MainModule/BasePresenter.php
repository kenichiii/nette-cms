<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\MainModule;

class BasePresenter extends \App\AppModule\AdminModule\BasePresenter
{
	public function startup()
	{
		parent::startup();

		// Check if user is logged in and redirect to sign-in page if no
		if (!$this->getUser()->isLoggedIn() || !$this->getUser()->isInRole('admin')) {
			$this->redirect(':App:Admin:User:SignIn:', [
				'backlink' => $this->storeRequest()
			]);
		}
	}
}