<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\MainModule\UserModule\Presenters;

use App\AppModule\AdminModule\MainModule\UserModule\Forms\AccountSettingsFormFactory;
use App\AppModule\AdminModule\MainModule\UserModule\Forms\ChangePasswordFormFactory;
use App\Libs\Repository\App\UserRepository;
use Nette\Application\UI\Form;
use Nette;

class DefaultPresenter extends \App\AppModule\AdminModule\MainModule\BasePresenter
{
	public function __construct(
		private AccountSettingsFormFactory $accountSettingsFormFactory,
		private ChangePasswordFormFactory $changePasswordFormFactory,
		private UserRepository $userRepository,
	)
	{
	}

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

	public function actionUpload()
	{
		$file_name = $_FILES['file_to_upload']['name'] ?? null;
		$file_temp_location = $_FILES['file_to_upload']['tmp_name'] ?? null;
		if (!is_dir("docs/users/{$this->getUser()->getId()}")) {
			mkdir("docs/users/{$this->getUser()->getId()}");
		}
		if (!$file_temp_location) {
			$this->flashMessage('ERROR: No file has been selected', 'danger');
		}  elseif (move_uploaded_file($file_temp_location, "docs/users/{$this->getUser()->getId()}/$file_name")){
			$this->userRepository->updateByPK(['photo'=>$file_name],$this->getUser()->getId());
			$this->flashMessage('Account Photo was successfully uploaded', 'success');
		} else {
			$this->flashMessage('Server Error', 'danger');
		}
		$this->redirect('accountSettings');
	}

	/**
	 * @return Form
	 */
	protected function createComponentAccountSettingsForm(): Form
	{
		return $this->accountSettingsFormFactory->create(function (): void {
			$this->flashMessage($this->translator->translate(
				'Account Settings has been successfully changed'),
				'success'
			);
			$this->redirect('this');
		});
	}

	/**
	 * @return Form
	 */
	protected function createComponentChangePasswordForm(): Form
	{
		return $this->changePasswordFormFactory->create(function (bool $succ): void {
			if ($succ) {
				$this->flashMessage($this->translator->translate(
					'Account Settings has been successfully changed'),
					'success'
				);
			}
			$this->redrawControl('form');
			$this->redrawControl('flashMessages');
			$this->redrawControl('contentWrapper');
		});
	}
}