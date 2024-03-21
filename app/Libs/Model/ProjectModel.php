<?php

declare(strict_types=1);

namespace App\Libs\Model;

use App\Libs\Kenichi\ORM\Column\Primary\Active;
use App\Libs\Kenichi\ORM\Column\Primary\Deleted;
use App\Libs\Kenichi\ORM\Column\Primary\Id;
use App\Libs\Kenichi\ORM\Column\Primary\Lang;
use App\Libs\Kenichi\ORM\Column\Primary\Photo;
use App\Libs\Kenichi\ORM\Column\Primary\Rank;
use App\Libs\Kenichi\ORM\Column\Primary\Section;
use App\Libs\Kenichi\ORM\Column\Primary\Uri;
use App\Libs\Kenichi\ORM\Column\Primitive\Number;
use App\Libs\Kenichi\ORM\Column\Primitive\Text;
use App\Libs\Kenichi\ORM\Column\Primitive\Varchar;
use App\Libs\Kenichi\ORM\Model;

class ProjectModel extends Model
{
	const SECTION = ['web' => 'web','apps' => 'apps','design' => 'design'];

	public function initModel()
	{
		$this->modeladd(new Id());
		$this->modeladd(new Lang());
		$this->modeladd(new Section());
		$this->modeladd('title', new Varchar());
		$this->modeladd( new Uri());
		$this->get('uri')->setUniqueWith('lang');
		$this->modeladd('description', new Varchar());
		$this->modeladd(new Photo());
		$this->modeladd(new Content());
		$this->modeladd('testimonial', new Number());
		$this->modeladd(new Rank());
		$this->modeladd(new Active());
		$this->modeladd(new Deleted());
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