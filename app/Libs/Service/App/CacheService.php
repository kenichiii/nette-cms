<?php

declare(strict_types=1);

namespace App\Libs\Service\App;

use Nette;
use Nette\Caching\Cache;

final class CacheService
{
	public function __construct(
		private Nette\Caching\Cache $cache,
	)
	{
	}
    public function getCache(): Nette\Caching\Cache
	{
		return $this->cache;
	}
	public function removeTag(string|array $tag): void
	{
		$this->cache->clean([
			Cache::Tags => is_string($tag) ? [$tag] : $tag
		]);
	}

	public function removeKey(string $key): void
	{
		$this->cache->remove($key);
	}

	public function cleanCache(): void
	{
		$this->cache->clean([
			$this->cache::All => true,
		]);
	}

}