<?php

declare(strict_types=1);

namespace App\Libs\Kenichi\ORM;

use Dibi;

abstract class Repository
{
	protected ?Model $model = null;
	protected ?string $modelClassName = null;

	protected ?string $table = null;
	protected ?string $alias = null;

	public function __construct(protected Dibi\Connection $database) {

	}

	/**
	 * @return Dibi\Connection
	 */
	public function getConn(): Dibi\Connection
	{
		return $this->database;
	}

	/**
	 * @return string
	 */
	public function getModelClassName(): string
	{
		if($this->modelClassName === null) {
			$this->modelClassName = preg_replace('/(Repository)/', 'Model',get_called_class());
		}

		return $this->modelClassName;
	}

	/**
	 * @return Model
	 */
	public function getModel(): Model
	{
		if ($this->model === null) {
			$class = $this->getModelClassName();
			$this->model = new $class();
			$this->model->setRepository($this);
		}
		return $this->model;
	}

	/**
	 * @return SelectQuery
	 */
	public function getSelect(): SelectQuery
	{
		return new SelectQuery($this);
	}

	/**
	 * @return void
	 */
	public function transactionCommit(): void
	{
		$this->getConn()->query("COMMIT");
		$this->getConn()->query("SET AUTOCOMMIT=1");
	}

	public function transactionStart(): void
	{
		$this->getConn()->query("SET AUTOCOMMIT=0");
		$this->getConn()->query("START TRANSACTION");
	}

	public function transactionRollback()
	{
		$this->getConn()->query("ROLLBACK");
		$this->getConn()->query("SET AUTOCOMMIT=1");
	}

	public function getAliasRaw()
	{
		if($this->alias === null) {
			$tbl = $this->getTablePure();
			$this->alias = $tbl[0].$tbl[2];
		}
		return $this->alias;
	}

	protected function getCollums($prefix='')
	{
		$collums = array();

		foreach($this->getModel()->model() as $key=>$child)
		{
			if( $child->isMixed() && $child->isInData() )
			{
				foreach( $this->getCollumsMixed($child,$prefix) as $m=>$coll)
				{
					$collums []= $coll;
				}
			}
			elseif( $child->isInnerSql() && $child->isInData() )
			{
				$collums []= "(".$child->getQuery($this).") AS [".$prefix.$child->getCollum()."]";
			}
			elseif( $child->isInData() ) //primitive
				$collums []= $this->getAlias ($child->getCollumName()).' AS ['.$prefix.$child->getCollum().']';
		}

		foreach($this->getModel()->relations() as $rkey=>$rchild)
		{

			if( $rchild->isJoin() && $rchild->isInData() )
			{
				foreach( $rchild->repository()->getCollums($prefix.$rkey.'_') as $m=>$coll )
				{
					$collums []= $coll;
				}
			}

		}

		return $collums;
	}

	protected function getCollumsMixed($mixed,$prefix)
	{
		$collums = array();

		foreach( $mixed->model() as $key => $child )
		{
			if( $child->isMixed() &&  $child->isInData() )
			{
				foreach( $this->getCollumsMixed($child,$prefix) as $m=>$coll)
				{
					$collums []= $coll;
				}
			}
			elseif( $child->isInnerSql() && $child->isInData() )
			{
				$collums []= "(".$child->getQuery($this).") AS [".$prefix.$child->getCollum()."]";
			}
			elseif( $child->isInData() ) //primitive
				$collums []= $this->getAlias ($child->getCollumName()).' AS ['.$prefix.$child->getCollum().']';
		}

		return $collums;
	}

	public function getTable(): string
	{
		$t = '['.$this->getTableRaw().'] AS '.$this->getAlias();

		foreach ($this->getModel()->getRelations() as $rkey => $rchild) {
			if ($rchild->isJoin()) {
				$t .= $this->getTableNestedJoins($rchild, $this);
			}
		}

		return $t;
	}

	protected function getTableNestedJoins(Model $rchild, Repository $parentRepo): string
	{

		$t = " LEFT JOIN [{$rchild->getRepository()->getTableRaw()}] as {$rchild->getRepository()->getAlias()} "
			. " ON {$rchild->getRepository()->getAlias($rchild->getFromColumnName())}={$parentGrid->getAlias($rchild->getToColumnName())}";

		foreach ($rchild->getRelations() as $rkey => $rrchild) {
			if ($rrchild->isJoin()) {
				$t .= $this->getTableNestedJoins($rrchild, $rchild->getRepository());
			}
		}

		return $t;
	}

