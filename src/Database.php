<?php

namespace Challenge;

class Database
{
	protected $pdo;

	protected $pdo_options =
	[
		\PDO::ATTR_EMULATE_PREPARES   => false,
		\PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
		\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "' . APP_DATABASE_CHAR . '" COLLATE "' . APP_DATABASE_COLL . '"'
	];

	/** @var \PDOStatement last prepared statement */
	protected $stmt;

	public function __construct()
	{
		$this->pdo = new \PDO('mysql:host=' . APP_DATABASE_HOST . ';dbname='  . APP_DATABASE_NAME, APP_DATABASE_USER, APP_DATABASE_PASS);
		$this->pdo->exec('use ' . APP_DATABASE_NAME);
	}

	public function prepare(string $sql): \PDOStatement
	{
		$this->stmt = $this->pdo->prepare($sql);

		return $this->stmt;
	}

	public function bindInt($name, $value)
	{
		$this->stmt->bindParam($name, $value, \PDO::PARAM_INT);
	}

	public function select(int $mode = \PDO::FETCH_OBJ)
	{
		if (!$this->stmt->execute())
		{
			Logs::addError(get($this->stmt->errorInfo(), 2));

			return null;
		}

		return $this->stmt->fetchAll($mode);
	}

	public function selectFirst(int $mode = \PDO::FETCH_OBJ)
	{
		$result = static::select($mode);

		return get($result, 0, null);
	}

	public function insert(string $table, array $fields, array $values):? int
	{
		$fields_inline = static::getFieldsInline($fields);
		$values_inline = static::getValuesInline($values);

		$this->stmt = $this->pdo->prepare("INSERT INTO `$table` ($fields_inline) VALUES ($values_inline)");

		if (!$this->stmt->execute($values))
		{
			Logs::addError(get($this->stmt->errorInfo(), 2));

			return null;
		}

		return $this->pdo->lastInsertId();
	}

	protected static function getFieldsInline(array $fields): string
	{
		foreach ($fields as $index => $field)
		{
			$fields[$index] = "`$field`";
		}

		return implode(', ', $fields);
	}

	protected static function getValuesInline(array $values): string
	{
		$result = str_repeat('?', count($values));
		$result = str_split($result);
		$result = implode(', ', $result);

		return $result;
	}
}