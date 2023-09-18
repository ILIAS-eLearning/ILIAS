<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->disableParallel();
    $rectorConfig->phpVersion(PhpVersion::PHP_80);
    $rectorConfig->sets([
        SetList::DEAD_CODE,
        SetList::TYPE_DECLARATION,
        SetList::CODE_QUALITY,
        SetList::EARLY_RETURN,
    ]);
};
