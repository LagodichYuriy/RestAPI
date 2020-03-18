<?php

namespace Challenge;

class Headers
{
	const CODE_SUCCESS               = 200;
	const CODE_TEMPORARY_REDIRECT    = 307;
	const CODE_BAD_REQUEST           = 400;
	const CODE_UNAUTHORIZED          = 401;
	const CODE_FORBIDDEN             = 403;
	const CODE_NOT_FOUND             = 404;
	const CODE_INTERNAL_SERVER_ERROR = 500;

	public static function setCORS()
	{
		static::send('Access-Control-Allow-Origin: *');
	}

	public static function setContentJSON()
	{
		static::send('Content-Type: application/json; charset=UTF-8');
	}

	public static function sendCode(int $code)
	{
		http_response_code($code);
	}

	public static function send(string $header)
	{
		header($header);
	}
}