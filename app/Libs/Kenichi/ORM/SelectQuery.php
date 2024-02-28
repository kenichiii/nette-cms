<?php

declare(strict_types=1);

namespace App\Libs\Kenichi\ORM;

class SelectQuery
{
	protected string $columns = '*';

	protected string $from;
	protected string $joins;

	protected string $whereStart = 'WHERE 1=1';
	protected array $where   = [];

	protected ?string $groupBy = null;

	protected string $havingStart = 'HAVING 1=1';
	protected array $having  = [];

	protected ?string $orderBy = null;

	protected ?array $limit   = null;

	public function __construct(protected Repository $repository)
	{

	}

	/**
	 * @return Repository
	 */
	public function getRepository(): Repository
	{
		return $this->repository;
	}

	public function getColumns(): string
	{
		return $this->columns;
	}

	/**
	 * @return Model
	 */
	public function getModel(): Model
	{
		return $this->getRepository()->getModel();
	}

	/**
	 * @return Model[]
	 * @throws Exception
	 * @throws \Dibi\Exception
	 */
	public function fetchData(): array
	{
		$query []= 'SELECT '
			. implode(',',$this->getDataColumns())
			. ' FROM '.$this->getRepository()->getTable()
			. ' ' . $this->whereStart
		;

		foreach ($this->where as $key => $w) {
			if (count($w) === 1) {
				array_push($query, $w[0]);
			} elseif (count($w) === 2) {
				array_push($query, $w[0], $w[1]);
			}
		}

		if ($this->groupBy) {
			array_push($query, $this->groupBy);
		}

		if (count($this->having)) {
			array_push($query, $this->havingStart);
			foreach ($this->having as $key => $w) {
				if (count($w) === 1) {
					array_push($query, $w[0]);
				} else {
					array_push($query, $w[0], $w[1]);
				}
			}
		}

		if ($this->orderBy) {
			array_push($query, $this->orderBy);
		}

		if (is_array($this->limit)) {
			if(count($this->limit) === 2) {//only limit
				array_push($query, $this->limit[0], $this->limit[1]);
			} else {//limit with offset
				array_push($query, $this->limit[0], $this->limit[1], $this->limit[2]);
			}
		}

		$data = [];

		foreach ($this->getRepository()->getConn()->fetchAll($query) as $key => $value) {
			$bean = $this->getRepository()->getModelClassName();
			$model = new $bean();
			$model->setRepository($this->getRepository());
			$data [$key]=  $model;
			$data [$key]->fromDb($value);
		}

		return $data;
	}

	/**
	 * @return Model|null
	 * @throws Exception
	 * @throws \Dibi\Exception
	 */
	public function fetchSingle(): ?Model
	{
		$query []= 'SELECT '
			. $this->getColumns()
			. ' FROM '. $this->getRepository()->getTable()
			. ' ' . $this->whereStart
		;

		foreach ($this->where as $key => $w) {
			if (count($w) === 1) {
				array_push($query, $w[0]);
			} else {
				array_push($query, $w[0], $w[1]);
			}
		}

		if($this->groupBy) {
			array_push($query, $this->groupBy);
		}

		if (count($this->having)) {
			array_push($query, $this->havingStart);
			foreach ($this->having as $key => $w) {
				if (count($w) === 1) {
					array_push($query, $w[0]);
				} else {
					array_push($query, $w[0], $w[1]);
				}
			}
		}

		if ($this->orderBy) {
			array_push($query,$this->orderBy);
		}

		if ($this->limit) {
			array_push($query,$this->limit);
		}

		$data = $this->getRepository()->getConn()->fetch($query);

		if ($data) {
			$bean = $this->getRepository()->getModelClassName();
			$model = new $bean();
			$model->setRepository($this->getRepository());
			$model->fromdb($data);

			return $model;
		}

		return null;
	}

	/**
	 * @param int $pk
	 * @return Model|null
	 * @throws Exception
	 * @throws \Dibi\Exception
	 */
	public function fetchByPk(int $pk): ?Model
	{
		$query []= 'SELECT '
			. (is_array($this->getColumns()) ? implode(',', $this->getColumns()) : $this->getColumns())
			. ' FROM ' . $this->getRepository()->getTable() .' ' . $this->whereStart
		;

		array_push(
			$query,
			' and ' . $this->getRepository()->getAlias($this->getRepository()->getModel()->getPrimaryKey()->getColumnName())
			. '=' . $this->getRepository()->getModel()->getPrimaryKey()->getDibiModificator(),
			$pk
		);

		$data = $this->getRepository()->getConn()->fetch($query);

		if ($data) {
			$bean = $this->getRepository()->getModelClassName();
			$model = new $bean();
			$model->fromdb($data);
			$model->setRepository($this->getRepository());
			return $model;
		}

		return null;
	}

