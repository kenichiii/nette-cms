<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\MainModule\UtilsModule\Presenters;

use App\AppModule\AdminModule\MainModule\Components\Datagrid\Datagrid;
use App\AppModule\AdminModule\MainModule\Components\Datagrid\DatagridFactory;
use App\AppModule\AdminModule\MainModule\UsersModule\Forms\AddNewSettingFormFactory;
use App\AppModule\AdminModule\MainModule\UsersModule\Forms\EditSettingFormFactory;
use App\Libs\Repository\App\UserRepository;
use App\Libs\Repository\ContactFormRepository;
use App\Libs\Repository\ProjectRepository;
use App\Libs\Repository\TestimonialRepository;
use App\Libs\Service\App\CacheService;
use App\Libs\Service\App\SettingsService;
use App\Libs\Service\PhpQueryService;
use App\Libs\Utils\Utils;
use CbowOfRivia\DmarcRecordBuilder\DmarcRecord;
use Nette\Application\UI\Form;

class DefaultPresenter extends \App\AppModule\AdminModule\MainModule\BasePresenter
{
	public function __construct(
		private CacheService $cacheService,
		private ProjectRepository $repository,
	)
	{
	}
	public function renderDefault() {
		$pg = new PhpQueryService();
		$pg->getFirmy();
		exit;
		///$this->repository->getConn()->query('DROP TABLE ' . $this->repository->getTableRaw());
		//$sql = $this->repository->createTable();
		//$this->repository->getConn()->query($sql);
		/*
		$record = new DmarcRecord();

		$record->policy('none')
			->subdomainPolicy('none')
			->pct(100)
			->rua('mailto:kena1@email.cz')
			->ruf('mailto:kena1@email.cz')
			->adkim('relaxed')
			->aspf('relaxed')
			->reporting('any')
			->interval(604800);
			$this->getTemplate()->status = $_SERVER['HTTP_HOST'];
			*/
	}

	public function actionDeleteCache()
	{
		if ($this->isAjax()) {
			//Utils::removeDir('../temp/cache');
			$this->cacheService->cleanCache();
			$this->getPayload()->succ = true;
			$this->flashMessage('Cache has been successfully removed', 'success');
			$this->redrawControl('flashMessages');
			$this->redrawControl('contentWrapper');
		} else {
			$this->redirect(':App:Admin:Main:Dashboard:Default:');
		}

	}
}