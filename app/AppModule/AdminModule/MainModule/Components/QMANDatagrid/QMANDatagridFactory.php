<?php declare(strict_types = 1);

namespace App\Libs\Components\QMANDatagrid;

use App\Libs\Components\QMANDatagrid\QMANDatagrid;
use App\Libs\Model\Service\QMANService;
use Nette\Http\Session;

final class QMANDatagridFactory
{

	public function __construct(
		private Session $session,
		private QMANService $QMANService
	)
	{
	}

	public function create(string $id, string $qmanPrefix, string $getData, array $definition): QMANDatagrid
	{
		$grid = new QMANDatagrid($id, $qmanPrefix, $getData, $definition, $this->session, $this->QMANService);
		return $grid;
	}

}