	public function getTableRaw(): string
	{
		if ($this->table === null) {
			$class = explode('\\', get_called_class());
			$this->table = preg_replace('/(Repository)$/', '', end($class));
		}

		return $this->table;
	}

	public function insert(array $data): int
	{
			$this->getConn()->insert($this->getTableRaw(), $data)->execute();

			return $this->getConn()->getInsertId();
	}

	public function updateByPK($data, $pk)
	{
		$query = array('UPDATE ['.$this->getTableRaw().'] SET ', $data,
			'WHERE  ['.$this->getModel()->getPrimaryKey()->getColumnName().']='
			.$this->getModel()->getPrimaryKey()->getDibiModificator(), $pk)
		;

		return $this->getConn()->query($query);
	}

	public function deleteByPK($pk)
	{
		$query = array('DELETE FROM ['.$this->getTableRaw().'] WHERE ['.$this->getModel()->getPrimaryKey()->getCollumName().']
		='.$this->getModel()->getPrimaryKey()->getDibiModificator(), $pk);
		return $this->getConn()->query($query);
	}

	public function getAlias(string $column = null): string
	{
		if ($this->alias === null) {
			$this->alias = $this->getTableRaw()[0];
			$this->alias.= $this->getTableRaw()[2];
		}

		if ($column === null) {
			return '[' . $this->alias . ']';
		} else {
			return $this->getAlias() . '.[' . $column . ']';
		}
	}

	public function setAlias(string $a): Repository
	{
		$this->_alias = $a;
		return $this;
	}

	public function setModel(Model $m): Repository
	{
		$this->model = $m;
		$this->modelClassName = get_class($m);
		return $this;
	}

	public function createTable(bool $innoDB = true): string
	{
		$row = [];
		$indexes = [];

		foreach ($this->getModel()->getModel() as $key => $child) {
			if ($child->isGroup()) {
				foreach ($child->getColumnsRaw() as $key2 => $column) {
					if ($column->isInnerSql()) {
						continue;
					}

					$query = '['.$column->getColumnName().'] '.$column->getSqlName();
					if ($column->isColumn() && $column->isNotNull()) {
						$query .= ' NOT NULL ';
					}
					if ($column->isColumn() && $coumnn->getDefault() !== null ) {
						$query .= " DEFAULT '{$column->getDefault()}'";
					}
					if ($column->isColumn() && $collum->isPrimaryKey())	{
						$query .= " AUTO_INCREMENT ";
						$indexes []= 'PRIMARY KEY ('.$collum->getColumnName().')'."\n";
					}

					$row []= $query;

					if ($column->isColumn() && $column->isKey()) {
						$indexes []= 'INDEX ['.$column->getColumnName().'] (['.$column->getColumnName().'])'."\n";
					}
					if ($column->isColumn() && $column->isUnique()) {
						if ($column->getUniqueWith() === null) {
							$indexes [] = 'UNIQUE INDEX [' . $collum->getColumnName() . '] (['
								. $collum->getColumnName()
								. '])' . "\n";
						} elseif (is_string($column->getUniqueWith())) {
							$indexes [] = 'UNIQUE INDEX [' . $collum->getColumnName() . '_' . $column->getUniqueWith() . '] (['
								. $column->getColumnName() . '],[' . $column->getUniqueWith()
								. '])' . "\n";
						} elseif (is_array($column->getUniqueWith())) {
							$indexes [] = 'UNIQUE INDEX [' . $column->getColumnName() . '_' . $column->getUniqueWith() . '] (['
								. $column->getColumnName() . '],[' . implode('],[', $column->getUniqueWith())
								. '])' . "\n";
						}
					}
				}
			} elseif ($child->isColumn()) {
				if (!$child->isInnerSql()) {
					$column = $child;

					$query = '[' . $column->getColumnName() . '] ' . $column->getSqlName();
					if ($column->isNotNull()) {
						$query .= ' NOT NULL ';
					}
					if ($column->getDefault() !== null) {
						$query .= " DEFAULT '{$column->getDefault()}'";
					}
					if ($column->isPrimaryKey()) {
						$query .= " AUTO_INCREMENT ";
						$indexes []= 'PRIMARY KEY ('.$column->getColumnName().')'."\n";
					}

					$row []= $query;

					if ($column->isKey()) {
						$indexes []= 'INDEX [key-'.$column->getColumnName().'] (['
							.$column->getColumnName().'])'."\n";
					}
					if ($column->isUnique()) {
						if ($column->getUniqueWith() === null) {
							$indexes [] = 'UNIQUE INDEX [uni-' . $column->getColumnName() . '] (['
								. $column->getColumnName() . '])' . "\n";
						} elseif (is_array($column->getUniqueWith())) {
							$indexes [] = 'UNIQUE INDEX [uni-' . $column->getColumnName() . '_'
								. implode('_', $column->getUniqueWith()) . '] ([' . $column->getCollumName()
								. '],[' . implode('],[', $column->getUniqueWith())
								. '])' . "\n";
						}
					}

				}
			}


		}

		$query = 'CREATE TABLE ['.$this->getTableRaw().'] ( '."\n" .implode(",\n ",$row);
		if (count($indexes)) {
			$query .= ',' . implode(','."\n ", $indexes);
		}
		$query .= "\n".' ) ENGINE='.($innoDB ? 'InnoDB' : 'myISAM');

		return $query;
	}

	public function alterTable()
	{
		$query = '';

		$dbname = ServiceContainer::service($this->getConnectionName())->getConfig()['database'];
		$table = str_replace(
			':'.$this->getConnectionName().':',

			ServiceContainer::service(
				$this->getConnectionName()
			)->getDbPrefix(),

			$this->getTableRaw()
		);

		$model = $this->getModel();

		$dbindexes = $this->getConn()->fetchAll('SHOW INDEX FROM ['.$table.'] FROM ['.$dbname.']');
		$dbschema = $this->getConn()->fetchAll("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '{$dbname}' AND TABLE_NAME = '{$table}'");

		$modelCollums = $this->getAlterCollums();
		$foundedDbCollumsInModel = array();

		foreach($dbschema as $key=>$dbcollum)
		{

			if($model->hasCollumName($dbcollum['COLUMN_NAME']) )
			{
				$foundedDbCollumsInModel[]=$dbcollum['COLUMN_NAME'];

				if(strtolower($model->getCollumName($dbcollum['COLUMN_NAME'])->getSqlName())!=$dbcollum['DATA_TYPE'])
				{
					$query .= "\n\n".'alter table ['.$this->getTableRaw().'] modify ['
						. $model->getCollumName($dbcollum['COLUMN_NAME'])->getCollumName()."] ".$model->getCollumName($dbcollum['COLUMN_NAME'])->getSqlType();
					if( $model->getCollumName($dbcollum['COLUMN_NAME'])->isNotNull() ) $query .= ' NOT NULL ';
					if( $model->getCollumName($dbcollum['COLUMN_NAME'])->getDefault()!=null ) { $query .= " DEFAULT '{$model->getCollumName($dbcollum['COLUMN_NAME'])->getDefault()}'"; }

				}

				if($model->getCollumName($dbcollum['COLUMN_NAME'])->isPrimaryKey())
				{
					if($dbcollum['COLUMN_KEY']!='PRI')
					{
						if($dbcollum['COLUMN_KEY']=='MUL')
						{
							if($exstKeys = $this->getAlterCollumnKeysInfo($dbcollum['COLUMN_NAME'],$dbindexes))
							{
								foreach($exstKeys as $pkek=>$pkekey)
									$query .= "\n\n"."DROP INDEX [{$pkekey['Key_name']}] ON [{$this->getTableRaw()}];";
							}
						}

						foreach($dbschema as $pkkey=>$pkchild)
							if($pkchild['COLUMN_KEY']=='PRI'&&$dbcollum['COLUMN_NAME']!=$pkchild['COLUMN_NAME'])
							{
								$query .= "\n\n".'ALTER TABLE ['.$this->getTableRaw().'] MODIFY ['.$pkchild['COLUMN_NAME'].'] INT NOT NULL;';
								$query .= "\n".'ALTER TABLE ['.$this->getTableRaw().'] DROP PRIMARY KEY';
								//DROP INDEX `PRIMARY` ON t;
							}

						$query .= "\n\n".'ALTER TABLE ['.$this->getTableRaw().'] ADD PRIMARY KEY(['.$dbcollum['COLUMN_NAME']."]);";
					}
				}

				if($model->getCollumName($dbcollum['COLUMN_NAME'])->getDefault()!==null)
				{
					if($dbcollum['COLUMN_DEFAULT']!=$model->getCollumName($dbcollum['COLUMN_NAME'])->getDefault())
					{
						$query .= "\n\n ALTER TABLE [{$this->getTableRaw()}] MODIFY ["
							.$model->getCollumName($dbcollum['COLUMN_NAME'])->getCollumName()."] ".$model->getCollumName($dbcollum['COLUMN_NAME'])->getSqlType();
						if( $model->getCollumName($dbcollum['COLUMN_NAME'])->isNotNull() ) $query .= ' NOT NULL ';
						if( $model->getCollumName($dbcollum['COLUMN_NAME'])->getDefault()!=null ) { $query .= " DEFAULT '{$model->getCollumName($dbcollum['COLUMN_NAME'])->getDefault()}'"; }
						$query .= ";";
					}
				}
				else
				{
					if($dbcollum['COLUMN_DEFAULT']!='')
					{
						$query .= "\n\n ALTER TABLE [{$this->getTableRaw()}] MODIFY ["
							.$model->getCollumName($dbcollum['COLUMN_NAME'])->getCollumName()."] ".$model->getCollumName($dbcollum['COLUMN_NAME'])->getSqlType();
						if( $model->getCollumName($dbcollum['COLUMN_NAME'])->isNotNull() ) $query .= ' NOT NULL ';
						if( $model->getCollumName($dbcollum['COLUMN_NAME'])->getDefault()!=null ) { $query .= " DEFAULT NULL"; }
						$query .= ";";
					}
				}

				if($model->getCollumName($dbcollum['COLUMN_NAME'])->isNotNull())
				{
					if($dbcollum['IS_NULLABLE']=='YES')
					{
						$query .= "\n\n ALTER TABLE [{$this->getTableRaw()}] MODIFY ["
							.$model->getCollumName($dbcollum['COLUMN_NAME'])->getCollumName()."] ".$model->getCollumName($dbcollum['COLUMN_NAME'])->getSqlType();
						$query .= ' NOT NULL ';
						if( $model->getCollumName($dbcollum['COLUMN_NAME'])->getDefault()!=null ) { $query .= " DEFAULT '{$model->getCollumName($dbcollum['COLUMN_NAME'])->getDefault()}'"; }
						$query .= ";";
					}
				}
				else
				{
					if($dbcollum['IS_NULLABLE']=='NO'&&!$model->getCollumName($dbcollum['COLUMN_NAME'])->isPrimaryKey())
					{
						$query .= "\n\n ALTER TABLE [{$this->getTableRaw()}] MODIFY ["
							.$model->getCollumName($dbcollum['COLUMN_NAME'])->getCollumName()."] ".$model->getCollumName($dbcollum['COLUMN_NAME'])->getSqlType();
						$query .= ' NULL ';
						if( $model->getCollumName($dbcollum['COLUMN_NAME'])->getDefault()!=null ) { $query .= " DEFAULT '{$model->getCollumName($dbcollum['COLUMN_NAME'])->getDefault()}'"; }
						$query .= ";";
					}
				}

				if($model->getCollumName($dbcollum['COLUMN_NAME'])->isKey())
				{
					if($dbcollum['COLUMN_KEY']=='MUL')
					{
						if(!$exstKey = $this->getAlterCollumnKeyInfo($dbcollum['COLUMN_NAME'],$dbindexes,'Non_unique',1))
						{
							$query .= "\n\n ALTER TABLE [{$this->getTableRaw()}] ADD INDEX [key-{$model->getCollumName($dbcollum['COLUMN_NAME'])->getCollumName()}] ([{$model->getCollumName($dbcollum['COLUMN_NAME'])->getCollumName()}]);";
						}
					}
					elseif($dbcollum['COLUMN_KEY']!='UNI')
					{
						$query .= "\n\n ALTER TABLE [{$this->getTableRaw()}] ADD INDEX [key-{$model->getCollumName($dbcollum['COLUMN_NAME'])->getCollumName()}] ([{$model->getCollumName($dbcollum['COLUMN_NAME'])->getCollumName()}]);";
					}

				}
				else
				{
					if($dbcollum['COLUMN_KEY']=='MUL')
					{
						if($exstKey = $this->getAlterCollumnKeyInfo($dbcollum['COLUMN_NAME'],$dbindexes,'Non_unique',1))
						{
							$query .= "\n\n"."DROP INDEX [{$exstKey['Key_name']}] ON [{$this->getTableRaw()}];";
						}
					}
				}


				if($model->getCollumName($dbcollum['COLUMN_NAME'])->isUnique())
				{
					if($dbcollum['COLUMN_KEY']=='UNI'||$dbcollum['COLUMN_KEY']=='MUL')
					{

						$passed = true;

						if(!$exst = $this->getAlterCollumnKeyInfo($dbcollum['COLUMN_NAME'],$dbindexes,'Non_unique',0))
						{
							$passed = false;
						}
						else
						{
							if($model->getCollumName($dbcollum['COLUMN_NAME'])->getUniqueWith())
							{
								$unique_used = array();
								foreach ($model->getCollumName($dbcollum['COLUMN_NAME'])->getUniqueWith() as $ukey => $anotherUnique)
								{
									if($anotherExst = $this->getAlterCollumnKeyInfo($anotherUnique,$dbindexes,'Key_name',$exst['Key_name']))
									{

										$unique_used[]=$anotherExst['Column_name'];
									} else $passed = false;
								}

								$unique_used []= $dbcollum['COLUMN_NAME'];
								foreach ($dbindexes as $ikey => $i)
								{
									if($i['Key_name']==$exst['Key_name']&&!in_array($i['Column_name'], $unique_used))
									{
										$passed = false;
									}
								}

							}

							if(!$passed)
							{
								$query .= "\n\n"."DROP INDEX [{$exst['Key_name']}] ON [{$this->getTableRaw()}];";
							}
						}


						if(!$passed)
						{
							if($model->getCollumName($dbcollum['COLUMN_NAME'])->getUniqueWith()==null)
								$query .= "\n\n ALTER TABLE [{$this->getTableRaw()}] ADD UNIQUE INDEX [uni-{$model->getCollumName($dbcollum['COLUMN_NAME'])->getCollumName()}] ([{$model->getCollumName($dbcollum['COLUMN_NAME'])->getCollumName()}]);";
							elseif(is_array($model->getCollumName($dbcollum['COLUMN_NAME'])->getUniqueWith()))
								$query .= "\n\n ALTER TABLE [{$this->getTableRaw()}] ADD UNIQUE INDEX [uni-{$model->getCollumName($dbcollum['COLUMN_NAME'])->getCollumName()}] ([{$model->getCollumName($dbcollum['COLUMN_NAME'])->getCollumName()},".implode('],[',$model->getCollumName($dbcollum['COLUMN_NAME'])->getUniqueWith())."]);";
						}
					} //end if MUL
					else
					{
						if($model->getCollumName($dbcollum['COLUMN_NAME'])->getUniqueWith()==null)
							$query .= "\n\n ALTER TABLE [{$this->getTableRaw()}] ADD UNIQUE INDEX [uni-{$model->getCollumName($dbcollum['COLUMN_NAME'])->getCollumName()}] ([{$model->getCollumName($dbcollum['COLUMN_NAME'])->getCollumName()}]);";
						elseif(is_array($model->getCollumName($dbcollum['COLUMN_NAME'])->getUniqueWith()))
							$query .= "\n\n ALTER TABLE [{$this->getTableRaw()}] ADD UNIQUE INDEX [uni-{$model->getCollumName($dbcollum['COLUMN_NAME'])->getCollumName()}] ([{$model->getCollumName($dbcollum['COLUMN_NAME'])->getCollumName()},".implode('],[',$model->getCollumName($dbcollum['COLUMN_NAME'])->getUniqueWith())."]);";
					}

				}
				else
				{
					if( !$model->getCollumName($dbcollum['COLUMN_NAME'])->isPrimaryKey() &&  $exst = $this->getAlterCollumnKeyInfo($dbcollum['COLUMN_NAME'],$dbindexes,'Non_unique','0'))
					{
						if($exst['Seq_in_index']==1)
							$query .= "\n\n"."DROP INDEX [{$exst['Key_name']}] ON [{$this->getTableRaw()}];";
					}

				}


				if($model->getCollumName($dbcollum['COLUMN_NAME']) instanceof Collum\Primitive\Int
					||$model->getCollumName($dbcollum['COLUMN_NAME']) instanceof Collum\Primitive\Decimal
					||$model->getCollumName($dbcollum['COLUMN_NAME']) instanceof Collum\Primitive\Varchar)
				{
					if(strtolower($model->getCollumName($dbcollum['COLUMN_NAME'])->getSqlType())!=$dbcollum['COLUMN_TYPE'])
					{
						$query .= "\n\n ALTER TABLE [{$this->getTableRaw()}] MODIFY ["
							. $model->getCollumName($dbcollum['COLUMN_NAME'])->getCollumName()."] ".$model->getCollumName($dbcollum['COLUMN_NAME'])->getSqlType();
						if( $model->getCollumName($dbcollum['COLUMN_NAME'])->isNotNull() ) $query .= ' NOT NULL ';
						if( $model->getCollumName($dbcollum['COLUMN_NAME'])->getDefault()!=null ) { $query .= " DEFAULT '{$model->getCollumName($dbcollum['COLUMN_NAME'])->getDefault()}'"; }
						$query .= ";";
					}

				}





			} //end if in array get alter collums
			else
			{

				//drop keys
				if($exstKeys = $this->getAlterCollumnKeysInfo($dbcollum['COLUMN_NAME'],$dbindexes))
				{
					foreach($exstKeys as $ekey=>$ek)
						$query .= "\n\n"."DROP INDEX [{$ek['Key_name']}] ON [{$this->getTableRaw()}];";
				}

				//drop collum
				$query .= "\n\n".'ALTER TABLE ['.$this->getTableRaw().'] DROP COLUMN ['.$dbcollum['COLUMN_NAME']."];";
			}
		}

		foreach ($modelCollums as $key => $modelCollum)
		{
			if(!in_array($modelCollum, $foundedDbCollumsInModel) )
			{
				//add collum
				$query .= "\n\n ALTER TABLE [{$this->getTableRaw()}] ADD "
					. $model->get($modelCollum)->getCollumName()." ".$model->get($modelCollum)->getSqlType();
				if( $model->get($modelCollum)->isNotNull() ) $query .= ' NOT NULL ';
				if( $model->get($modelCollum)->getDefault()!=null ) { $query .= " DEFAULT '{$model->get($modelCollum)->getDefault()}'"; }
				$query .= ";";

				if($model->get($modelCollum)->isKey())
				{
					$query .= "\n\n ALTER TABLE [{$this->getTableRaw()}] ADD INDEX [key-{$model->get($modelCollum)->getCollumName()}] ([{$model->get($modelCollum)->getCollumName()}]);";
				}

				if($model->get($modelCollum)->isUnique())
				{
					if($model->get($modelCollum)->getUniqueWith()==null)
						$query .= "\n\n ALTER TABLE [{$this->getTableRaw()}] ADD UNIQUE INDEX [uni-{$model->get($modelCollum)->getCollumName()}] ([{$model->get($modelCollum)->getCollumName()}]);";
					elseif(is_array($model->get($modelCollum)->getUniqueWith()))
						$query .= "\n\n ALTER TABLE [{$this->getTableRaw()}] ADD UNIQUE INDEX [uni-{$model->get($modelCollum)->getCollumName()}] ([{$model->get($modelCollum)->getCollumName()},".implode('],[',$model->get($modelCollum)->getUniqueWith())."]);";
				}

			}
		}

		$query .= "\n\n".'# END COMPARING'.";";

		return $query;
	}

	public function getAlterCollumnKeysInfo($collum,$indexes)
	{
		$ret = array();
		foreach($indexes as $key=>$value)
		{
			if($value['Column_name']==$collum) $ret []= $value;
		}
		return count($ret>0)?$ret:null;
	}

	public function getAlterCollumnKeyInfo($collum,$indexes,$key=null,$keyValue=null)
	{
		foreach($indexes as $ikey=>$value)
		{
			if($key )
			{ if($value['Column_name']==$collum && $value[$key]==$keyValue) return $value;}
			else
			{ if($value['Column_name']==$collum) return $value; }
		}
		return null;
	}

	public function getAlterCollums()
	{
		$collums = array();

		foreach($this->getModel()->model() as $key=>$child)
		{
			if( $child->isMixed() )
			{
				foreach( $this->getAlterCollumsMixed($child) as $m=>$coll)
				{
					$collums []= $coll;
				}
			}
			elseif( $child->isInnerSql() )
			{

			}
			elseif( $child->isPrimitive() ) //primitive
				$collums []= $child->getCollumName();
		}

		return $collums;
	}

	public function getAlterCollumsMixed($mixed)
	{
		$collums = array();
		foreach( $mixed->model() as $key => $child )
		{
			if( $child->isMixed() )
			{
				foreach( $this->getAlterCollumsMixed() as $m=>$coll)
				{
					$collums []= $coll;
				}
			}
			elseif( $child->isPrimitive() ) //primitive
				$collums []= $child->getCollumName();
		}

		return $collums;
	}
}


