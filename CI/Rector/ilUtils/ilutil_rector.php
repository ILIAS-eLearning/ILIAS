<?php declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use ILIAS\CI\Rector\ilUtils\ReplaceUtilSendMessageRector;
use Rector\Core\Configuration\Option;
use ILIAS\CI\Rector\DIC\DICMemberResolver;
use ILIAS\CI\Rector\DIC\DICDependencyManipulator;

return function (ContainerConfigurator $containerConfigurator) : void {
    // language level
    $language_level = require __DIR__ . "/../language_level.php";
    $language_level($containerConfigurator);
    
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::DEBUG, false);
    $parameters->set(Option::SKIP, [
        // there a several classes which make Rector break (multiple classes
        // in one file, wrong declarations in inheritance, ...)
        "Modules/LTIConsumer",
        "Services/LTI",
        "Services/SOAPAuth/include"
    ]);
    
    $services = $containerConfigurator->services();
    $services->set(DICMemberResolver::class)->autowire();
    $services->set(DICDependencyManipulator::class)->autowire();
    $services->set(ReplaceUtilSendMessageRector::class);
};
