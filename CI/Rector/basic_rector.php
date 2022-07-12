<?php declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use ILIAS\CI\Rector\RemoveRequiresAndIncludesRector;
use ILIAS\CI\Rector\ChangeLicenseHeader;

return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    // We start with a single and sinle (own) rule. remove requires and include.
    $services->set(RemoveRequiresAndIncludesRector::class);
    // The second rule will add (or replace) e license-header for every class-file
    $services->set(ChangeLicenseHeader::class);
};
