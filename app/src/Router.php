<?php

namespace Challenge\App;

use Challenge\Logs;
use Challenge\Rest;

class Router
{
	/** @var Rest */
	protected $rest;

	public function __construct()
	{
		$this->rest = new Rest();
	}

	public function init(): bool
	{
		if (!$this->rest->getController())
		{
			return Logs::addError('Endpoint is not specified');
		}


		$class_name = __NAMESPACE__ . '\\' . $this->rest->getController() . 'Controller';

		if (class_exists($class_name))
		{
			/** @var Rest $instance */
			$instance = new $class_name();


			$method = 'method' . $this->rest->getMethod();

			if (method_exists($instance, $method))
			{
				call_user_func([$instance, $method]);


				# fallback

				$this->rest->outputDefault();
			}
		}


		return Logs::addError('Such endpoint does not exist');
	}
}