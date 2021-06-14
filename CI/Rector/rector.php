<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Rector\Core\ValueObject\PhpVersion;
use Utils\Rector\RemoveRequiresAndIncludesRector;
use Rector\Set\ValueObject\SetList;
use Rector\Visibility\Rector\ClassConst\ChangeConstantVisibilityRector;
use Rector\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\Param\ParamTypeFromStrictTypedPropertyRector;
use Rector\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector;
use Rector\Set\ValueObject\DowngradeSetList;

return static function (ContainerConfigurator $containerConfigurator) : void {
    // We need the parameters to set e.g. the language level of PHP
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_74);
    
    // get services (needed for register a single rule)
    $services = $containerConfigurator->services();
    
    // We start with a single and sinle (own) rule. remove requires and include.
    $services->set(\ILIAS\CI\Rector\RemoveRequiresAndIncludesRector::class);
    
    // After that, you can try to introduce TypeDeclarations in your component
    // $containerConfigurator->import(SetList::TYPE_DECLARATION);
    
    // This SetList intricuces some changes concerning PHP7.4,
    // see libs/composer/vendor/rector/rector/config/set/php74.php for more details.
    $containerConfigurator->import(SetList::PHP_74);
    
    // You can introduce or modify visibilities of constants and/or methods with there rectors:
    // $services->set(ChangeConstantVisibilityRector::class);
    // $services->set(ChangeMethodVisibilityRector::class);

    // The DeadCode List is quite aggressive, but it helps to finds things in code, which could be removed
    // $containerConfigurator->import(SetList::DEAD_CODE);

    // CodeQuality should be used with care... But again. helps to find pieces of code, which could be optimized.
    // $containerConfigurator->import(SetList::CODE_QUALITY);
};
