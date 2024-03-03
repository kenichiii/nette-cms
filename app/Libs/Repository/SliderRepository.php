<?php

declare(strict_types=1);

namespace App\Libs\Repository;

use App\Libs\Kenichi\ORM\Repository;

class SliderRepository extends Repository
{
	public function moveUpAction(int $id): SliderRepository
	{
		$that = $this->getByPk($id);

		$mrank = $that->get('rank')->getValue();

		$downneib = $this->getSelect()
			->addLangCond($that->get('lang')->getValue())
			->andWhere('[section]=%s', $that->get('section')->getValue())
			->where('and '.$this->getAlias('rank').'<%i',$mrank)
			->orderBy('rank',' DESC ')
			//->limit(1)
			->fetchSingle();

		if ($downneib) {
			$that->set('rank',$downneib->get('rank')->getValue());
			$downneib->set('rank',$mrank);

			$that->update();
			$downneib->update();
		}

		return $this;
	}


	public function moveDownAction(int $id): SliderRepository
	{
		$that = $this->getByPk($id);

		$mrank = $that->get('rank')->getValue();

		$select = $this->getSelect();
		$upneib = $select->addLangCond($that->get('lang')->getValue())
			->andWhere('[section]=%s', $that->get('section')->getValue())
			->andWhere($this->getAlias('rank').' > %i', $mrank)
			->orderBy('rank','ASC')
			//->limit(1)
			->fetchSingle();

		if ($upneib) {
			$that->set('rank',$upneib->get('rank')->getValue());
			$upneib->set('rank',$mrank);

			$that->update();
			$upneib->update();
		}

		return $this;
	}
}