<?php

declare(strict_types=1);

namespace App\Libs\Kenichi\ORM\Column;

use App\Libs\Kenichi\ORM\Validation;

abstract class Column
{
	protected string $sqlName;

	protected string $dibiModificator;

	protected ?string $name = null;
	protected ?string $rawName = null;
	protected ?string $columnName = null;

	protected mixed $value = null;
	protected mixed $default = null;

	protected bool $notnull = false;
	protected bool $primaryKey = false;
	protected bool $key = false;
	protected bool $unique = false;
	protected mixed $uniqueWith = null;
	protected bool $innerSql = false;

	protected bool $isChange = false;

	protected bool $sanitize = true;

	protected bool $isInData = true;

	protected bool $isInForm = true;


	/**
	 * @return string
	 */
	public function getRawName(): string
	{
		if ($this->rawName === null) {
			$class = get_called_class();
			$pieces = explode('\\', $class);
			$this->rawName = strtolower(end($pieces));
		}

		return $this->rawName;
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	public function setRawName(string $name): Column
	{
		$this->rawName = $name;

		if ($this->name === null) {
			$this->name = $name;
		}

		return $this;
	}

	/**
	 * @param mixed $value
	 * @param string|null $from
	 * @return $this
	 */
	public function set(mixed $value, ?string $from = null): Column
	{
		if ($from === 'db') {
			$this->setfromdb($value);
		} elseif ($from === 'form') {
			$this->setfromform($value);
		} else {
			$this->isChange = true;
			$this->value = $value;
		}

		return $this;
	}

	/**
	 * @param mixed $value
	 * @return $this
	 */
	public function setfromdb(mixed $value): Column
	{
		$this->isChange = false;
		$this->value = $value;
		return $this;
	}

	/**
	 * @param mixed $value
	 * @return $this
	 */
	public function setfromform(mixed $value): Column
	{
		$this->isChange = true;

		if ($this->isSanitize()) {
			$this->value = $this->sanitize($value);
		} else {
			$this->value = is_string($value) ? trim($value) : $value;
		}
		return $this;
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return (string) $this->getValue();
	}

	public function getValue(): mixed
	{
		return $this->value !== null ? $this->value : $this->getDefault();
	}

	public function getColumn()
	{
		return $this->name ?: $this->getRawName();
	}


	public function validate(string $formAction = null, mixed $data = null): Validation
	{
		$val = new Validation();

		if ($this->isNotNull() && ( $this->getValue() === null || ($this->getValue() !== 0 && $this->getValue() === ''))) {
			$val->addError('notnull', $this->getColumn());
		}
		return $val;
	}

	/**
	 * @param mixed $data
	 * @return $this
	 */
	public function fromForm(mixed $data): Column
	{
		foreach($data as $key=>$value)
		{
			if ($this->getColumn() == $key) {
				$this->setfromform($value);
			}
		}

		return $this;
	}

	/**
	 * @param string $string
	 * @return string
	 */
	public static function sanitize($string): string
	{
		return htmlspecialchars(trim((string)$string));
	}

	/**
	 * @return string
	 */
	public function getSqlName(): string
	{
		return $this->sqlName;
	}

	/**
	 * @param string $sqlName
	 * @return Column
	 */
	public function setSqlName(string $sqlName): Column
	{
		$this->sqlName = $sqlName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDibiModificator(): string
	{
		return $this->dibiModificator;
	}

	/**
	 * @param string $dibiModificator
	 * @return Column
	 */
	public function setDibiModificator(string $dibiModificator): Column
	{
		$this->dibiModificator = $dibiModificator;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getName(): ?string
	{
		return $this->name ?: $this->getRawName();
	}

	/**
	 * @param string|null $name
	 * @return Column
	 */
	public function setName(?string $name): Column
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getColumnName(): ?string
	{
		return $this->columnName ?: $this->getRawName();
	}

	/**
	 * @param string|null $columnName
	 * @return Column
	 */
	public function setColumnName(?string $columnName): Column
	{
		$this->columnName = $columnName;
		return $this;
	}

	/**
	 * @return mixed|null
	 */
	public function getDefault(): mixed
	{
		return $this->default;
	}

	/**
	 * @param mixed|null $default
	 * @return Column
	 */
	public function setDefault(mixed $default): Column
	{
		$this->default = $default;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isNotnull(): bool
	{
		return $this->notnull;
	}

	/**
	 * @param bool $notnull
	 * @return Column
	 */
	public function setNotnull(bool $notnull): Column
	{
		$this->notnull = $notnull;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isPrimaryKey(): bool
	{
		return $this->primaryKey;
	}

	/**
	 * @param bool $primaryKey
	 * @return Column
	 */
	public function setPrimaryKey(bool $primaryKey): Column
	{
		$this->primaryKey = $primaryKey;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isKey(): bool
	{
		return $this->key;
	}

	/**
	 * @param bool $key
	 * @return Column
	 */
	public function setKey(bool $key): Column
	{
		$this->key = $key;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isUnique(): bool
	{
		return $this->unique;
	}

	/**
	 * @param bool $unique
	 * @return Column
	 */
	public function setUnique(bool $unique): Column
	{
		$this->unique = $unique;
		return $this;
	}

	/**
	 * @return mixed|null
	 */
	public function getUniqueWith(): mixed
	{
		return $this->uniqueWith;
	}

	/**
	 * @param mixed|null $uniqueWith
	 * @return Column
	 */
	public function setUniqueWith(mixed $uniqueWith): Column
	{
		$this->uniqueWith = $uniqueWith;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isInnerSql(): bool
	{
		return $this->innerSql;
	}

	/**
	 * @param bool $innerSql
	 * @return Column
	 */
	public function setInnerSql(bool $innerSql): Column
	{
		$this->innerSql = $innerSql;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isChange(): bool
	{
		return $this->isChange;
	}

	/**
	 * @param bool $isChange
	 * @return Column
	 */
	public function setIsChange(bool $isChange): Column
	{
		$this->isChange = $isChange;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isSanitize(): bool
	{
		return $this->sanitize;
	}

	/**
	 * @param bool $sanitize
	 * @return Column
	 */
	public function setSanitize(bool $sanitize): Column
	{
		$this->sanitize = $sanitize;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isInData(): bool
	{
		return $this->isInData;
	}

	/**
	 * @param bool $isInData
	 * @return Column
	 */
	public function setIsInData(bool $isInData): Column
	{
		$this->isInData = $isInData;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isInForm(): bool
	{
		return $this->isInForm;
	}

	/**
	 * @param bool $isInForm
	 * @return Column
	 */
	public function setIsInForm(bool $isInForm): Column
	{
		$this->isInForm = $isInForm;
		return $this;
	}

	public function isModel(): bool
	{
		return false;
	}

	public function isGroup(): bool
	{
		return false;
	}

	public function isColumn(): bool
	{
		return true;
	}
}
