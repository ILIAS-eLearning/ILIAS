<?php

namespace srag\IliasComponent\Context\Infrastructure\Resources\Doctrine\Connection;

use Doctrine\DBAL\Connection;

class DoctrineSqliteConnection implements DoctrineConnection {

	public function __construct() {

	}


	public function connect(DoctrineConnectionFactory $factory): Connection {
		return new Connection([
			'memory' => true,
		], new SqliteDriver());
	}
}
