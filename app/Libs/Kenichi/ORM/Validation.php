<?php

declare(strict_types=1);

namespace App\Libs\Kenichi\ORM;

class Validation
{
	protected array $errors = [];

	/**
	 * @param string $type
	 * @param string $element
	 * @param array $variables
	 * @return $this
	 */
	public function addError(string $type, string $element, array $variables = []): Validation
	{
		$this->errors[]= ['el' => $element, 'mess' => $type, 'params' => $variables];
		return $this;
	}

	/**
	 * @param Validation $v
	 * @return $this
	 */
	public function add(Validation $v): Validation
	{
		$this->errors = array_merge($this->errors, $v->getErrors());
		return $this;
	}

	/**
	 * @return array
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}

	/**
	 * @return bool
	 */
	public function isSucc(): bool
	{
		return (bool) count($this->getErrors());
	}
}