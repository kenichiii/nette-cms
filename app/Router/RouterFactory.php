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

	public static function createRouter(
		array $appConfig,
		PageService $pageService,
		Nette\Http\Session $session,
		Nette\Http\Request $request
	): RouteList
	{
		$router = new RouteList;

		$router
			->withModule('App:Admin')
			->addRoute($appConfig['adminUri'].'/<presenter>/<action>/[<id>]', 'Main:Dashboard:Default:default');

		$router
			->withModule('App:Front')
			->addRoute('/<uri .+>', [
			null => [
				Route::FILTER_IN => function (array $params) use ($appConfig, $pageService, $session) {
					if ($appConfig['devMode'] && !$session->getSection('_dev_mode')->get('pwd')) {
						return [
							'presenter' => 'DevMode',
							'action' => 'default',
							'id' => null,
						];
					} else {
						if ($uri = str_replace((string)$appConfig['subdir'],'', $params['uri'])) {

							$pageService->parseUrl($uri);

							return [
								'presenter' => $pageService->getCurrentPage()->get('presenter')->getValue(),
								'action' => $pageService->getCurrentPage()->get('action')->getValue(),
								'id' => $pageService->getSlug(),
								'params' => $params,
							];

						} else {
							return [
								'presenter' => 'Homepage',
								'action' => 'default',
								'id' => null,
							];
						}

					}
				},/*
				Route::FILTER_OUT => function (array $params) use ($pageService, $appConfig) {

					try {
						$page = $pageService->getPageByPointer($params['action']);
					} catch (\Throwable $e) {
						return ['url'=>'/test'];
					}

					return [
						'uri' => $pageService->getPageUrl($page),
						'params' => $params['params'],
					];
				},*/
			],
		]);

		if($request->getUrl()->getPath() === '/'.$appConfig['subdir'] && $appConfig['devMode']
			&& !$session->getSection('_dev_mode')->get('pwd')
		) {
			$router->withModule('App:Front')->addRoute('<presenter=DevMode>/<action=default>');
		} elseif ($request->getUrl()->getPath() === '/'.$appConfig['subdir']) {
			$router->withModule('App:Front')->addRoute('<presenter=Homepage>/<action=default>');
		}

		return $router;
	}
}
