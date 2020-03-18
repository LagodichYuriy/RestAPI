<?php

namespace Challenge\App;

use function Challenge\database;

use Challenge\Logs;

class EmployeesModel
{
	const ORDER_FIELD_ID         = 'id';
	const ORDER_FIELD_FIRST_NAME = 'first_name';
	const ORDER_FIELD_LAST_NAME  = 'last_name';
	const ORDER_FIELD_EMAIL      = 'email';

	const ORDER_DIRECTION_ASC  = 'ASC';
	const ORDER_DIRECTION_DESC = 'DESC';

	const LIMIT_MAX = 100;

	public static function getEmployees(array $options = []): array
	{
		# default options

		$options = array_filter($options) +
		[
			'order_field'     => self::ORDER_FIELD_ID,
			'order_direction' => self::ORDER_DIRECTION_ASC,
			'limit'           => self::LIMIT_MAX,
			'page'            => 1
		];

		$options = (object) $options;


		# validation

		if (!static::isValidOrderField($options->order_field))
		{
			Logs::addWarning("Invalid order field value: `$options->order_field`");

			return [];
		}

		if (!static::isValidOrderDirection($options->order_direction))
		{
			Logs::addWarning("Invalid order direction value: `$options->order_direction`");

			return [];
		}


		$options->limit  = static::filterLimit($options->limit);

		$options->offset = ($options->page - 1) * $options->limit;
		$options->offset = static::filterOffset($options->offset);


		# query

		$database = database();
		$database->prepare
		("
			SELECT
				*
			FROM
				employees
			ORDER BY
				$options->order_field $options->order_direction
			LIMIT
				:limit
			OFFSET
				:offset
		");

		$database->bindInt(':limit',  $options->limit);
		$database->bindInt(':offset', $options->offset);

		return $database->select();
	}

	public static function getEmployeeByID(int $id): \stdClass
	{
		$database = database();

		$database->prepare('SELECT * FROM employees WHERE id = :id');
		$database->bindInt(':id', $id);

		return $database->selectFirst();
	}

	public static function filterOffset(int $offset): int
	{
		if ($offset < 0)
		{
			return 0;
		}

		return (int) $offset;
	}

	public static function filterLimit(int $limit): int
	{
		if ($limit < 0 or $limit > self::LIMIT_MAX)
		{
			return self::LIMIT_MAX;
		}

		return (int) $limit;
	}

	public static function isEmployeeExists(int $id): bool
	{
		$database = database();
		$database->prepare
		("
			SELECT
				id
			FROM
				employees
			WHERE
				id = :id
		");

		$database->bindInt(':id', $id);

		return (bool) $database->select();
	}

	public static function isValidOrderDirection(string $direction): bool
	{
		$direction = mb_strtoupper($direction);

		switch ($direction)
		{
			case self::ORDER_DIRECTION_ASC:
			case self::ORDER_DIRECTION_DESC:
				return true;
		}

		return false;
	}

	public static function isValidOrderField(string $field): bool
	{
		switch ($field)
		{
			case self::ORDER_FIELD_ID:
			case self::ORDER_FIELD_FIRST_NAME:
			case self::ORDER_FIELD_LAST_NAME:
			case self::ORDER_FIELD_EMAIL:
				return true;
		}

		return false;
	}
}