<?php

namespace Challenge\App;

use function Challenge\database;

use function Challenge\get;
use Challenge\Logs;

class ReviewsModel
{
	const ORDER_FIELD_ID          = 'id';
	const ORDER_FIELD_EMPLOYEE_ID = 'employee_id';
	const ORDER_FIELD_REVIEWER_ID = 'reviewer_id';
	const ORDER_FIELD_RATING      = 'rating';
	const ORDER_FIELD_CREATED     = 'created';

	const ORDER_DIRECTION_ASC  = 'ASC';
	const ORDER_DIRECTION_DESC = 'DESC';

	const WHERE_FIELD_REVIEWER_ID = 'reviewer_id';
	const WHERE_FIELD_EMPLOYEE_ID = 'employee_id';

	const LIMIT_MAX = 100;

	public static function addReview(array $options):? int
	{
		if (!isset($options['employee_id']))
		{
			Logs::addWarning('Missed field value: employee_id');

			return null;
		}

		if (!isset($options['reviewer_id']))
		{
			Logs::addWarning('Missed field value: reviewer_id');

			return null;
		}


		$database = database();

		return $database->insert('reviews', ['employee_id', 'reviewer_id', 'rating', 'comment'],
		[
			get($options, 'employee_id'),
			get($options, 'reviewer_id'),
			get($options, 'rating'),
			get($options, 'comment')
		]);
	}

	public static function getReviews(array $options = []): array
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

		$extra_where = '';

		$reviewer_id = get($options, 'reviewer_id');
		$employee_id = get($options, 'employee_id');

		if ($reviewer_id) { $extra_where .= 'AND reviews.reviewer_id = :reviewer_id' . PHP_EOL; }
		if ($employee_id) { $extra_where .= 'AND reviews.reviewer_id = :employee_id' . PHP_EOL; }

		$database = database();
		$database->prepare
		("
			SELECT
				reviews.id,
				reviews.rating,
				reviews.comment,
				reviews.created,
				
				employees.id         AS employee_id,
				employees.first_name AS employee_first_name,
				employees.last_name  AS employee_last_name,
				employees.email      AS employee_email,
				
				reviewers.id         AS reviewer_id,
				reviewers.first_name AS reviewer_first_name,
				reviewers.last_name  AS reviewer_last_name,
				reviewers.email      AS reviewer_email
			FROM
				reviews
			LEFT JOIN
				employees AS employees ON reviews.employee_id = employees.id
			LEFT JOIN
				employees AS reviewers ON reviews.reviewer_id = reviewers.id
			WHERE
				1 = 1
				
				$extra_where
				
			ORDER BY
				$options->order_field $options->order_direction
			LIMIT
				:limit
			OFFSET
				:offset
		");

		$database->bindInt(':limit',  $options->limit);
		$database->bindInt(':offset', $options->offset);

		if ($reviewer_id) { $database->bindInt(':reviewer_id', $reviewer_id); }
		if ($employee_id) { $database->bindInt(':employee_id', $employee_id); }

		return $database->select();
	}

	public static function isReviewExists(int $employee_id, int $reviewer_id): bool
	{
		$database = database();
		$database->prepare
		("
			SELECT
				id
			FROM
				reviews
			WHERE
				employee_id = :employee_id
			AND
				reviewer_id = :reviewer_id
			AND
				created > DATE_SUB(NOW(), INTERVAL 1 YEAR)
		");

		$database->bindInt(':employee_id', $employee_id);
		$database->bindInt(':reviewer_id', $reviewer_id);

		return (bool) $database->select();
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
			case self::ORDER_FIELD_EMPLOYEE_ID:
			case self::ORDER_FIELD_REVIEWER_ID:
			case self::ORDER_FIELD_RATING:
				return true;
		}

		return false;
	}

	public static function isValidWhereField(string $field): bool
	{
		switch ($field)
		{
			case self::WHERE_FIELD_EMPLOYEE_ID:
			case self::WHERE_FIELD_REVIEWER_ID:
				return true;
		}

		return false;
	}
}