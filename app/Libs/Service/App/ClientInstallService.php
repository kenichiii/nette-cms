<?php

declare(strict_types=1);

namespace App\Libs\Service\App;

interface ClientInstallService
{
	public function install(): string;
}