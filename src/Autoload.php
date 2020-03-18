<?php

namespace Challenge;

require_once 'Globals.php';

$_autoload = Autoload::getInstance();

class Autoload
{
	public static function getInstance()
	{
		static $autoload;

		if ($autoload === null)
		{
			$autoload = new Autoload();
			$autoload->init();
		}

		return $autoload;
	}

	public function init()
	{
		define('APP_NAMESPACE', __NAMESPACE__);


		# locate root dirs

		$backtrace = debug_backtrace();

		if (!isset($backtrace[1]))
		{
			die('unable to find the caller');
		}


		define('APP_DIR_ROOT', dirname($backtrace[1]['file']));
		define('APP_DIR_APP',  dirname($backtrace[2]['file']));

		define('APP_DIR_ROOT_SRC', APP_DIR_ROOT . DIRECTORY_SEPARATOR . 'src');
		define('APP_DIR_APP_SRC',  APP_DIR_APP  . DIRECTORY_SEPARATOR . 'src');


		if (!defined('APP_TIMEZONE'))
		{
			define('APP_TIMEZONE', 'UTC');
		}

		date_default_timezone_set(APP_TIMEZONE);



		if (!defined('APP_DEBUG'))
		{
			define('APP_DEBUG', false);
		}

		if (APP_DEBUG)
		{
			error_reporting(-1); # show all errors/warnings/stricts
		}


		if (!defined('APP_STRING_ENCODING'))
		{
			define('APP_STRING_ENCODING', 'UTF-8');
		}

		mb_internal_encoding(APP_STRING_ENCODING);


		# register autoload

		spl_autoload_register(__NAMESPACE__ . '\Autoload::autoload');
	}

	public static function autoload(string $class_name)
	{
		$class_name_parts = explode('\\', $class_name);

		$namespace = get($class_name_parts, 0);

		if ($namespace !== APP_NAMESPACE)
		{
			# not our business

			return;
		}


		     if (get($class_name_parts, 1) == 'App') { $directory = APP_DIR_APP_SRC;  $file_name = get($class_name_parts, 2); }
		else                                         { $directory = APP_DIR_ROOT_SRC; $file_name = get($class_name_parts, 1); }

		static::requireFile($directory . DIRECTORY_SEPARATOR . $file_name . ".php");
	}

	protected static function requireFile(string $path): bool
	{
		if (!$path)
		{
			return false;
		}


		$path = realpath($path);

		if (is_file($path) and file_exists($path) and pathinfo($path, PATHINFO_EXTENSION) === 'php')
		{
			require_once $path;

			return true;
		}

		return false;
	}

	protected static function escapeFile(string $path): string
	{
		return preg_replace('/[^a-z0-9]\//i', '', $path); # remove all but alpha+numeric+slash
	}
}