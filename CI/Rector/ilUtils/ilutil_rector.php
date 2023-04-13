<?php

declare(strict_types=1);

use ILIAS\CI\Rector\ilUtils\ReplaceUtilSendMessageRector;
use Rector\Core\Configuration\Option;
use ILIAS\CI\Rector\DIC\DICMemberResolver;
use ILIAS\CI\Rector\DIC\DICDependencyManipulator;
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->disableParallel();
    $rectorConfig->parameters()->set(Option::SKIP, [
        // there a several classes which make Rector break (multiple classes
        // in one file, wrong declarations in inheritance, ...)
        "Modules/LTIConsumer",
        "Services/LTI",
        "Services/SOAPAuth/include"
    ]);
    $rectorConfig->parameters()->set(Option::DEBUG, false);

    $rectorConfig->phpVersion(PhpVersion::PHP_80);

    $rectorConfig->services()->set(DICMemberResolver::class)->autowire();
    $rectorConfig->services()->set(DICDependencyManipulator::class)->autowire();
    $rectorConfig->services()->set(ReplaceUtilSendMessageRector::class);
};
