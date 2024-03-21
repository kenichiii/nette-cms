<?php

declare(strict_types=1);

namespace App\AppModule\FrontModule\Presenters;

use App\Libs\Repository\ProjectRepository;
use App\Libs\Repository\SliderRepository;
use App\Libs\Repository\TestimonialRepository;
use App\Libs\Service\App\CacheService;

class HomepagePresenter extends BasePresenter
{
	public function  __construct(
		private SliderRepository $sliderRepository,
		private TestimonialRepository $testimonialRepository,
		private ProjectRepository $projectRepository,
		private CacheService $cacheService,
	)
	{
	}

	public function renderDefault()
	{

		$this->getTemplate()->content = $this->pageService->getPageContent(
			$this->pageService->getCurrentPage()->get('id')->getValue()
		);
		/*
		$key = 	'projects-'.$this->lang;
		$this->getTemplate()->projects = $this->cacheService->getCache()->load($key, function () {
			$data = $this->projectRepository->getSelect()
				->addLangCond($this->lang)
				->addDeletedCond()
				->addActiveCond()
				->orderBy('rank', 'asc')
				->fetchData() ?: [];
			foreach ($data as $key => $value) {
				$data[$key]->setRepository(null);
			}
			return $data;
		});
		*/
		$key = 	'testimonials-'.$this->lang;
		$this->getTemplate()->testimonials = $this->cacheService->getCache()->load($key, function () {
			$data = $this->testimonialRepository->getSelect()
				->addLangCond($this->lang)
				->addDeletedCond()
				->addActiveCond()
				->orderBy('rank', 'asc')
				->fetchData() ?: [];
			foreach ($data as $key => $value) {
				$data[$key]->setRepository(null);
			}
			return $data;
		});


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