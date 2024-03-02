<?php

declare(strict_types=1);

if (isset($_GET['cleanCache'])) {
	function removeDir(string $dir): void {
		if (is_dir($dir)) {
			$it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
			$files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
			foreach ($files as $file) {
				if ($file->isDir()) {
					rmdir($file->getPathname());
				} else {
					unlink($file->getPathname());
				}
			}
			rmdir($dir);
		}
	}
	removeDir('../temp/cache');
}

require __DIR__ . '/../vendor/autoload.php';

$configurator = App\Bootstrap::boot();
$container = $configurator->createContainer();
$application = $container->getByType(Nette\Application\Application::class);
$application->run();
