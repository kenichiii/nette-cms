<?php

declare(strict_types=1);

namespace App\Libs\NA\Service;


use App\Libs\NA\Repository\ActionRepository;
use App\Libs\NA\Repository\DailyThoughRepository;
use App\Libs\NA\Repository\GroupRepository;
use App\Libs\NA\Repository\MeetingRepository;
use App\Libs\Repository\App\PageRepository;
use App\Libs\Service\App\ClientInstallService;
use App\Libs\Service\App\SettingsService;

class InstallService implements ClientInstallService
{
	public function __construct(
		protected GroupRepository $groupRepository,
		protected MeetingRepository $meetingRepository,
		protected DailyThoughRepository $dailyThoughRepository,
		protected PageRepository $pageRepository,
		protected SettingsService $settingsService,
		protected ActionRepository $actionRepository,
	)
	{

	}

	public function install(): string
	{
		$message = '<h2>Narcotics Anonymous</h2>';
		$message.= $this->groups();
		$message.= $this->meetings();
		$message.= $this->dailyThoughs();
		$message.= $this->actions();
		$message.= $this->pages();
		return $message;
	}

	public function groups()
	{
		try {
			$this->groupRepository->getConn()->query('DROP TABLE ' . $this->groupRepository->getTableRaw());
		} catch (\Throwable $e) {

		}
		$message = '';
		try {
			$table = $this->groupRepository->createTable();
			$this->groupRepository->getConn()->query($table);

			$meesage = '&bull; NA Groups installed <br>';

		} catch (\Throwable $e) {
			$message = '&bull; NA Groups ERROR:' . $e->getMessage() . '<br>';
		}
		return $meesage;
	}

	public function meetings()
	{
		try {
			$this->meetingRepository->getConn()->query('DROP TABLE ' . $this->meetingRepository->getTableRaw());
		} catch (\Throwable $e) {

		}
		$message = '';
		try {
			$table = $this->meetingRepository->createTable();
			$this->meetingRepository->getConn()->query($table);

			$meesage = '&bull; NA Meetings installed <br>';

		} catch (\Throwable $e) {
			$message = '&bull; NA Meetings ERROR:' . $e->getMessage() . '<br>';
		}
		return $meesage;
	}

	public function dailyThoughs()
	{

		try {
			$this->dailyThoughRepository->getConn()->query('DROP TABLE ' . $this->dailyThoughRepository->getTableRaw());
		} catch (\Throwable $e) {

		}
		$message = '';
		try {
			$table = $this->dailyThoughRepository->createTable();
			$this->dailyThoughRepository->getConn()->query($table);

			$message = '&bull; NA Daily Thoughs installed <br>';

		} catch (\Throwable $e) {
			$message = '&bull; NA Daily Thoughs ERROR:' . $e->getMessage() . '<br>';
		}
		return $message;
	}


	public function actions()
	{

		try {
			$this->actionRepository->getConn()->query('DROP TABLE ' . $this->actionRepository->getTableRaw());
		} catch (\Throwable $e) {

		}
		$message = '';
		try {
			$table = $this->actionRepository->createTable();
			$this->actionRepository->getConn()->query($table);

			$message = '&bull; NA Actions installed <br>';

		} catch (\Throwable $e) {
			$message = '&bull; NA Actions ERROR:' . $e->getMessage() . '<br>';
		}
		return $message;
	}

	public function pages()
	{
		$message = '';
		foreach ($this->settingsService->getAppConfig()['langs'] as $lang) {
			try {
				$this->pageRepository->insert([
					'parent' => 0,
					'lang' => $lang,
					'title' => 'DailyThough ' . $lang,
					'menuName' => 'Daily Though',
					'content' => '',
					'uri' => 'daily-though-'.$lang,
					'active' => 1,
					'menu' => 1,
					'pointer' => 'dailyThough',
					'presenter' => 'NA',
					'action' => 'dailyThough',
					'rank' => 4,
				]);

				$this->pageRepository->insert([
					'parent' => 0,
					'lang' => $lang,
					'title' => 'NA Actions ' . $lang,
					'menuName' => 'NA Actions',
					'content' => '',
					'uri' => 'na-actions-'.$lang,
					'active' => 1,
					'menu' => 1,
					'pointer' => 'naActions',
					'presenter' => 'NA',
					'action' => 'actions',
					'rank' => 5,
				]);


				$this->pageRepository->insert([
					'parent' => 0,
					'lang' => $lang,
					'title' => 'NA Action ' . $lang,
					'menuName' => 'NA Action',
					'content' => '',
					'uri' => 'na-action-'.$lang,
					'active' => 1,
					'menu' => 0,
					'pointer' => 'naAction',
					'presenter' => 'NA',
					'action' => 'action',
					'rank' => 6,
				]);

				$this->pageRepository->insert([
					'parent' => 0,
					'lang' => $lang,
					'title' => 'NA Meetings ' . $lang,
					'menuName' => 'NA Meetings',
					'content' => '',
					'uri' => 'na-meetings-'.$lang,
					'active' => 1,
					'menu' => 1,
					'pointer' => 'naMeetings',
					'presenter' => 'NA',
					'action' => 'meetings',
					'rank' => 7,
				]);


				$this->pageRepository->insert([
					'parent' => 0,
					'lang' => $lang,
					'title' => 'NA Meeting ' . $lang,
					'menuName' => 'NA Meeting',
					'content' => '',
					'uri' => 'na-meeting-'.$lang,
					'active' => 1,
					'menu' => 0,
					'pointer' => 'naMeeting',
					'presenter' => 'NA',
					'action' => 'meeting',
					'rank' => 8,
				]);

				$message .= '&bull; NA Pages '.$lang.' installed <br>';
			} catch (\Throwable $e) {
				$message .= '&bull; NA PAGES ERROR: '. $e->getMessage() . '<br>';
			}
		}
		return $message;
	}
}