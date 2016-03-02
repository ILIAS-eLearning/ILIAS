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
//		$str = 'SHOW TABLES ' . ($database ? ' IN ' . $database : '') . ' NOT LIKE \'%_seq\'';
		$str = 'SHOW TABLES ' . ($database ? ' IN ' . $database : '');
		$r = $this->pdo->query($str);
		$tables = array();
		while ($data = $r->fetchColumn()) {
			if(strpos($data, '_seq') === false){
				$tables[] = $data;
			}
		}

		return $tables;
	}


	/**
	 * @param null $database
	 * @return array
	 */
	public function listSequences($database = null) {
		$r = $this->pdo->query('SHOW TABLES ' . ($database ? ' IN ' . $database : '') . ' LIKE \'%_seq\'');
		$tables = array();
		while ($data = $r->fetchColumn()) {
			$tables[] = $data;
		}

		return $tables;
	}
}
