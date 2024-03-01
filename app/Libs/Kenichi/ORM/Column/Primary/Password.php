<?php

declare(strict_types=1);

namespace App\Libs\Kenichi\ORM\Column\Primary;

use App\Libs\Kenichi\ORM\Model;
use App\Libs\Kenichi\ORM\Validation;

class Password extends \App\Libs\Kenichi\ORM\Column\Primitive\Varchar
{
	protected ?string $val_again = null;
	protected ?string $raw = null;
	protected int $minLength = 6;

	public function getMinLength(): int
	{
		return $this->minLength;
	}

	/**
	 * @param int $number
	 * @return $this
	 */
	public function setMinLength(int $number): Password
	{
		$this->minLength = $number;
		return $this;
	}

	public function fromForm(mixed $data): Password
	{
		foreach ($data as $key => $value) {
			if ($this->getColumn() === $key) {
				$this->set(self::encode($value));
				$this->raw = $value;
			}

			if ($this->getColumn().'_again' === $key) {
				$this->val_again = self::encode($value);
			}
		}

		return $this;
	}


	public function validate(string $formAction = null, mixed $data = null, ?Model $model = null): Validation
	{
		$val = new Validation();

		$val->add(parent::validate($formAction, $data, $model));

		if( $val->isSucc() )
		{
			if ($this->raw !== null && strlen($this->raw) < $this->getMinLength()) {
				$val->addError('toshortpassw', $this->getColumn());
			} elseif ($this->val_again !== null && $this->getValue() !== $this->val_again ){
				$val->addError('passwdontmatch', $this->getColumn());
			}

		}

		return $val;
	}

	public static function encode($string)
	{
		return sha1($string);
	}

	public static function generatePassword($length = 8)
	{

		// start with a blank password
		$password = "";

		// define possible characters - any character in this string can be
		// picked for use in the password, so if you want to put vowels back in
		// or add special characters such as exclamation marks, this is where
		// you should do it
		$possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";

		// we refer to the length of $possible a few times, so let's grab it now
		$maxlength = strlen($possible);

		// check for length overflow and truncate if necessary
		if ($length > $maxlength) {
			$length = $maxlength;
		}

		// set up a counter for how many characters are in the password so far
		$i = 0;

		// add random characters to $password until $length is reached
		while ($i < $length) {

			// pick a random character from the possible ones
			$char = substr($possible, mt_rand(0, $maxlength-1), 1);

			// have we already used this character in $password?
			if (!strstr($password, $char)) {
				// no, so it's OK to add it onto the end of whatever we've already got...
				$password .= $char;
				// ... and increase the counter by one
				$i++;
			}

		}

		// done!
		return $password;

	}
}