	/**
	 * @param $selector
	 * @return int
	 * @throws Exception
	 */
	public function getCount(string $selector = '*'): int
	{
		//can be used in having
		$query []= 'SELECT count('.$selector.') FROM ' . $this->getRepository()->getTable()
			. ' ' . $this->whereStart;

		foreach ($this->where as $key => $w) {
			if (count($w) === 1) {
				array_push($query, $w[0]);
			} else {
				array_push($query, $w[0], $w[1]);
			}
		}

		if ($this->groupBy) {
			array_push($query, $this->groupBy);
		} else {
			array_push(
				$query,
				"GROUP BY " . $this->getAlias($this->getModel()->getPrimaryKey()->getColumnName())
			);
		}


		if (count($this->having)) {
			array_push($query, $this->havingStart);
			foreach ($this->having as $key => $w) {
				if (count($w) == 1) {
					array_push($query, $w[0]);
				} else {
					array_push($query, $w[0], $w[1]);
				}
			}
		}

		return (int) $this->getRepository()->getConn()->fetchSingle($query);
	}

	/**
	 * @return $this
	 */
	public function clearWhere(): SelectQuery
	{
		$this->where = [];
		return $this;
	}

	/**
	 * @param string $where
	 * @param mixed|null $values
	 * @return $this
	 * @throws Exception
	 */
	public function andWhere(string $where, mixed $values = null): SelectQuery
	{
		if ($this->getModel()->hasColumnName($where)) {
			return $this->where(' and ' . $this->getRepository()->getAlias() . '.[' . $where . '] = '
				. $this->getModel()->getColumnName($where)->getDibiModificator(),
				$values
			);
		} elseif ($this->getModel()->modelHas($where)) {
			return $this->where(' and ' . $this->getRepository()->getAlias() . '. ['
				. $this->getModel()->get($where)->getColumnName() . '] = '
				. $this->getModel()->get($where)->getDibiModificator(),
				$values
			);
		} else {
			return $this->where(' and ' . $where, $values);
		}
	}

	/**
	 * @param string $where
	 * @param mixed|null $values
	 * @return $this
	 * @throws Exception
	 */
	public function orWhere(string $where, mixed $values = null): SelectQuery
	{
		if(in_array( $where, array_keys($this->getModel()->getCollumsNamesInArray())))
			return $this->where(' or '.$this->getAlias().'.['.$where.']='.$this->getModel()->getCollumName($where)->getDibiMod(),$values);
		elseif($this->getModel()->modelHas($where))
			return $this->where(' or '.$this->getAlias().'.['.$this->getModel()->get($where)->getCollumName().']='.$this->getModel()->get($where)->getDibiMod(),$values);
		else
			return $this->where(' or '.$where,$values);
	}

	/**
	 * @param string|array $where
	 * @param mixed|null $values
	 * @return $this
	 */
	public function where(string|array $where, mixed $values = null): SelectQuery
	{
		if ($values === null) {
			if( is_array($where)) {
				foreach ($where as $key => $cond) {
					if (count($cond) === 1) {
						$this->where [] = array(' ' . $cond[0] . ' ');
					} else {
						$this->where [] = array(' ' . $cond[0] . ' ', $cond[1]);
					}
				}
			} else {
				$this->where [] = array(' ' . $where . ' ');
			}
		} else {
			$this->where [] = array(' ' . $where . ' ', $values);
		}

		return $this;
	}

	/**
	 * @return $this
	 */
	public function clearHaving(): SelectQuery
	{
		$this->having = [];
		return $this;
	}

	/**
	 * @param string $where
	 * @param mixed|null $values
	 * @return $this
	 */
	public function andHaving(string $where, mixed $values = null): SelectQuery
	{
		return $this->having(' and ' . $where, $values);
	}

	/**
	 * @param string $where
	 * @param mixed|null $values
	 * @return $this
	 */
	public function orHaving(string $where, mixed $values = null): SelectQuery
	{
		return $this->having(' or ' . $where, $values);
	}

	/**
	 * @param string $where
	 * @param mixed|null $values
	 * @return $this
	 */
	public function having(string $where, mixed $values = null): SelectQuery
	{
		if ($values === null) {
			$this->having []= [$where];
		}
		else {
			$this->having [] = [$where, $values];
		}

		return $this;
	}

	/**
	 * @return $this
	 */
	public function clearOrderBy(): SelectQuery
	{
		$this->orderBy = null;
		return $this;
	}

	/**
	 * @param string $orderby
	 * @return $this
	 */
	public function orderBy(string $orderby): SelectQuery
	{
		$this->orderBy = ' ORDER BY '.$this->orderBy.' '.$orderby;
		return $this;
	}

