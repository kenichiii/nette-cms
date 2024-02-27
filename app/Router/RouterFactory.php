<?php

declare(strict_types=1);

namespace App\Router;

use App\Libs\Service\App\PageService;
use  Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;

final class RouterFactory
{
	use Nette\StaticClass;

	public static function createRouter(array $appConfig, PageService $pageService): RouteList
	{
		$router = new RouteList;

		$router
			->withModule('App:Admin')
			->addRoute($appConfig['adminUri'].'/<presenter>/<action>/[<id>]', 'Main:Dashboard:Default:default');

		$router
			->withModule('App:Front')
			->addRoute('/<uri .+>', [
			null => [
				Route::FILTER_IN => function (array $params) use ($appConfig, $pageService) {
					if ($uri = str_replace($appConfig['subdir'],'', $params['uri'].'/')) {
						bdump($uri);
						$pageService->parseUrl($uri);
						bdump($pageService->getCurrentPage());
						return [
							'presenter' => $pageService->getCurrentPage()->get('presenter')->getValue(),
							'action' => $pageService->getCurrentPage()->get('action')->getValue(),
							'id' => $pageService->getSlug(),
						];
					} else {
						return [
							'presenter' => 'Homepage',
							'action' => 'default',
							'id' => null,
						];
					}
				},
				Route::FILTER_OUT => function (array $params) use ($pageService, $appConfig) {

					try {
						$page = $pageService->getPageByPointer($params['action']);
					} catch (\Throwable $e) {
						return ['url'=>'/test'];
					}

					return [
						'uri' => $pageService->getPageUrl($page),
					];
				},
			],
		]);

		return $router;
	}
}
