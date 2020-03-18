<?php

namespace Challenge;

class Logs
{
	const TYPE_WARNING = 'warning';
	const TYPE_ERROR   = 'error';

	static $errors = [];

	public function __destruct()
	{
		# dump error log

		if (static::$errors)
		{
			static::output();
		}
	}

	public static function addWarning(string $message) { return static::addCustom(self::TYPE_WARNING, $message); }
	public static function addError  (string $message) { return static::addCustom(self::TYPE_ERROR,   $message); }

	protected static function addCustom(string $type, string $message): bool
	{
		static::$errors[] = $message;

		error_log("[$type]: $message");


		if ($type === self::TYPE_ERROR)
		{
			# fatal

			static::output();

			die;
		}


		# saves one code line, allows to do "return Logs::addError(...)"

		return false;
	}

	protected static function output()
	{
		$rest = new Rest();
		$rest->output
		([
			'errors' => static::getErrors()
		]);
	}

	public static function getErrors(): array
	{
		return static::$errors;
	}

	public static function hasErrors(): bool
	{
		return (bool) static::getErrors();
	}
}