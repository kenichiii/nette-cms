<?php

declare(strict_types=1);

namespace App\Libs\Kenichi\ORM;
use App\Libs\Kenichi\ORM\Column\Column;
abstract class ColumnGroup
{
	protected ?Model $parentModel;

	protected array $model = [];

	protected ?string $name = null;
	protected ?string $rawName = null;

	protected bool $isInData = true;

	protected bool $isInForm = true;

	public function __construct() {
		$this->initModel();
	}

	abstract protected function initModel();

	public function offsetSet($offset, $value): void
	{
		if (is_null($offset)) {
			//$this->getModel()[] = $value;
		} else {
			$this->set($offset, $value);
		}
	}

	public function offsetExists($offset): bool
	{
		try {
			return (bool) $this->get($offset);
		} catch (\Throwable $e) {
			return false;
		}
	}

	public function offsetUnset($offset): void
	{
		$this->removeColumn($offset);
	}

	public function offsetGet($offset): mixed
	{
		return $this->get($offset);
	}


	/**
	 * @return bool
	 */
	public function isInForm(): bool
	{
		return $this->isInForm;
	}

	/**
	 * @param bool $bool
	 * @return $this
	 */
	public function setInForm(bool $bool): ColumnGroup
	{
		$this->isInForm = $bool;
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
	 * @param bool $bool
	 * @return $this
	 */
	public function setInData(bool $bool): ColumnGroup
	{
		$this->isInData = $bool;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRawName(): string
	{
		if($this->rawName === null) {
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
	public function setRawName(string $name): ColumnGroup
	{
		$this->rawName = strtolower($name);
		return $this;
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function has(string $name): bool
	{
		$name = strtolower($name);

		$expl = explode('_', $name);

		if (count($expl) === 1)  {
			return isset($this->model[$name]);
		}
		elseif ($this->has($expl[0])) {
				$col = $this->get($expl[0]);
				$pieces = [];
				for ($i = 1; $i < count($expl); $i++)  {
					$pieces []= $expl[$i];
				}

				if ($col->isGroup()) {
					return $col->has(implode ('_', $pieces));
				} else {
					return false;
				}
		}

		return false;
	}

	/**
	 * @param string|Column|ColumnGroup $name
	 * @param string|null $class
	 * @return $this
	 */
	public function addColumn(string|Column|ColumnGroup $name, Column|ColumnGroup|null $class = null): ColumnGroup
	{
		if(is_object($name)) {
			$class = $name;
			$name = $class->getName();
		}

		$name = strtolower($name);

		$this->model[$name]= $class;
		$this->model[$name]->setRawName($name);
		//$this->model[$name]->setOwnerModel($this->ownerModel());

		return $this;
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	public function removeColumn(string $name): ColumnGroup
	{
		$name = strtolower($name);
		if(array_key_exists($name, $this->model)) {
			unset($this->model[$name]);
		}
		return $this;
	}

	/**
	 * @return array
	 */
	public function getModel(): array
	{
		return $this->model;
	}

	/**
	 * @param Model $class
	 * @return void
	 */
	public function setParentModel(Model $class): void
	{
		$this->parentModel = $class;
	}

	/**
	 * @return Model|null
	 */
	public function getParentModel(): ?Model
	{
		return $this->parentModel;
	}

	/**
	 * @return $this
	 */
	public function clearModel(): ColumnGroup
	{
		$this->model = [];
		return $this;
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	public function setName(string $name): ColumnGroup
	{
		$name = strtolower($name);

		$this->name = $name;
		if($this->isGroup()) {
			$this->buildGroup($name.'_');
		}

		return $this;
	}

	/**
	 * @param string $prefix
	 * @return void
	 */
	protected function buildGroup(string $prefix = ''): void
	{
		foreach ($this->model as $key => $child) {
			if ($child->isGroup()) {
				$child->buildGroup($prefix . $child->getName() . '_');
			}
			elseif ($child->isPrimitive()) {
				$child->setName($prefix . $child->rawName());
			}
		}
	}

	/**
	 * @param string $child
	 * @param mixed $value
	 * @param string|null $from
	 * @return $this
	 * @throws Exception
	 */
	public function set(string $child, mixed $value, string $from = null): ColumnGroup
	{
		$child = strtolower($child);

		$test = explode('_', $child);

		$rek = [];
		for ($i = 1; $i < count($test); $i++) {
			$rek []= $test[$i];
		}

		if (isset($this->model[$test[0]]) && count($rek)
			&& ! $this->model[$test[0]]->isPrimitive()
		) {
			$this->model[$test[0]]->set(implode('_', $rek), $value, $from);
		}
		elseif (isset($this->model[$test[0]]) && !count($rek) ) {
			$this->model[$test[0]]->set($value, $from);
		}
		else {
			throw new Exception ("{$this->getName()} cant set {$child}");
		}

		return $this;
	}


	/**
	 * @param string $child
	 * @return Column|ColumnGroup
	 * @throws Exception
	 */
	public function get(string $child): Column|ColumnGroup
	{
		$child = strtolower($child);

		$test = explode('_', $child);

		$rek = [];
		for($i = 1; $i < count($test); $i++) {
			$rek []= $test[$i];
		}


		if (isset($this->model[$test[0]]) && count($rek) > 0 ) {
			return $this->model[$test[0]]->get(implode('_', $rek));
		} elseif (isset($this->model[$test[0]]) && !count($rek)) {
			return $this->model[$test[0]];
		} else {
			throw new Exception ("{$this->getName()} cant get {$child}");
		}
	}

	/**
	 * @param string|null $formAction
	 * @param mixed|null $data
	 * @return Validation
	 * @throws Exception
	 */
	public function validate(?string $formAction = null, mixed $data = null): Validation
	{
		$val = new Validation();

		foreach ($this->model as $key => $value) {
			if ($value->isColumn()) {
				$val->add($this->get($key)->validate($formAction, $data));
			} elseif($value->isGroup()) {
				foreach($value->model as $mkey => $mvalue) {
					if ($mvalue->isColumn()) {
						$val->add($value->get($mkey)->validate($formAction, $data));
					} elseif($mvalue->isMixed()) {
						$val->add($this->validateGroup($mvalue, $formAction, $data));
					}
				}
			}
		}

		return $val;
	}

	/**
	 * @param ColumnGroup $group
	 * @param string $formAction
	 * @param array|null $data
	 * @return Validation
	 * @throws Exception
	 */
	protected function validateGroup(ColumnGroup $group, string $formAction, ?array $data): Validation
	{
		$val = new Validation();
		foreach ($group->model() as $key => $value) {
			if($value->isColumn()) {
				$val->add($this->get($key)->validate($formAction, $data));
			} elseif ($value->isGroup()) {
				foreach ($value->model as $mkey => $mvalue) {
					if($mvalue->isColumn()) {
						$val->add($this->get($mkey)->validate($formAction, $data));
					} elseif($mvalue->isGroup()) {
						$val->add($this->validateGroup($mvalue, $formAction, $data));
					}
				}
			}
		}

		return $val;
	}


	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name ?: $this->getRawName();
	}

	/**
	 * @return bool
	 */
	public function isModel(): bool
	{
		return false;
	}

	/**
	 * @return bool
	 */
	public function isPrimary(): bool
	{
		return false;
	}

	/**
	 * @return bool
	 */
	public function isGroup(): bool
	{
		return true;
	}

	/**
	 * @return bool
	 */
	public function isColumn(): bool
	{
		return false;
	}

	/**
	 * @return bool
	 */
	public function isInnerSql(): bool
	{
		return false;
	}

	/**
	 * @return bool
	 */
	public function isPrimaryKey(): bool
	{
		return false;
	}

	/**
	 * @return Column[]
	 */
	public function getColumnsRaw(): array
	{
		$columns = [];

		foreach($this->model as $key => $child)	{
			if ($child->isInnerSql()) {
				continue;
			} elseif ($child->isColumn()) {
				$columns []= $child;
			} elseif ($child->isGroup()) {
				$columns = array_merge($columns,$child->getColumnsRaw());
			}
		}

		return $columns;
	}

	/**
	 * @return array
	 */
	public function getColumnsValues(): array
	{
		$columns = [];

		foreach($this->model as $key => $child)	{
			if( $child->isInnerSql()) {
				continue;
			} elseif ($child->isColumn()) {
				$columns [$child->getColumn()]= $child->getValue();
			} elseif($child->isGroup()) {
				$columns = array_merge($columns, $child->getColumnsValues());
			}
		}

		return $columns;
	}

	/**
	 * @return array
	 */
	public function getColumnsNames(): array
	{
		$columns = [];

		foreach($this->model as $key=>$child) {
			if ($child->isInnerSql() || ($child->isColumn() && $child->isPrimaryKey())) {
				continue;
			} elseif($child->isColumn()) {
				$columns [$child->getColumnName()]= $child->getValue();
			} elseif($child->isGroup()) {
				$columns = array_merge($columns,$child->getColumnsNames());
			}
		}

		return $columns;
	}

	/**
	 * @return array
	 */
	public function getColumnsForUpdate(): array
	{
		$columns = array();

		foreach($this->model as $key=>$child) {
			if ($child->isInnerSql() || ($child->isColumn() && $child->isPrimaryKey()))	{
				continue;
			} elseif ($child->isColumn() && $child->isChange())	{
				$columns [$child->getColumn()]= $child->getValue();
			} elseif($child->isGroup())	{
				$columns = array_merge($columns, $child->getColumnsForUpdate());
			}
		}

		return $columns;
	}

	/**
	 * @return array
	 */
	public function getColumnsNamesForUpdate(): array
	{
		$columns = [];

		foreach($this->model as $key=>$child) {
			if ($child->isInnerSql() || ($child->isPrimaryKey())) {
				continue;
			} elseif ($child->isColumn() && $child->isChange())	{
				$columns [$child->getColumnName()]= $child->getValue();
			} elseif ($child->isGroup()) {
				$columns = array_merge($columns, $child->getColumnsNamesForUpdate());
			}
		}

		return $columns;
	}

	/**
	 * @param array $data
	 * @return $this
	 * @throws Exception
	 */
	public function fromForm(?array $data): ColumnGroup
	{

		foreach ($this->getColumnsRaw() as $key => $child) {
			$this->get($child->getColumn())->fromForm($data);
		}

		return $this;
	}
}
