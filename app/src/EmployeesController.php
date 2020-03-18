<?php

namespace Challenge\App;

use Challenge\Rest;

class EmployeesController extends Rest
{
	const METHOD_GET_FIELDS_REQUIRED = [];
	const METHOD_GET_FIELDS_OPTIONAL = ['order_field', 'order_direction', 'page', 'limit'];

	public function methodGET()
	{
		$request = static::getParams(self::METHOD_GET_FIELDS_REQUIRED, self::METHOD_GET_FIELDS_OPTIONAL);

		$employees = EmployeesModel::getEmployees
		([
			'order_field'     => $request->order_field,
			'order_direction' => $request->order_direction,
			'page'            => $request->page,
			'limit'           => $request->limit
		]);

		static::outputResult($employees);
	}
}