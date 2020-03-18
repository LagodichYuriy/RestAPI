<?php

namespace Challenge\App;

use Challenge\Logs;
use Challenge\Rest;

class ReviewsController extends Rest
{
	const METHOD_GET_FIELDS_REQUIRED = [];
	const METHOD_GET_FIELDS_OPTIONAL = ['sort_field', 'order_field', 'order_direction', 'page', 'limit', 'employee_id', 'reviewer_id'];

	const METHOD_POST_FIELDS_REQUIRED = ['employee_id', 'reviewer_id', 'rating'];
	const METHOD_POST_FIELDS_OPTIONAL = ['comment'];

	const RATING_MIN = 1;
	const RATING_MAX = 5;

	public function methodGET()
	{
		$request = static::getParams(self::METHOD_GET_FIELDS_REQUIRED, self::METHOD_GET_FIELDS_OPTIONAL);

		$reviews = ReviewsModel::getReviews
		([
			'order_field'     => $request->order_field,
			'order_direction' => $request->order_direction,
			'page'            => $request->page,
			'limit'           => $request->limit,
			'employee_id'     => $request->employee_id,
			'reviewer_id'     => $request->reviewer_id
		]);

		static::outputResult($reviews);
	}

	public function methodPOST()
	{
		$request = static::getParams(self::METHOD_POST_FIELDS_REQUIRED, self::METHOD_POST_FIELDS_OPTIONAL);

		if ($request->reviewer_id == $request->employee_id)
		{
			Logs::addError('An employee cannot review himself');

			return;
		}

		if (!EmployeesModel::isEmployeeExists($request->employee_id))
		{
			Logs::addError('This employee does not exist');

			return;
		}

		if (!EmployeesModel::isEmployeeExists($request->reviewer_id))
		{
			Logs::addError('This reviewer does not exist');

			return;
		}

		if (ReviewsModel::isReviewExists($request->employee_id, $request->reviewer_id))
		{
			Logs::addError('This review already exists in the database, you have to wait a year between reviews');

			return;
		}


		if ($request->rating < self::RATING_MIN or $request->rating > self::RATING_MAX or (int) $request->rating != $request->rating)
		{
			Logs::addError('Invalid rating value');

			return;
		}


		$review_id = ReviewsModel::addReview
		([
			'employee_id' => $request->employee_id,
			'reviewer_id' => $request->reviewer_id,
			'rating'      => $request->rating,
			'comment'     => $request->comment
		]);

		if (!$review_id)
		{
			Logs::addError('Unexpected server error');

			return;
		}

		static::outputResult
		([
			'review_id' => $review_id
		]);
	}
}