<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use ILIAS\CI\Rector\RemoveRequiresAndIncludesRector;
use ILIAS\CI\Rector\ChangeLicenseHeader;
use Rector\Config\RectorConfig;

return static function (RectorConfig $c): void {
    $c->disableParallel();
    // We start with a single and sinle (own) rule. remove requires and include.
    $c->rule(RemoveRequiresAndIncludesRector::class);
    // The second rule will add (or replace) e license-header for every class-file
    $c->rule(ChangeLicenseHeader::class);
};
