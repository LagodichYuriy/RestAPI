<?php

namespace Challenge;

class URL
{
	protected $parts = [];

	public function __construct($url = null)
	{
		$this->parts = static::parse($url);
	}

	public static function parse(string $url = null, int $component = -1) :? array
	{
		if ($url === null)
		{
			$url = static::current();
		}


		$result = parse_url($url, $component);

		if ($result === false)
		{
			return null;
		}

		return $result;
	}

	public static function current(bool $skip_protocol = false, bool $skip_domain = false) : string
	{
		if ($skip_domain !== false)
		{
			return $_SERVER['REQUEST_URI'];
		}


		$url = '';

		if ($skip_protocol === false)
		{
			$protocol = 'http';

			if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on')
			{
				$protocol .= 's';
			}

			$url = $protocol . '://';
		}

		if (isset($_SERVER['SERVER_PORT']) and $_SERVER['SERVER_PORT'] != '80')
		{
			$url .= $_SERVER['HTTP_HOST'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
		}
		else
		{
			$url .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		}

		return $url;
	}


	public function getScheme  () : string { return static::getPart('scheme');   } # https
	public function getUser    () : string { return static::getPart('user');     } #         login
	public function getPass    () : string { return static::getPart('pass');     } #               password
	public function getHost    () : string { return static::getPart('host');     } #                        site.com
	public function getPort    () : string { return static::getPart('port');     } #                                 8080
	public function getPath    () : string { return static::getPart('path');     } #                                     /path
	public function getQuery   () : string { return static::getPart('query');    } #                                           key=val
	public function getFragment() : string { return static::getPart('fragment'); } #                                                   anchor
	public function getDomain() :? string                                          # https://site.com
	{
		if (static::getHost())
		{
			if (static::getScheme())
			{
				return static::getScheme() . '//' . static::getHost();
			}

			return static::getHost();
		}

		return null;
	}

	public function getPart($name) : string
	{
		return get($this->parts, $name);
	}
}