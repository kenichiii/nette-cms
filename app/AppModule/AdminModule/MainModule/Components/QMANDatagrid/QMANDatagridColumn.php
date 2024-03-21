<?php declare(strict_types = 1);

namespace App\Libs\Components\QMANDatagrid;

class QMANDatagridColumn
{
	public function __construct(protected string $key, protected array $definition)
	{

	}

	public function getNameCapitalized(): string
	{
		return ucfirst($this->key);
	}

	public function getName(): string
	{
		return $this->key;
	}

	public function getType(): string
	{
		return $this->definition['type'] ?? 'text';
	}

	public function getTitle(): string
	{
		return $this->definition['title'] ?? $this->key;
	}

	public function isSorting(): bool
	{
		return $this->definition['sorting'] ?? true;
	}

	public function getOptions(): array
	{
		return $this->definition['options'] ?? [];
	}

	public function renderCell(): ?callable
	{
		return $this->definition['render'] ?? null;
	}

}