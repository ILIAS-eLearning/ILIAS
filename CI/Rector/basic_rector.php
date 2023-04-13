<?php

declare(strict_types=1);

use ILIAS\CI\Rector\RemoveRequiresAndIncludesRector;
use ILIAS\CI\Rector\ChangeLicenseHeader;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->disableParallel();
    // We start with a single and sinle (own) rule. remove requires and include.
    $rectorConfig->rule(RemoveRequiresAndIncludesRector::class);
    // The second rule will add (or replace) e license-header for every class-file
    $rectorConfig->rule(ChangeLicenseHeader::class);
};
