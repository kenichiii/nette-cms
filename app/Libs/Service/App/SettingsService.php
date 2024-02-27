<?php

declare(strict_types=1);

namespace App\Libs\Service\App;

use App\Libs\Exception\Service\App\Settings\SettingsServiceException;
use App\Libs\Model\App\SettingsModel;
use App\Libs\Repository\App\SettingsRepository;

class SettingsService implements \ArrayAccess
{
	protected ?array $settings = null;
	public function __construct(protected array $appConfig, protected SettingsRepository $settingsRepository)
	{

	}

	public function getAppConfig(): array
	{
		return $this->appConfig;
	}

	public function offsetSet($offset, $value): void
	{
		if (is_null($offset)) {
			$this->settings[] = $value;
		} else {
			$this->settings[$offset] = $value;
		}
	}

	public function offsetExists($offset): bool
	{
		return isset($this->settings[$offset]);
	}

	public function offsetUnset($offset): void
	{
		unset($this->settings[$offset]);
	}

	public function offsetGet($offset): mixed
	{
		return $this->get($offset) ?? null;
	}

	/**
	 * @return SettingsModel[]
	 * @throws \App\Libs\Kenichi\ORM\Exception
	 * @throws \Dibi\Exception
	 */
	public function getSettings(): array
	{
		if ($this->settings === null) {
			$this->settings = $this->settingsRepository->getSelect()->fetchData();
		}
		return $this->settings;
	}

	/**
	 * @param string $pointer
	 * @return string
	 * @throws SettingsServiceException
	 */
	public function get(string $pointer): string
	{
		foreach ($this->getSettings() as $setting) {
			if ($setting->get('pointer')->getValue() === $pointer) {
				return $setting->get('value')->getValue();
			}
		}

		throw new SettingsServiceException("{$pointer} is not in Settings");
	}
}