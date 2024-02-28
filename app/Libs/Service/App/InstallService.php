<?php

declare(strict_types=1);

namespace App\Libs\Service\App;

use App\Libs\Kenichi\ORM\Column\Primary\Password;
use App\Libs\Repository\App\PageRepository;
use App\Libs\Repository\App\SettingsRepository;
use App\Libs\Repository\App\UserRepository;
use App\Libs\Service\App\UserService;
use Nette\Security\User;

class InstallService
{
	public function __construct(
		private array $appConfig,
		private PageRepository $pageRepository,
		private SettingsRepository $settingsRepository,
		private UserRepository $userRepository,
		private User $user,
	)
	{

	}

	public function settings()
	{
		$message = '';
		try {
			$this->settingsRepository->getConn()->query('DROP TABLE ' . $this->settingsRepository->getTableRaw());
		} catch (\Throwable $e) {
		}
		try {
			$table = $this->settingsRepository->createTable();
			$this->settingsRepository->getConn()->query($table);
			$message .= '&bull; Settings table installed <br>';
		} catch (\Throwable $e) {
			$message .= '&bull; SETTINGS ERROR:'.$e->getMessage().' <br>';
		}
		try {

			$this->settingsRepository->insert([
				'pointer' => 'site_name',
				'value' => 'Acme Corp.',
			]);

			$this->settingsRepository->insert([
				'pointer' => 'info_email',
				'value' => 'info@example.org',
			]);

			$this->settingsRepository->insert([
				'pointer' => 'contact_email',
				'value' => 'conteact@example.org',
			]);

			$this->settingsRepository->insert([
				'pointer' => 'contact_phone',
				'value' => '+420 123 123 123',
			]);


			$this->settingsRepository->insert([
				'pointer' => 'user_expiration_pernament',
				'value' => '14 days',
			]);

			$this->settingsRepository->insert([
				'pointer' => 'admin_user_expiration_pernament',
				'value' => '14 days',
			]);


			$this->settingsRepository->insert([
				'pointer' => 'user_expiration_default',
				'value' => '60 minutes',
			]);

			$this->settingsRepository->insert([
				'pointer' => 'admin_user_expiration_default',
				'value' => '60 minutes',
			]);

			$this->settingsRepository->insert([
				'pointer' => 'user_forgotten_password_token_expiration',
				'info' => 'in minutes',
				'value' => '25',
			]);

			$this->settingsRepository->insert([
				'pointer' => 'admin_user_forgotten_password_token_expiration',
				'info' => 'in minutes',
				'value' => '25',
			]);




			return '&bull; Settings installed <br>';
		} catch (\Throwable $e) {
			return '&bull; SETTINGS ERROR: '. $e->getMessage() . '<br>';
		}
	}

	public function pages()
	{
		$message = '';
		try {
			$this->pageRepository->getConn()->query('DROP TABLE ' . $this->pageRepository->getTableRaw());
		} catch (\Throwable $e) {
		}
		try {
			$pageTable = $this->pageRepository->createTable();
			$this->pageRepository->getConn()->query($pageTable);
			$message .= '&bull; Pages table installed <br>';
		} catch (\Throwable $e) {
			$message .= '&bull; PAGES ERROR: '.$e->getMessage().'<br>';
		}

			foreach ($this->appConfig['langs'] as $lang) {
				try {
					$this->pageRepository->insert([
						'parent' => 0,
						'lang' => $lang,
						'title' => 'Homepage ' . $lang,
						'menuName' => 'Home',
						'content' => '<p>Lorem ipsum. Lorem ipsum. Lorem ipsum. Lorem ipsum. </p><p>Lorem ipsum. Lorem ipsum. Lorem ipsum. Lorem ipsum. </p>',
						'uri' => '',
						'active' => 1,
						'menu' => 1,
						'pointer' => 'homepage',
						'presenter' => 'Homepage',
						'rank' => 1,
					]);

					$this->pageRepository->insert([
						'parent' => 0,
						'lang' => $lang,
						'title' => 'Error 404 ' . $lang,
						'menuName' => 'Error 404',
						'content' => '<p>Lorem ipsum. Lorem ipsum. Lorem ipsum. Lorem ipsum. </p>',
						'uri' => '',
						'active' => 1,
						'menu' => 0,
						'pointer' => 'error404',
						'rank' => 2,
					]);

					$id = $this->pageRepository->insert([
						'parent' => 0,
						'lang' => $lang,
						'title' => 'Test',
						'menuName' => 'Test ' . $lang,
						'content' => '',
						'uri' => 'test',
						'active' => 1,
						'menu' => 1,
						'pointer' => 'test',
						'rank' => 3,
					]);

					$this->pageRepository->insert([
						'parent' => $id,
						'lang' => $lang,
						'title' => 'Test subpage ' . $lang,
						'menuName' => 'Test subpage',
						'content' => '<p>Just test</p>',
						'uri' => 'test',
						'active' => 1,
						'menu' => 1,
						'pointer' => 'testSubPage',
						'rank' => 1,
					]);
					$message .= '&bull; Pages '.$lang.' installed <br>';

				} catch (\Throwable $e) {
					$message .= '&bull; PAGES ERROR: '. $e->getMessage() . '<br>';
				}
			}

		return $message;
	}

	public function users()
	{
		try {
			$this->userRepository->getConn()->query('DROP TABLE ' . $this->userRepository->getTableRaw());
		} catch (\Throwable $e) {
		}
		$message = '';
		try {
			$table = $this->userRepository->createTable();
			$this->userRepository->getConn()->query($table);

			$this->userRepository->insert([
				'email' => $this->appConfig['install']['adminEmail'],
				'name' => $this->appConfig['install']['adminName'],
				'roles' => '["user","admin"]',
				'role' => 'webmaster',
				'password' => Password::encode($this->appConfig['install']['adminPassword']),
			]);

			$meesage = '&bull; Users installed <br>';

		} catch (\Throwable $e) {
			$message = '&bull; USERS ERROR:'. $e->getMessage() . '<br>';
		}

		try {
			$this->user->login($this->appConfig['install']['adminEmail'], $this->appConfig['install']['adminPassword']);
			$message .= '&bull; User logged as admin <br>';

		} catch (\Throwable $e) {
			$message .= '&bull; USER LOGIN ERROR:' . $e->getMessage() . '<br>';
		}

		return $message;
	}
}