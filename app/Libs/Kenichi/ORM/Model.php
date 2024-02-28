<?php

declare(strict_types=1);

namespace App\Libs\Kenichi\ORM;

use Nette\Utils\Json;
use App\Libs\Kenichi\ORM\Column\Column;

abstract class Model extends ColumnGroup implements  \ArrayAccess
{
	abstract protected function initModel();

	const FORM_ACTION_NEW = 'new';
	const FORM_ACTION_EDIT = 'edit';

	protected ?string $repositoryClassName = null;
	protected ?Repository $repository = null;

	protected array $relations = [];

	protected bool $isJoin = false;
	protected bool $isRelation11 = false;
	protected bool $isRelationN1 = false;
	protected bool $isRelationNN = false;

	protected string $relationFromThisColumnName;
	protected string $relationToAnotherModelColumnName;

	public function __construct(?Model $class = null) {
		$this->parentModel = $class;
		$this->initModel();
	}

	public function offsetUnset($offset): void
	{
		if ($this->modelHas($offset)) {
			$this->removeColumn($offset);
		} else {
			$this->removeRelation($offset);
		}
	}

	/**
	 * @param $id
	 * @return Model
	 */
	public static function loadByPK(int $id): Model
	{
		$acc = get_called_class();
		$ins = new $acc();
		return $ins->getRepository()->getByPK($id);
	}

	/**
	 * @param $uri
	 * @return Model
	 */
	public static function loadByUri(string $uri): Model
	{
		$acc = get_called_class();
		$ins = new $acc();
		return $ins->getrepository()->getByUri($uri);
	}

	/**
	 * @param $pointer
	 * @return Model
	 */
	public static function loadByPointer(string $pointer): Model
	{
		$acc = get_called_class();
		$ins = new $acc();
		return $ins->getrepository()->getByPointer($pointer);
	}

	/**
	 * @param array $rels
	 * @return $this
	 */
	public function setRelations(array $rels): Model
	{
		$this->relations = $rels;
		return $this;
	}

	/**
	 * @return Model[]
	 */
	public function getRelations(): array
	{
		return $this->relations;
	}
	/**
	 * @param string|Model $class
	 * @return bool
	 * @throws Exception
	 */
	public function isNotParentModel(string|Model $class): bool
	{
		if (!$this->getParentModel()) {
			return true;
		} elseif (is_string($class)) {
			return $class !== get_class($this->getParentModel());
		} else {
			return get_class($class) !== get_class($this->getParentModel());
		}
	}

	/**
	 * @param string|Column|ColumnGroup $name
	 * @param Column|ColumnGroup|null $model
	 * @return $this
	 */
	public function modeladd(string|Column|ColumnGroup $name, Column|ColumnGroup|null $model = null): Model
	{
		if (is_object($name)) {
			$model = $name;
			$name = $model->getName();
		}

		$name = strtolower($name);

		$this->model[$name] = $model;
		$this->model[$name]->setRawName($name);
		//$this->model[$name]->setOwnerModel();

		return $this;
	}


	/**
	 * @param string $name
	 * @return bool
	 * @throws Exception
	 */
	public function has(string $name): bool
	{
		$name = strtolower($name);
		$expl = explode('_',$name);

		if (count($expl) === 1)	{
			return isset($this->relations[$name]) || isset($this->model[$name]);
		} else {
			if($this->has($expl[0])) {
				$coll = $this->get($expl[0]);

				$pieces = array();
				for ($i = 1; $i < count($expl); $i++) {
					$pieces []= $expl[$i];
				}

				if($coll->isGroup()) {
					return $coll->has(implode ('_', $pieces));
				} elseif($coll->isModel()) {
					return $coll->has(implode ('_', $pieces));
				} else {
					return false;
				}
			}

			return false;
		}
	}

