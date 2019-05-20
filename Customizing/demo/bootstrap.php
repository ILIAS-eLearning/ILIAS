<?php
// bootstrap.php
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

//require_once "../../libs/composer/vendor/autoload.php";

// Create a simple "default" Doctrine ORM configuration for Annotations
$isDevMode = true;
//$config = Setup::createAnnotationMetadataConfiguration(array("/home/mstuder/Develop/ILIAS/core_trunk/ilias/src/Modules/Course/Domain/Entity"), $isDevMode);
// or if you prefer yaml or XML
$config = Setup::createXMLMetadataConfiguration(["/var/www/ilias/src/Modules/Course/Infrastructure/Resources/Config/Doctrine/Entity",
	"/var/www/ilias/src/Modules/User/Infrastructure/Resources/Config/Doctrine/Entity"], $isDevMode);
//$config = Setup::createYAMLMetadataConfiguration(array(__DIR__."/config/yaml"), $isDevMode);

$config->addEntityNamespace("ILIAS","ILIAS\Modules\Course\Domain\Entity");
$config->addEntityNamespace("ILIAS","ILIAS\Modules\User\Domain\Entity");
$config->addFilter("coursemember", "ILIAS\Modules\Course\Infrastructure\Repository\Doctrine\CourseMemberBaseFilter");

//$config

//$config->addEntityNamespace('', 'ILIAS\Modules\Domain\Entity');

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
$entityManager = EntityManager::create($connectionParams, $config);

