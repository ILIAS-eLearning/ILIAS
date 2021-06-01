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

return static function (ContainerConfigurator $containerConfigurator) : void {
    // get parameters
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_74);
    
    // Define what rule sets will be applied
    $containerConfigurator->import(SetList::DEAD_CODE);
    $containerConfigurator->import(SetList::CODE_QUALITY);
    $containerConfigurator->import(SetList::CODING_STYLE);
    $containerConfigurator->import(SetList::TYPE_DECLARATION_STRICT);
    $containerConfigurator->import(SetList::PHP_74);
    
    // get services (needed for register a single rule)
    $services = $containerConfigurator->services();
    
    // register a single rule
    $services->set(ChangeConstantVisibilityRector::class);
    $services->set(ChangeMethodVisibilityRector::class);
    $services->set(ReturnTypeDeclarationRector::class);
    $services->set(ParamTypeFromStrictTypedPropertyRector::class);
    $services->set(ParamTypeDeclarationRector::class);
    
    // Own
    $services->set(\ILIAS\CI\Rector\RemoveRequiresAndIncludesRector::class);
};
