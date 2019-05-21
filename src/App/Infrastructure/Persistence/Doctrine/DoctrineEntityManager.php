<?php
namespace ILIAS\App\Infrasctrutre\Persistence\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

class DoctrineEntityManager
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $entityManager;

	private $xml_meta_data_configuration = [];

	private static $instance;

	public static function getInstance()
	{
		if(!self::$instance) {
			self::$instance = new self;
		}

		return self::$instance;
	}


	/**
	 * @param array $xml_meta_data_configurations
	 *
	 * @return $this
	 * @throws \Doctrine\ORM\ORMException
	 */
	public function visit(array $xml_meta_data_configurations) {

		foreach($xml_meta_data_configurations as $xml_meta_data_configuration) {
			if(!in_array($xml_meta_data_configuration,$this->xml_meta_data_configuration)) {
				$this->addXmlMetaDataConfiguration($xml_meta_data_configuration);
				$config = Setup::createXMLMetadataConfiguration($this->xml_meta_data_configuration);


				// database configuration parameters
				$connectionParams = array(
					'driver' => 'pdo_mysql',
					'host' => 'localhost',
					'port' => '3306',
					'user' => 'root',
					'password' => 'root',
					'dbname' => 'ilias',
					'charset' => 'utf8',
				);


				// obtaining the entity manager
				$this->entityManager = EntityManager::create($connectionParams, $config);
			}
		}

		return $this;
	}

	private function addXmlMetaDataConfiguration($xml_meta_data_configuration) {
		$this->xml_meta_data_configuration[] = $xml_meta_data_configuration;
	}


	/**
	 * @return EntityManager
	 */
	public function getEntityManager(): EntityManagerInterface {
		return $this->entityManager;
	}
}

