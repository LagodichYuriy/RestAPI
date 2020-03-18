<?php

namespace Challenge;

class Rest
{
	const TYPE_JSON = 'JSON';
	const TYPE_XML  = 'XML';

	const METHOD_DELETE = 'DELETE';
	const METHOD_HEAD   = 'HEAD';
	const METHOD_POST   = 'POST';
	const METHOD_GET    = 'GET';
	const METHOD_PUT    = 'PUT';


	protected $type = self::TYPE_JSON;

	protected $method;


	/** @var URL */
	protected $url;

	protected $controller;

	protected $params = [];

	public function __construct()
	{
		static::parseMethod();
		static::parseURL();
	}

	protected function parseMethod(): bool
	{
		$method = get($_SERVER, 'REQUEST_METHOD');

		switch ($method)
		{
			case self::METHOD_DELETE:
			case self::METHOD_HEAD:
			case self::METHOD_POST:
			case self::METHOD_GET:
			case self::METHOD_PUT:

				$this->method = $method;

				return true;
		}

		return Logs::addError("Unexpected HTTP method type: `$method`");
	}

	protected function parseURL()
	{
		$this->url = new URL();

		$path = $this->url->getPath();

		$parts = explode('/', $path);

		$this->controller = get($parts, 1);

		switch ($this->method)
		{
			case self::METHOD_GET:  $this->params = (object) $_GET;  break;
			case self::METHOD_POST: $this->params = (object) $_POST; break;
		}
	}

	public function getMethod    (): string { return $this->method;     }
	public function getController(): string { return $this->controller; }
	public function getOutputType(): string { return $this->type;       }

	public function getParams(array $fields_required = [], array $fields_optional = []): \stdClass
	{
		$data = new \stdClass();

		foreach ($fields_required as $field_required)
		{
			if (!isset($this->params->{$field_required}))
			{
				Logs::addError("Field missed: `$field_required`");
			}

			$data->{$field_required} = get($this->params, $field_required);
		}

		foreach ($fields_optional as $field_optional)
		{
			$data->{$field_optional} = get($this->params, $field_optional);
		}

		return $data;
	}


	public function setController($value)
	{
		$this->controller = $value;
	}

	public function setOutputType(string $type): bool
	{
		if (static::isOutputTypeValid($type))
		{
			$this->type = $type;

			return true;
		}

		return Logs::addError("Unsupported output format: `$type`");
	}

	public static function isOutputTypeValid(string $type): bool
	{
		switch ($type)
		{
			case self::TYPE_JSON:
			case self::TYPE_XML:
				return true;
		}

		return false;
	}

	public function output(array $data = []): bool
	{
		$execution_time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
		$execution_time = number_format($execution_time, 3, '.', '');


		     if (Logs::hasErrors() or get($data, 'errors')) { $http_code = Headers::CODE_BAD_REQUEST; }
		else                                                { $http_code = Headers::CODE_SUCCESS;     }

		Headers::sendCode($http_code);

		$data = $data +
		[
			'result' => [],

			'errors' => Logs::getErrors(),

			'http_code' => $http_code,

			'execution_time' => $execution_time
		];

		if (APP_DEBUG)
		{
			$data['memory_used'] = round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB';
		}


		$type = static::getOutputType();

		switch ($type)
		{
			case self::TYPE_JSON:

				static::outputJSON($data);

				return true;
		}

		return Logs::addError("Output method is not implemented: `$type`");
	}

	public function outputResult($data)
	{
		static::output(['result' => $data]);
	}

	public function outputDefault()
	{
		static::output
		([
			'errors' => 'Internal server error: missed API response action',

			'http_code' => Headers::CODE_INTERNAL_SERVER_ERROR
		]);
	}

	public static function outputJSON($data)
	{
		Headers::setCORS();
		Headers::setContentJSON();


		$options = 0;

		if (APP_DEBUG)
		{
			$options |= JSON_PRETTY_PRINT;
		}

		echo json_encode($data, $options);

		die;
	}
}