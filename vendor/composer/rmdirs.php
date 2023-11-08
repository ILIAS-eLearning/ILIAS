<?php declare(strict_types=1);
/* Copyright (c) 2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Michael Jansen <mjansen@databay.de
 */

if (php_sapi_name() !== 'cli') {
    exit();
}

$dirsToDeleted = [
    __DIR__ . '/vendor/geshi/geshi/contrib',
];

foreach ($dirsToDeleted as $directoryPath) {
    try {
        if (!is_dir($directoryPath)) {
            continue;
        }

        $recursiveDirIter = new RecursiveDirectoryIterator($directoryPath, FilesystemIterator::SKIP_DOTS);
        $resourceIter = new RecursiveIteratorIterator($recursiveDirIter, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($resourceIter as $resource) {
            /** @var $resource SplFileInfo */
            if ($resource->isDir()) {
                rmdir($resource->getPathname());
            } else {
                unlink($resource->getPathname());
            }
            echo "Deleted " . $resource->getPathname() . "\n";
        }
        rmdir($directoryPath);
        echo "Deleted " . $directoryPath . "\n";
    } catch (Exception $e) {
        echo $e->getMessage() . "\n";
    }
}
