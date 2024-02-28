<?php declare(strict_types = 1);

namespace App\Libs\Components\VisualPaginator;

use Nette\Http\Session;
use Nette\Localization\Translator;

final class VisualPaginatorFactory
{

	public function __construct(private Session $session)
	{
	}

	public function create(): VisualPaginator
	{
		$paginator = new VisualPaginator($this->session);
		return $paginator;
	}

}