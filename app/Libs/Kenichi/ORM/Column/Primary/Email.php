<?php

declare(strict_types=1);

namespace App\Libs\Kenichi\ORM\Column\Primary;

use App\Libs\Kenichi\ORM\Model;
use App\Libs\Kenichi\ORM\Validation;

class Email extends \App\Libs\Kenichi\ORM\Column\Primitive\Varchar
{
	protected bool $unique = true;
	public function validate(string $formAction = null,mixed $data = null, ?Model $model = null): Validation
	{
		$val = new Validation();

		$val->add(parent::validate($formAction, $data));

		if ($val->isSucc()) {
			if ($this->getValue() !== '' && !filter_var($this->getValue(),FILTER_VALIDATE_EMAIL)) {
				$val->addError('notvalidemail', $this->getColumn());
			}
		}

		return $val;
	}
}