	/**
	 * @return $this
	 */
	public function clearGroupBy(): SelectQuery
	{
		$this->groupBy = null;
		return $this;
	}

	/**
	 * @param string $column
	 * @return $this
	 */
	public function groupBy(string $column): SelectQuery
	{
		$this->groupBy = ' GROUP BY '.$this->getRepository()->getALias($column);
		return $this;
	}

	/**
	 * @return $this
	 */
	public function clearLimit(): SelectQuery
	{
		$this->limit = null;
		return $this;
	}

	/**
	 * @param int|null $limit
	 * @param int|null $offset
	 * @return $this
	 */
	public function limit(?int $limit, ?int $offset=null): SelectQuery
	{

		if ($limit !== null && $offset !== null) {
			$this->limit = ['%lmt %ofs', $limit, $offset];
		} elseif ($limit !== null) {
			$this->limit = ['%lmt', $limit];
		} elseif ($offset !== null) {
			$this->limit = ['%ofs', $offset];
		}

		return $this;
	}

	/**
	 * @return $this
	 */
	public function clear(): SelectQuery
	{
		$this->clearWhere()->clearHaving()->clearGroupBy()->clearOrderBy()->clearLimit();
		return $this;
	}


	/**
	 * @return $this
	 */
	public function addDeletedCond(): SelectQuery
	{
/*
		foreach($this->getModel()->getRels() as $key=>$child)
		{
			if( $child->isJoin() )
			{
				if($collum = $child->isDeletedAble())
					$this->where(' and '.$child->getQuery()->getAlias($collum->getCollum()).'=0 ');

				$this->setDeletedCondJoin($child);
			}

		}
*/
		$this->where(' and ' . $this->getRepository()->getAlias('deleted') . ' = 0 ');
		return $this;
	}

	public function addDeletedCondJoin($joined)
	{
		foreach($joined->getRels() as $key=>$child)
		{
			if( $child->isJoin() )
			{
				if($collum = $child->isDeletedAble())
					$this->where(' and '.$child->getQuery()->getAlias($collum->getCollum()).'=0 ');

				$this->setDeletedCondJoin($child);
			}

		}

	}

	public function addActiveCond(): SelectQuery
	{
/*
		foreach($this->getModel()->getRels() as $key=>$child)
		{
			if( $child->isJoin() )
			{
				if($collum = $child->isActiveAble())
					$this->where(' and '.$child->getQuery()->getAlias($collum->getCollumName()).'=1 ');

				$this->setActiveCondJoin($child);
			}

		}
*/
		$this->andWhere($this->getRepository()->getAlias('active') . ' = 1');
		return $this;
	}

	public function addActiveCondJoin($joined)
	{
		foreach($joined->getRels() as $key=>$child)
		{
			if( $child->isJoin() )
			{
				if($collum = $child->isActiveAble())
					$this->where(' and '.$child->getQuery()->getAlias($collum->getCollumName()).'=1 ');

				$this->setDeletedCondJoin($child);
			}

		}

	}

	/**
	 * @param int $parent
	 * @return $this
	 */
	public function addParentCond(int $parent): SelectQuery
	{
		$this->where( ' and '.$this->getRepository()->getAlias('parent') . ' = %i', $parent);
		return $this;
	}

	/**
	 * @param int $userid
	 * @return $this
	 */
	public function addUserIdCond(int $userid): SelectQuery
	{
		$this->where( ' and ' . $this->getRepository()->getAlias('user') . ' = %i' , $userid);
		return $this;
	}


	/**
	 * @param string $lang
	 * @return $this
	 */
	public function addLangCond(string $lang): SelectQuery
	{
/*
		foreach ($this->getModel()->getModel() as $key => $child) {
			if ($child->isModel()) {
				if ($child->isJoin()) {
					$this->andWhere(
						$child->getQuery()->getRepository()->getAlias('lang')
						. " = "
						. $colum->getDibiMod(),$lang);
				}
			}
		}
*/
		$this->andWhere(
			$this->getRepository()->getAlias('lang')
			. " = "
			. $this->getModel()->get('lang')->getDibiModificator(),
			$lang
		);

		return $this;
	}

	/**
	 * @param string|null $direction
	 * @return $this
	 */
	public function addRankOrderByCond(?string $direction = null): SelectQuery
	{
		$this->orderBy(
			$this->getRepository()->getAlias('rank')
			. " "
			. ($direction ?: $this->getModel()->get('rank')->getSorting())
		);
		return $this;
	}

	public function getDataColumns(): array
	{
		$columns = [];
		foreach ($this->getModel()->getColumnsRaw() as $column) {
			if ($column->isInData()) {
				$columns []= $column->getColumnName();
			}
		}

		return $columns;
	}
}