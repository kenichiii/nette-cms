<?php

declare(strict_types=1);

namespace App\AppModule\FrontModule\Presenters;

use App\Libs\Repository\SliderRepository;
use App\Libs\Service\App\CacheService;

class HomepagePresenter extends BasePresenter
{
	public function  __construct(
		private SliderRepository $sliderRepository,
		private CacheService $cacheService,
	)
	{
	}

	public function renderDefault()
	{

		$this->getTemplate()->content = $this->pageService->getPageContent(
			$this->pageService->getCurrentPage()->get('id')->getValue()
		);

		$key = 	'sliders-sliders-'.$this->lang;
		$this->getTemplate()->sliders = $this->cacheService->getCache()->load($key, function () {
			$data = $this->sliderRepository->getSelect()
				->addLangCond($this->lang)
				->addDeletedCond()
				->addActiveCond()
				->andWhere('section', 'sliders')
				->orderBy('rank', 'asc')
				->fetchData() ?: [];
			foreach ($data as $key => $value) {
				$data[$key]->setRepository(null);
			}
			return $data;
		});


		$key = 	'sliders-panels-'.$this->lang;
		$this->getTemplate()->panels = $this->cacheService->getCache()->load($key, function () {
			$data = $this->sliderRepository->getSelect()
				->addLangCond($this->lang)
				->addDeletedCond()
				->addActiveCond()
				->andWhere('section', 'panels')
				->orderBy('rank', 'asc')
				->fetchData() ?: [];
			foreach ($data as $key => $value) {
				$data[$key]->setRepository(null);
			}
			return $data;
		});
	}
}