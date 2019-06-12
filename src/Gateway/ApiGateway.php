<?php
chdir("../../");
global $DIC;

if (!file_exists(getcwd() . '/ilias.ini.php')) {
	header('Location: ./setup/setup.php');
	exit();
}

require_once 'Services/Context/classes/class.ilContext.php';
ilContext::init(ilContext::CONTEXT_WEB);

require_once 'Services/Init/classes/class.ilInitialisation.php';
ilInitialisation::initILIAS();

$DIC->ctrl()->initBaseClass('ilStartUpGUI');
$DIC->ctrl()->setTargetScript('ilias.php');

use Slim\App;
use DI\ContainerBuilder;
class ApiGateway extends App {
	protected function configureContainer(ContainerBuilder $builder) {
		$builder->addDefinitions(__DIR__ . '/slim-config.php');

		$builder->enableCompilation(__DIR__ . '/tmp');
		$builder->writeProxiesToFile(true, '/tmp/proxies');
	}
};