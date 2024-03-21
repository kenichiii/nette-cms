<?php

declare(strict_types=1);

namespace App\Libs\Model;

use App\Libs\Kenichi\ORM\Column\Primary\Active;
use App\Libs\Kenichi\ORM\Column\Primary\Deleted;
use App\Libs\Kenichi\ORM\Column\Primary\Id;
use App\Libs\Kenichi\ORM\Column\Primary\Lang;
use App\Libs\Kenichi\ORM\Column\Primary\Photo;
use App\Libs\Kenichi\ORM\Column\Primary\Rank;
use App\Libs\Kenichi\ORM\Column\Primitive\Text;
use App\Libs\Kenichi\ORM\Column\Primitive\Varchar;
use App\Libs\Kenichi\ORM\Model;

class TestimonialModel extends Model
{
	public function initModel()
	{
		$this->modeladd(new Id());
		$this->modeladd(new Lang());
		$this->modeladd('name', new Varchar());
		$this->modeladd('position', new Varchar());
		$this->modeladd(new Photo());
		$this->modeladd('content', new Text());
		$this->modeladd(new Active());
		$this->modeladd(new Deleted());
		$this->modeladd(new Rank());
	}

	public function setRank()
	{
		$col = $this->get('rank');
		$lang = $this->get('lang');

		$this->set($col->getColumn(),
			(
				$this->getRepository()->getConn()->fetchSingle(
					"select max([{$col->getColumnName()}]) 
                                 from [".$this->getRepository()->getTableRaw()."] 
                                where  [{$lang->getColumnName()}]=".$lang->getDibiModificator()
					." group by [".$this->getPrimaryKey()->getColumnName().']',
					$lang->getValue())
				+ 1
			)
		);
		return $this;
	}
}