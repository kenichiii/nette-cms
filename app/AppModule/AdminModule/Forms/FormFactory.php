<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\Forms;

use App\Libs\Service\App\Translator;
use Nette;
use Nette\Application\UI\Form;


final class FormFactory
{
	use Nette\SmartObject;

	public function __construct(private Nette\Security\User $user, private Translator $translator)
	{
	}


	public function create(): Form
	{
		$form = new Form;
		if ($this->user->isLoggedIn()) {
			$form->addProtection();
		}
		$this->translator->setSection('admin');
		$form->setTranslator($this->translator);
		return $form;
	}
}
