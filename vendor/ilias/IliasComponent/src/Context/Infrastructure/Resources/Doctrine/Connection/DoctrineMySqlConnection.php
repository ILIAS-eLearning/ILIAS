<?php

namespace srag\IliasComponent\Context\Infrastructure\Resources\Doctrine\Connection;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOMySql\Driver;

class DoctrineMySqlConnection implements DoctrineConnection {

	private $connectionParams = [];


	/**
	 * DoctrineMySqlConnection constructor.
	 *
	 * @param string $host
	 * @param string $user
	 * @param string $pasword
	 * @param string $dbname
	 * @param string $charset
	 */
	public function __construct(string $host, string $user, string $pasword, string $dbname, string $charset)/*:void*/ {

		$this->connectionParams = [
			"driver" => ("pdo_mysql"),
			"host" => $host,
			//"port" => self::dic()->clientIni()->readVariable("db","port"),
			"user" => $user,
			"password" => $pasword,
			"dbname" => $dbname,
			"charset" => "utf8"
		];
	}


	public function connect(DoctrineConnectionFactory $factory): Connection {
		return new Connection([
			$this->connectionParams
		], new Driver());
	}
}

