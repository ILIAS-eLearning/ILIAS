<?php

namespace srag\IliasComponent\Context\Infrastructure\Resources\Doctrine\Connection;

use Doctrine\DBAL\Connection;

interface DoctrineConnection {

	//only accept connect by factory
	public function connect(DoctrineConnectionFactory $factory): Connection;
}