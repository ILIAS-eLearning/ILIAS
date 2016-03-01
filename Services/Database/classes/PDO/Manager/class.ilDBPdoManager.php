<?php

/**
 * Class ilDBPdoManager
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDBPdoManager implements ilDBManager {

	/**
	 * @var PDO
	 */
	protected $pdo;


	/**
	 * ilDBPdoManager constructor.
	 *
	 * @param \PDO $pdo
	 */
	public function __construct(\PDO $pdo) { $this->pdo = $pdo; }


	/**
	 * @param null $database
	 * @return array
	 */
	public function listTables($database = null) {
		$r = $this->pdo->query('SHOW TABLES ' . ($database ? ' IN ' . $database : ''));
		$tables = array();
		while ($data = $r->fetchColumn()) {
			$tables[] = $data;
		}

		return $tables;
	}
}