	/**
	 * @param string $name
	 * @return bool
	 * @throws Exception
	 */
	public function modelHas(string $name): bool
	{
		$name = strtolower($name);

		$expl = explode('_',$name);

		if (count($expl) === 1)	{
			return isset($this->model[$name]);
		} else {
			if ($this->has($expl[0])) {
				$coll = $this->get($expl[0]);

				$pieces = [];
				for ($i = 1; $i < count($expl); $i++) {
					$pieces []= $expl[$i];
				}

				if($coll->isGroup()) {
					return $coll->has(implode ('_', $pieces));
				} else {
					return false;
				}
			}
			return false;
		}
	}

	/**
	 * @param string $name
	 * @param string $prefix
	 * @return string
	 * @throws Exception
	 */
	public function getParentModelPrefix(string $name, string $prefix=''): string
	{
		$expl = explode('_',$name);

		if (count($expl) === 1)	{
			if (isset($this->model[$name])) {
				return $prefix;
			} elseif (isset($this->relations[$name])) {
				return $this->relations[$name]->name().'_';
			} else {
				throw new Exception($name.' cant getParentModelPrefix');
			}
		} else {
			if ($this->has($expl[0])) {
				$coll = $this->get($expl[0]);

				$pieces = [];
				for ($i = 1; $i < count($expl); $i++) {
					$pieces []= $expl[$i];
				}

				if($coll->isGroup()) {
					return $prefix;
				} elseif ($coll->isModel()) {
					return $coll->getParentModelPrefix(
						implode ('_', $pieces),
						$prefix . $coll->getModelName() . '_'
					);
				} else {
					throw new Exception($name.' cant getParentModelPrefix');
				}
			}

			throw new Exception($name.' cant getParentModelPrefix');
		}
	}

	/**
	 * @param string $name
	 * @return $this
	 * @throws Exception
	 */
	public function getParentModel(string $name): Model
	{
		$name = strtolower($name);

		$expl = explode('_',$name);

		if (count($expl) === 1)	{
			if (isset($this->model[$name])) {
				return $this;
			} elseif (isset($this->relations[$name])) {
				return $this->get($name);
			} else {
				throw new Exception($name.' cant getParentModel');
			}
		} else {
			if ($this->has($expl[0])) {
				$coll = $this->get($expl[0]);

				$pieces = array();
				for ($i = 1; $i < count($expl); $i++) {
					$pieces []= $expl[$i];
				}

				if ($coll->isGroup()) {
					return $this;
				} elseif ($coll->isModel()) {
					return $coll->getParentModel(implode ('_', $pieces));
				} else {
					throw new Exception($name.' cant getParentModel');
				}
			} else {
				throw new Exception($name.' cant getParentModel');
			}
		}
	}


