<?php

namespace Challenge;

/**
 * Returns pointer to the database instance
 *
 * @return Database
 */

function database()
{
	static $database;

	if ($database === null)
	{
		$database = new Database();
	}

	return $database;
}


/**
 * Function-helper, helps to avoid unnecessary issets
 *
 * @param mixed $src
 * @param mixed $what
 * @param mixed $default
 *
 * @return mixed
 */
function get($src, $what = null, $default = null)
{
	if (func_num_args() == 1)
	{
		# e.g. $value = get('get');

		$what = $src;

		$src = $_REQUEST;
	}


	# arrays

	if (is_array($src))
	{
		if (isset($src[$what]) or array_key_exists($what, $src))
		{
			return $src[$what];
		}

		return $default;
	}


	# objects

	if (isset($src->{$what}) or property_exists($src, $what))
	{
		return $src->{$what};
	}

	return $default;
}

/**
 * Helps to format debug output
 *
 * @param mixed $var
 * @param bool $var_dump
 */
function debug($var, $var_dump = false)
{
	if (defined('APP_DEBUG') and !APP_DEBUG)
	{
		return;
	}

	?><pre style="text-align: left!important;"><?php if ($var_dump) { var_dump($var); } else { print_r($var); } ?></pre><?php
}