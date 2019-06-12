<?php

namespace ILIAS\Gateway;

use DI\ContainerBuilder;
use DI\Bridge\Slim\App;

//class ApiGateway {

	//public function init() {
	class ApiGateway extends App {

			protected function configureContainer(ContainerBuilder $builder) {
				$builder->addDefinitions(__DIR__ . '/slim-config.php');

				$builder->enableCompilation(__DIR__ . '/tmp');
				$builder->writeProxiesToFile(true, '/tmp/proxies');

			}
		};
	//}
//}

//(new ApiGateway)->init();
