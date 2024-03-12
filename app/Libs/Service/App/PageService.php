<?php

declare(strict_types=1);

namespace App\Libs\Service\App;

use App\Libs\Exception\Service\App\Page\PageServiceException;
use App\Libs\Model\App\PageModel;
use App\Libs\Repository\App\PageRepository;
use Nette\Caching\Cache;

class PageService
{
	protected ?string $lang = null;
	protected ?array $activePages = null;
	protected ?PageModel $currentPage = null;
	protected ?PageModel $currentRootPage = null;
	protected array $tree = [];
	protected string $slug = '';

	public function __construct(
		protected array $appConfig,
		protected PageRepository $pageRepository,
		protected CacheService $cacheService,
	)
	{

	}

	public function getIsoLang(): string
	{
		return $this->appConfig['isoLangs'][$this->getLang()];
	}

	/**
	 * @return array
	 */
	public function getLangs(): array
	{
		return $this->appConfig['langs'];
	}

	/**
	 * @return string
	 */
	public function getLang(): string
	{
		return $this->lang ?: $this->appConfig['langs'][0];
	}

	public function getDefaultLang(): string
	{
		return 	$this->appConfig['langs'][0];
	}

	protected function findSlug(int $level, array $uri): string
	{
		$slug = '';
		for ($i = $level; $i < count($uri); $i++) {
			$slug .= $uri[$i] ?  $uri[$i] . '/' : '';
		}
		return (string) preg_replace('/(\/)$/', '', $slug);
	}

	public function getSlug(): string
	{
		return $this->slug;
	}

	public function getPageTree(): array
	{
		return $this->tree;
	}

	public function parseUrl(string $url): void
	{
		$uri = explode('/', $url);
		if (!in_array($uri[0], $this->getLangs())) {
			$this->lang = $this->getLangs()[0];
			$level = 0;
		} else {
			$this->lang = $uri[0];
			$level = 1;
		}

		if (!isset($uri[$level])) {
			$currentPage = $this->getPageByPointer('homepage');
			$this->currentRootPage = $currentPage;
		} else {
			$result = $this->findCurrentPage($uri, 0, $level);

			if (is_array($result)) {
				$this->tree = $result[2];
				$this->slug = $this->findSlug($result[1], $uri);
				$currentPage = $result[0];
			} else {
				$currentPage = null;
			}
		}

		if (!$currentPage) {
			$currentPage = $this->getPageByPointer('error404');
			$this->currentRootPage = $currentPage;
			$this->tree = [$currentPage];
			$this->slug = $this->findSlug($level, $uri);
		}

		$this->currentPage = $currentPage;
	}

	/**
	 * @param string $pointer
	 * @return PageModel
	 * @throws PageServiceException
	 * @throws \App\Libs\Kenichi\ORM\Exception
	 * @throws \Dibi\Exception
	 */
	public function getPageByPointer(string $pointer): PageModel
	{
		foreach ($this->getActivePages() as $page) {
			if ($page->get('pointer')->getValue() === $pointer) {
				return $page;
			}
		}
		throw new PageServiceException("{$pointer} dont exists");
	}



	/**
	 * @param string|null $lang
	 * @return PageModel[]
	 * @throws \App\Libs\Kenichi\ORM\Exception
	 * @throws \Dibi\Exception
	 */
	public function getActivePages(): array
	{
		$lang = $this->getLang();
		$key = "pages-active-{$lang}";
		if ($this->activePages === null) {
			$this->activePages = $this->cacheService->getCache()->load($key, function () use ($lang) {
				$select = $this->pageRepository->getSelect();
				$this->activePages = $select->addActiveCond()
					->addDeletedCond()
					->addLangCond($this->getLang())
					->addRankOrderByCond('ASC')
					->fetchData();

				foreach ($this->activePages as $key => $page) {
					$this->activePages[$key]->setRepository(null);
				}

				return $this->activePages;
			});
		}
		return $this->activePages;
	}

	public function getPageContent(int $id): string
	{
		$key = "page-content-{$id}";
		return $this->cacheService->getCache()->load($key, function () use ($id) {
			$page = $this->pageRepository->getByPk($id);
			return $page->get('content')->getValue();
		});
	}

	/**
	 * @return PageModel
	 */
	public function getCurrentPage(): ?PageModel
	{
		return $this->currentPage ?: $this->getPageByPointer('homepage');
	}

	/**
	 * @return PageModel
	 */
	public function getCurrentRootPage(): ?PageModel
	{
		return $this->currentRootPage ?: $this->getPageByPointer('homepage');
	}

	private function findCurrentPage($uris, $parent_id = 0, $level = 1, $tree = [])
	{
		$return = null;

		foreach ($this->getActivePages() as $page) {

			if (isset($uris[$level]) && strtolower($uris[$level]) === strtolower($page->get('uri')->getValue())
				&& $parent_id === $page->get('parent')->getValue()
			) {
				if ($parent_id === 0) {
					$this->currentRootPage = $page;
				}
				$tree []= $page;
				$level++;
				$return = [$page, $level, $tree];

				if (isset($uris[$level])) {
					$return = $this->findCurrentPage($uris, $page->get('id')->getValue(), $level, $tree);
					if ($return === null) {
						$return = [$page, $level, $tree];
					}
				}

				break;
			}
		}

		return $return;
	}

	public function getSubdir(): string
	{
		return $this->appConfig['subdir'] ?: '';
	}

	public function getLangPrefix(): string
	{
		return $this->getLang() === $this->appConfig['langs'][0] ? '': $this->getLang() . '/';
	}

	public function getPageUrl(PageModel $item, ?array $params = null): string
	{
		$prefix =  $this->getSubdir()  . $this->getLangPrefix();
		if ($item->get('parent')->getValue() === 0) {
			$url = '/'. $prefix . ($item->get('uri')->getValue() ? $item->get('uri')->getValue() . '/' : '');
			if (is_array($params) && isset($params['id'])) {
				$url .= $params['id'] .'/';
				unset($params['id']);
			}
			if (is_array($params) && count($params)) {
				$url .= '?' . http_build_query($params);
			}
			return $url;
		}
		$tree = '';
		foreach ($this->getActivePages() as $page) {
			if ($page->get('id')->getValue() === $item->get('parent')->getValue()) {
				$tree .= $this->getParentUri($page);
				$tree .= $page->get('uri')->getValue() . '/';
			}
		}
		$url = '/'. $prefix . $tree . $item->get('uri')->getValue() . '/';
		if (is_array($params) && isset($params['id'])) {
			$url .= $params['id'] .'/';
			unset($params['id']);
		}
		if (is_array($params) && count($params)) {
			$url .= '?' . http_build_query($params);
		}
		return $url;
	}

	protected function getParentUri(PageModel $item): string
	{
		$tree = '';
		foreach ($this->getActivePages() as $page) {
			if ($page->get('id')->getValue() === $item->get('parent')->getValue()) {
				if ($page->get('parent')->getValue() !== 0) {
					$tree .= $this->getParentUri($page);
				}
				$tree .= $page->get('uri')->getValue() . '/';
			}
		}
		return $tree;
	}

	public function hasChildren(PageModel $page): bool
	{
		foreach ($this->getActivePages() as $item) {
			if ($item->get('menu')->getValue()
				&& $item->get('parent')->getValue() === $page->get('id')->getValue()
			) {
				return true;
			}
		}
		return false;
	}

	public function getPageUrlByPointer(string $pointer, ?array $params): string
	{
		return $this->getPageUrl($this->getPageByPointer($pointer), $params);
	}
}
