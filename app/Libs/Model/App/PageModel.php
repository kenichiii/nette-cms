<?php

declare(strict_types=1);

namespace App\Libs\Model\App;


use App\Libs\Kenichi\ORM\Column\Primary\Active;
use App\Libs\Kenichi\ORM\Column\Primary\Created;
use App\Libs\Kenichi\ORM\Column\Primary\Deleted;
use App\Libs\Kenichi\ORM\Column\Primary\Id;
use App\Libs\Kenichi\ORM\Column\Primary\Pointer;
use App\Libs\Kenichi\ORM\Column\Primary\Rank;
use App\Libs\Kenichi\ORM\Column\Primitive\Bit;
use App\Libs\Kenichi\ORM\Column\Primitive\Number;
use App\Libs\Kenichi\ORM\Column\Primitive\Text;
use App\Libs\Kenichi\ORM\Column\Primitive\Varchar;
use App\Libs\Kenichi\ORM\Model;

class PageModel extends Model
{
	public function initModel()
	{
		$this->modeladd(new Id());

		$parent = new Number();
		$parent->setDefault('0');

		$this->modeladd('parent', $parent);
		$this->modeladd('lang', new Varchar());

		$pointer = new Pointer();
		$pointer->setUniqueWith('lang')
			->setUnique(true);
		$this->modeladd($pointer);

		$this->modeladd('uri', new Varchar());

		$presenter = new Varchar();
		$presenter->setDefault('Textpage')
				->setNotnull(true);
		$this->modeladd('presenter', $presenter);

		$action = new Varchar();
		$action->setNotnull(true)
			->setDefault('default');

		$this->modeladd('action', $action);
		$this->modeladd('title', new Varchar());
		$this->modeladd('menuName', new Varchar());

		$layout = new Varchar();
		$layout->setDefault('layout');
		$this->modeladd('layout', $layout);

		$loggedUser = new Bit();
		$loggedUser->setDefault('0');
		$this->modeladd('loggedUser', $loggedUser);

		$content = new Text();
		$content->setSanitize(false);
		$this->modeladd('content', $content);

		$this->modeladd('description', new Text());

		$menu = new Bit();
		$menu->setDefault('0');
		$this->modeladd('menu', $menu);

		$this->modeladd(new Rank());
		$this->modeladd(new Active());
		$this->modeladd(new Deleted());
		$this->modeladd(new Created());
	}


	public function setRank()
	{
		$col = $this->get('rank');
		$parent = $this->get('parent');

		$this->set($col->getColumn(),
			(
				$this->getRepository()->getConn()->fetchSingle(
					"select max({$col->getColumnName()}) 
                                 from ".$this->getRepository()->getTableRaw()." 
                                where {$parent->getColumnName()}=".$parent->getDibiModificator()
								." group by ".$this->getPrimaryKey()->getColumnName(),
					$parent->getValue() )
				+ 1
			)
		);
		return $this;
	}
}