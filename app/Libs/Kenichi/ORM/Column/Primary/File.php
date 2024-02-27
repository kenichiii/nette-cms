<?php

declare(strict_types=1);

namespace App\Libs\Kenichi\ORM\Column\Primary;

use App\Libs\Kenichi\ORM\Column\Primitive\Varchar;

class File extends Varchar
{
	protected $dir = null;

	protected $allowedExt = array('jpg','png','gif','pdf','doc','docx','xls','txt','zip','csv','avi','mp3','mp4');


	public function getExt(): string
	{
		$h = explode('.',$this->getValue());
		$e = end($h);
		return strtolower($e);
	}

	public function isValidFile($filename): bool
	{
		$h = explode('.',$filename);
		$ext = end($h);
		return in_array(strtolower($ext), $this->getAllowedExt());
	}

	public function getAllowedExt(): array
	{
		return $this->allowedExt;
	}

	/**
	 * @param $array
	 * @return $this
	 */
	public function setAllowedExt($array): File
	{
		$this->allowedExt = $array;
		return $this;
	}

	/**
	 * @param $dir
	 * @return $this
	 */
	public function setDir($dir): File
	{
		$this->_dir = $dir;
		return $this;
	}

	public function getDir(): string
	{
		return $this->dir;
	}

	public function getFullPath(): string
	{
		return $this->getDir() . '/' . $this->getValue();
	}
}

