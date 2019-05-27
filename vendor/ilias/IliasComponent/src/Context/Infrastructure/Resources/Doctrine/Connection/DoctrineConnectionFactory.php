<?php

namespace srag\IliasComponent\Context\Infrastructure\Resources\Doctrine\Connection;

use Doctrine\DBAL\Connection;

class DoctrineConnectionFactory {

	protected static $connection;


	public function getConnection(DoctrineConnection $connection_object):Connection {
		if (!self::$connection) {
			self::$connection = $connection_object->connect($this);
		}

		return self::$connection;
	}
}
