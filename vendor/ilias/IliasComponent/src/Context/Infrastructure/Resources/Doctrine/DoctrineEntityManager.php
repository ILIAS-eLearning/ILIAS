<?php

namespace srag\IliasComponent\Context\Infrastructure\Resources\Doctrine;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Proxy\ProxyFactory;
use Doctrine\Common\Persistence\Mapping\Driver\DefaultFileLocator;
use Doctrine\ORM\Tools\Setup;

class DoctrineEntityManager {


	/** @var EventManager */
	protected $event_manager;
	/** @var entityManager */
	public $entity_manager;
	/** @var  $xml_meta_data_configuration */
	protected $xml_meta_data_configuration = [];


	/**
	 * DoctrineEntityManager constructor.
	 *
	 * @param EntityManager|null $entity_manager
	 */
	public function __construct(EntityManager $entity_manager = null) {
		$this->entity_manager = $entity_manager;
	}


	/**
	 * @param array $xml_meta_data_configurations
	 *
	 * @return DoctrineEntityManager
	 *
	 * @throws ORMException
	 */
	public function visit(array $xml_meta_data_configurations, Connection $connection, EventManager $eventManager = null): DoctrineEntityManager {

		$this->event_manager = $eventManager;
		$this->connection = $connection;

		$config = new Configuration();

		foreach ($xml_meta_data_configurations as $xml_meta_data_configuration) {
			if (!in_array($xml_meta_data_configuration, $this->xml_meta_data_configuration)) {
				$this->addXmlMetaDataConfiguration($xml_meta_data_configuration);
			}
		}

		$config = Setup::createXMLMetadataConfiguration($this->xml_meta_data_configuration, TRUE);

		return new self(EntityManager::create($connection, $config));
	}


	/**
	 * @param string $xml_meta_data_configuration
	 */
	protected function addXmlMetaDataConfiguration(string $xml_meta_data_configuration): void {
		$this->xml_meta_data_configuration[] = $xml_meta_data_configuration;
	}
}