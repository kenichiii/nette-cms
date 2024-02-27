<?php

declare(strict_types=1);

namespace App\AppModule\FrontModule\Presenters;

class TextpagePresenter extends BasePresenter
{
	public function renderDefault(string $id)
	{
		if (!$this->pageService->getCurrentPage()['content']->getValue()) {
			$child = null;
			foreach ($this->pageService->getActivePages() as $page) {
				if ($page['parent']->getValue() === $this->pageService->getCurrentPage()['id']->getValue()) {
					$child = $page;
					break;
				}
			}
			if ($child) {
				$this->redirect($child['pointer']->getValue());
			}
		}

		$this->getTemplate()->id = $id;
	}
}