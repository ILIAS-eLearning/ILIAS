<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Set\ValueObject\SetList;

return static function (ContainerConfigurator $containerConfigurator) : void {
    // basic rules
    $basic = include "basic_rector.php";
    $basic($containerConfigurator);

    // language level
    $language_level = include "language_level.php";
    $language_level($containerConfigurator);

    // After that, you can try to introduce TypeDeclarations in your component
    $containerConfigurator->import(SetList::TYPE_DECLARATION);

    // This SetList introduces some changes concerning PHP7.4,
    // see libs/composer/vendor/rector/rector/config/set/php74.php for more details.
    $containerConfigurator->import(SetList::PHP_74);

    // The DeadCode List is quite aggressive, but it helps to find things in code, which could be removed.
    // Or no longer needed PHPDoc due to introduced types
    // $containerConfigurator->import(SetList::DEAD_CODE);

    // CodeQuality should be used with care... But again. helps to find pieces of code, which could be optimized.
    // $containerConfigurator->import(SetList::CODE_QUALITY);
};
