<?php

declare(strict_types=1);

namespace App\AppModule\FrontModule\Presenters;

use App\Libs\Repository\ProjectRepository;
use App\Libs\Repository\TestimonialRepository;
use App\Libs\Service\App\CacheService;

class ProjectPresenter extends BasePresenter
{
	public function __construct(
		private CacheService $cacheService,
		private ProjectRepository $projectRepository,
		private TestimonialRepository $testimonialRepository,
	)
	{
	}

	public function renderDefault(string $id)
	{
		$key = 'project-'.$id;

		$result = $this->cacheService->getCache()->load($key, function () use ($id)  {
			$select = $this->projectRepository->getSelect();
			$model = $select->addDeletedCond()->addActiveCond()
				->andWhere('uri', $id)
				->andWhere('lang', $this->lang)
				->fetchSingle();
			if ($model) {
				$model->setRepository(null);
				if ($model['testimonial']->getValue()) {
					$testimonial = $this->testimonialRepository->getSelect()
						->addLangCond($this->lang)
						->addDeletedCond()
						->andWhere('id', $model['testimonial']->getValue())
						->fetchSingle();
					if ($testimonial) {
						$testimonial->setRepository(null);
					}
				}
			}
			return ['project' => $model, 'testimonial' => $testimonial];
		});
		if ($result['project']) {
			$title = 'webčerka: '.$result['project']['title']->getValue();
			$this->pageService->getCurrentPage()->set('title', $title);
			$this->pageService->getCurrentPage()->set('description', $result['project']['description']->getValue());
		}

		$this->getTemplate()->project = $result['project'];
		$this->getTemplate()->testimonial = $result['testimonial'];
	}
}