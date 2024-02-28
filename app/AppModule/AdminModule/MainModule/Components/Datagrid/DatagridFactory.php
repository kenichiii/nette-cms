<?php declare(strict_types = 1);

namespace App\AppModule\AdminModule\MainModule\Components\Datagrid;

use App\Libs\Kenichi\ORM\Repository;
use App\Libs\Service\App\Translator;
use Nette\Http\Session;

final class DatagridFactory
{

	public function __construct(
		private Session $session,
		private Translator $translator,
	)
	{
		$this->translator->setSection('admin');
	}

	public function create(Repository $repository, array $definition): Datagrid
	{
		$grid = new Datagrid($definition,$repository, $this->session, $this->translator);
		return $grid;
	}

}