<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use ILIAS\CI\Rector\RemoveRequiresAndIncludesRector;
use ILIAS\CI\Rector\ChangeLicenseHeader;
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Set\ValueObject\SetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\DowngradeLevelSetList;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;

return static function (RectorConfig $c): void {
    $c->disableParallel();
    $c->phpVersion(PhpVersion::PHP_80);
    $c->sets([
        SetList::PHP_80,
        SetList::PHP_81,
        SetList::PHP_82,
        LevelSetList::UP_TO_PHP_82,
        DowngradeLevelSetList::DOWN_TO_PHP_80,
    ]);
    $c->services()->remove(ClosureToArrowFunctionRector::class);
};