	/**
	 * @param string|Model $name
	 * @param Model|null $model
	 * @return $this
	 */
	public function relationsadd(string|Model $name, ?Model $model = null): Model
	{
		if($name instanceof Model) {
			$model = $name;
			$name = $model->getName();
		}

		$this->relations[$name] = $model;
		$this->relations[$name]->setName($name);
		$this->relations[$name]->setParentModel($this);

		return $this;
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	public function removeRelation(string $name): Model
	{
		if(array_key_exists($name, $this->relations)) {
			unset($this->relations[$name]);
		}

		return $this;
	}

	/**
	 * @param Model $fromModel
	 * @return mixed
	 * @throws Exception
	 */
	public function getSelectRelationN1(Model $fromModel)
	{
		$repo = $fromModel->getRepository();
		$select = $repo->getSelect();
		$select->andWhere(
			$repo->getAlias(
				$fromModel->getFromColumnName())
			. ' = '. $fromModel->get(
				$fromModel->getFromColumnName()
			)->getDibiModificator(),
			$this->get(
				$fromModel->getRelationToAnotherModelColumnName()
			)->getValue()
		);
		return $select;
	}


	public function getSelectRelationNN(Model $nnModel)
	{

		$fromModel = $nnModel->getFromModel();
		$toModel = $nnModel->getToModel();

		if ($this->getRawName() === $fromModel->getRawName())
		{
			$toRepo = $toModel->getRepository();
			$query = $toRepo->getAlias($nnModel->getRelationToAnotherModelColumnName())
				." in "
				. "("
				.   "SELECT [{$nnModel->getRelationToAnotherModelColumnName()}]"
				.   "  FROM [{$nnModel->getRepository()->getTableRaw()}] "
				.   " WHERE [{$nnModel->getRelationFromThisColumnName()}]="
				.    $nnModel->get(
					$nnModel->getRelationFromThisColumnName()
				)->getDibiModificater()
				. ")";

			$select = $toRepo->getSelect();
			$select->andWhere(
				$query,
				$this->get(
					$nnModel->getRelationFromThisColumnName()
				)->getValue()
			);

			return $select;
		} else {
			$fromRepo = $fromModel->getRepository();
			$query = $fromRepo->getAlias($nnModel->getRelationFromThisColumnName())
				." in "
				. "("
				.   "SELECT [{$nnModel->getRelationFromThisColumnName()}]"
				.   "  FROM [{$nnModel->getRepository()->getTableRaw()}] "
				.   " WHERE [{$nnModel->getRelationToAnotherModelColumnName()}]="
				.               $nnModel->get(
					$nnModel->getRelationToAnotherModelColumnName()
				)->getDibiModificator()
				. ")";
			$select = $fromRepo->getSelect();
			$select->andWhere(
				$query,
				$this->get(
					$nnModel->getRelationToAnotherModelColumnName()
				)->getValue()
			);

			return $select;
		}
	}

	/**
	 * @param Model $model11
	 * @return Model
	 */
	public function getModelRelation11(Model $model11): Model
	{
		$fromModel = $model11->getFromModel();
		if ($this->getRawName() === $fromModel->getRawName()) {
			$toModel = $model11->getToModel();
			$toRepo = $toModel->getRepository();
			$select = $toRepo->getSelect();
			$select->andWhere(
				$toRepo->getAlias($toModel->getPrimaryKey()->getColumn())
				." in {"
				. "SELECT [{$model11->getRelationToAnotherModelColumnName()}]"
				. "  FROM [{$model11->getRepository()->getTableRaw()}] "
				. " WHERE [{{$model11->getRelationFromThisColumnName()}}]="
				.            $fromModel->get(
					$model11->getRelationFromThisColumnName()
				)->getDibiModificator(),
				$fromModel->get(
					$model11->getRelationFromThisColumnName()
				)->getValue()
			);

			return $select->getSingle();
		} else {
			$toModel = $model11->getToModel();
			$fromRepo = $fromModel->getRepository();
			$select = $fromRepo->getSelect();
			$select->andWhere(
				$fromRepo->getAlias($fromModel->getPrimaryKey()->getColumn())
				." in {"
				. "SELECT [{$model11->getRelationFromThisColumnName()}]"
				. "  FROM [{$model11->getRepository()->getTableRaw()}] "
				. " WHERE [{{$model11->getRelationToAnotherModelColumnName()}}]="
				.              $toModel->get(
					$model11->getRelationToAnotherModelColumnName()
				)->getDibiModificator(),
				$fromModel->get(
					$model11->getRelationToAnotherModelColumnName()
				)->getValue()
			);

			return $select->getSingle();
		}
	}


	/**
	 * @param Repository $repo
	 * @param $local
	 * @return $this
	 */
	public function setRepository(Repository $repo): Model
	{
		$this->repository = $repo;
		return $this;
	}

	/**
	 * @param string $child
	 * @return Column|ColumnGroup|Model
	 * @throws Exception
	 */
	public function get(string $child): Column|ColumnGroup|Model
	{
		$child = strtolower($child);
		$test = explode('_',$child);

		$rek = [];
		for ($i = 1; $i < count($test); $i++) {
			$rek []= $test[$i];
		}

		if (isset($this->model[$test[0]]) && count($rek) ) {
			return $this->model[$test[0]]->get(implode('_',$rek));
		} elseif (isset($this->model[$test[0]]) && !count($rek) ) {
			return $this->model[$test[0]];
		}

		if (isset($this->relations[$test[0]]) && count($rek) ) {
			return $this->relations[$test[0]]->get(implode('_',$rek));
		}
		elseif (isset($this->relations[$test[0]]) && !count($rek) ) {
			return $this->relations[$test[0]];
		}

		throw new Exception(get_class($this)." cant get {$child}");
	}

	/**
	 * @param string $name
	 * @return bool
	 * @throws Exception
	 */
	public function hasColumnName(string $name): bool
	{
		foreach($this->getColumnsRaw() as $key => $c)
		{
			if ($c->getColumnName() === $name) {
				return true;
			}
		}

		if ($this->getPrimaryKey()->getColumnName() === $name) {
			return true;
		}

		return false;
	}

	/**
	 * @param string $name
	 * @return Column|ColumnGroup
	 * @throws Exception
	 */
	public function getColumnName(string $name): Column|ColumnGroup
	{
		foreach($this->getColumnsRaw() as $key => $c)
		{
			if ($c->getColumnName() === $name) {
				return $this->get($c->getColumn());
			}
		}

		if ($this->getPrimaryKey()->getColumnName() === $name) {
			return $this->get($this->getPrimaryKey()->getColumn());
		}

		throw new Exception(get_class($this)." cant getCollum {$name}");
	}

	/**
	 * @param string $child
	 * @param mixed $value
	 * @param string|null $from
	 * @return $this
	 * @throws Exception
	 */
	public function set(string $child, mixed $value, string $from = null): Model
	{
		$child = strtolower($child);

		$test = explode('_',$child);

		$rek = [];
		for ($i = 1; $i < count($test); $i++) {
			$rek []= $test[$i];
		}

		if(isset($this->model[$test[0]]) && count($rek)
			&& ! $this->model[$test[0]]->isColumn()
		) {
			$this->model[$test[0]]->set(implode('_', $rek), $value, $from);
		} elseif (isset($this->model[$test[0]]) && !count($rek)) {
			$this->model[$test[0]]->set($value,$from);
		} elseif (isset($this->relations[$test[0]]) && count($rek)) {
			$this->relations[$test[0]]->set(implode('_', $rek), $value, $from);
		} elseif (isset($this->relations[$test[0]]) && !count($rek)) {
			$this->relations[$test[0]]->set($value, $from);
		} else {
			throw new Exception(get_class($this)." cant set {$child}");
		}

		return $this;
	}

	/**
	 * @return Repository
	 */
	public function getRepository(): Repository
	{
		if($this->repository === null)
		{
			throw new Exception('Repository is NULL');
		}

		return $this->repository;
	}


	/**
	 * @param $fromThisColumnName
	 * @param $toParentColumnName
	 * @return $this
	 */
	public function setRelationN1($fromThisColumnName, $toParentColumnName): Model
	{
		$this->isRelationN1 = true;
		$this->relationFromThisColumnName       = $fromThisColumnName;
		$this->relationToAnotherModelColumnName = $toParentColumnName;

		return $this;
	}

	/**
	 * @param $fromThisColumnName
	 * @param $toAnotherModelColumnName
	 * @return $this
	 */
	public function setJoin($fromThisColumnName, $toAnotherModelColumnName): Model
	{
		$this->isJoin = true;

		$this->relationFromThisColumnName = $fromThisColumnName;
		$this->relationToAnotherModelColumnName = $toAnotherModelColumnName;

		return $this;
	}


	/**
	 * @param mixed $data
	 * @return $this
	 * @throws Exception
	 */
	public function fromDb(mixed $data): Model
	{
		foreach ($data as $key => $value) {
			if ($this->has($key)) {
				$this->set($key, $value, 'db');
			}
		}

		return $this;
	}

	/**
	 * @param mixed $data
	 * @return $this
	 * @throws Exception
	 */
	public function fromForm(mixed $data): Model
	{

		parent::fromForm($data);

		foreach ($this->model as $key => $child) {
			if ($child->isModel()) {
				$this->get($child->getName())->fromForm($data);
			}
		}

		//add relations

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isModel(): bool
	{
		return true;
	}

	/**
	 * @return bool
	 */
	public function isGroup(): bool
	{
		return false;
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

		$val->add(parent::validate($formAction, $data));
		$val->add($this->checkUniques($formAction, $data));

		return $val;
	}

	/**
	 * @param string $action
	 * @param mixed|null $data
	 * @return Validation
	 * @throws Exception
	 */
	public function checkUniques(string $action, mixed $data = null): Validation
	{
		$val = new Validation();

		if ($action === self::FORM_ACTION_NEW) {
			foreach($this->getColumnsRaw() as $key => $column) {
				if ($column->isUnique() && $column->getUniqueWith() !== null && is_array($column->getUniqueWith())) {
					$repo = $this->getRepository();
					$select = $repo->getSelect();
					$select->where( " and ".$repo->getAlias().'.'.$column->getColumnName().' = %s' ,$column->getValue());
					foreach ($column->getUniqueWith() as $key2 => $c ) {
						$select->where(
							" and " . $repo->getAlias() . '.' . $this->{"get{$c}"}()->getColumnName() . ' = %s',
							$this->{"get{$c}"}()->getValue()
						);
					}
					if ($select->getCount() > 0) {
						$val->addError('notunique', $column->getColumn());
					}
				} elseif ($column->isUnique() && $column->getUniqueWith() !== null && is_string($column->getUniqueWith())) {
					$repo = $this->getRepository();
					$select = $repo->getSelect();
					$select->where( " and ".$repo->getAlias().'.'.$column->getColumnName().' = %s', $column->getValue());
					$select->where(
						" and ".$repo->getAlias().'.'.$this->{"get{$column->getUniqueWith()}"}()->getColumnName() . ' = %s'
						,$this->{"get{$column->getUniqueWith()}"}()->getValue()
					);

					if ($select->getCount() > 0) {
						$val->addError('notunique', $collum->getColumn());
					}
				} elseif ($column->isUnique() && $collum->getUniqueWith() === null)	{
					$repo = $this->getRepository();
					$select = $repo->getSelect();
					$select->where( " and " . $repo->getAlias().'.'.$column->getColumnName() . ' = %s', $column->getValue());

					if ($select->getCount() > 0) {
						$val->addError('notunique', $column->getColumn(),);
					}
				}
			}
		} elseif ($action === self::FORM_ACTION_EDIT) {

			$old = $this->getRepository()->getByPk($this->getPrimaryKey()->getValue());

			foreach ($this->getColumnsRaw() as $key => $column) {
				if ($column->isUnique() && $column->getUniqueWith() !== null && is_array($column->getUniqueWith())) {
					$same = true;
					if ($old->get($column->getColumn())->getValue() == $column->getValue())	{
						foreach ($column->getUniqueWith() as $key2 => $c ) {
							if ($old->{"get{$c}"}()->getValue() !== $this->get($c)->getValue()) {
								$same = false;
							}
						}
					} else {
						$same = false;
					}

					if (!$same) {
						$repo = $this->getRepository();
						$select = $repo->getSelect();
						$select->where( " and ".$repo->getAlias().'.'.$column->getColumn().' = %s', $column->getValue());
						foreach ($column->getUniqueWith() as $key2 => $c ) {
							$select->where(
								" and " . $repo->getAlias() . '.' . $this->get($c)->getColumnName() . ' = %s',
								$this->get($c)->getValue()
							);
						}

						if ($select->getCount() > 0) {
							$val->addError('notunique', $column->getColumn());
						}
					}
				} elseif ($column->isUnique() && $column->getUniqueWith() !== null && is_string($column->getUniqueWith())) {
					if ($old->get($column->getCollum())->getValue() !== $column->getValue()
						|| $old->get($column->getColumnName())->getValue() !== $this->get($column->getUniqueWith())->getValue()
					) {
						$repo = $this->getRepository();
						$select = $repo->getSelect();
						$select->where( " and ".$repo->getAlias().'.'.$column->getCollumName().' = %s', $column->getValue());
						$select->where(
							" and ".$repo->getAlias().'.'.$this->get($column->getUniqueWith())->getColumnName().' = %s',
							$this->get($column->getUniqueWith())->getValue()
						);

						if ($select->getCount() > 0) {
							$val->addError('notunique', $column->getCollum());
						}
					}
				} elseif ($column->isUnique() && $column->getUniqueWith() === null)	{
					if ($old && $old->get($column->getColumn())->getValue() != $column->getValue())	{
						$repo = $this->getRepository();
						$select = $repo->getSelect();
						$select->where( " and ".$repo->getAlias().'.'.$column->getCollumName().' = %s', $column->getValue());

						if ($select->getCount() > 0) {
							$val->addError ('notunique', $collum->getColumn());
						}
					}
				}
			}
		}

		return $val;
	}



	/**
	 *
	 * @return int $newId
	 */
	public function insert(): int
	{
		$id = $this->getRepository()->insert(
				$this->getColumnsValues()
		);

		$this->set($this->getPrimaryKey()->getColumn(), $id);

		return $id;
	}

	/**
	 * @return $this
	 * @throws Exception
	 */
	public function update(): Model
	{
		$this->getRepository()->updateByPK(
			$this->getColumnsNamesForUpdate(),
			$this->getPrimaryKey()->getValue()
		);

		return $this;
	}

	/**
	 * @return $this
	 * @throws Exception
	 */
	public function delete(): Model
	{
		$this->getRepository()->deleteByPK(
			$this->getPrimaryKey()->getValue()
		);

		return $this;
	}


	/**
	 * @return Column
	 * @throws Exception
	 */
	public function getPrimaryKey(): Column
	{
		foreach ($this->model as $key => $child) {
			if ($child->isColumn() && $child->isPrimaryKey()) {
				return $child;
			}
		}

		throw new Exception(get_class($this).' not defined PK');
	}

	/**
	 * @param $json
	 * @return $this
	 * @throws Exception
	 * @throws \Nette\Utils\JsonException
	 */
	public function fromJson($json): Model
	{
		$o = Json::decode($json);

		if (!$o) {
			throw new Exception(get_class($this).' not valid json object');
		}

		foreach ($o as $c => $v) {
			if ($this->has($c)) {
				$this->set($c, $v);
			}
		}

		return $this;
	}

	/**
	 * @return string
	 * @throws \Nette\Utils\JsonException
	 */
	public function toJson(): string
	{
		return Json::encode($this->getColumnsValues());
	}

	/**
	 * @return bool
	 */
	public function isJoin(): bool
	{
		return $this->isJoin;
	}

	/**
	 * @param bool $isJoin
	 * @return Model
	 */
	public function setIsJoin(bool $isJoin): Model
	{
		$this->isJoin = $isJoin;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isRelation11(): bool
	{
		return $this->isRelation11;
	}

	/**
	 * @param bool $isRelation11
	 * @return Model
	 */
	public function setIsRelation11(bool $isRelation11): Model
	{
		$this->isRelation11 = $isRelation11;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isRelationN1(): bool
	{
		return $this->isRelationN1;
	}

	/**
	 * @param bool $isRelationN1
	 * @return Model
	 */
	public function setIsRelationN1(bool $isRelationN1): Model
	{
		$this->isRelationN1 = $isRelationN1;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isRelationNN(): bool
	{
		return $this->isRelationNN;
	}

	/**
	 * @param bool $isRelationNN
	 * @return Model
	 */
	public function setIsRelationNN(bool $isRelationNN): Model
	{
		$this->isRelationNN = $isRelationNN;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRelationFromThisColumnName(): string
	{
		return $this->relationFromThisColumnName;
	}

	/**
	 * @param string $relationFromThisColumnName
	 * @return Model
	 */
	public function setRelationFromThisColumnName(string $relationFromThisColumnName): Model
	{
		$this->relationFromThisColumnName = $relationFromThisColumnName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRelationToAnotherModelColumnName(): string
	{
		return $this->relationToAnotherModelColumnName;
	}

	/**
	 * @param string $relationToAnotherModelColumnName
	 * @return Model
	 */
	public function setRelationToAnotherModelColumnName(string $relationToAnotherModelColumnName): Model
	{
		$this->relationToAnotherModelColumnName = $relationToAnotherModelColumnName;
		return $this;
	}
}
