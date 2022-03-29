<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\FileUpload\Exception\IllegalStateException;
use ILIAS\Filesystem\Exception\DirectoryNotFoundException;

interface ilObjectCustomIcon
{
    /**
     * @return string[]
     */
    public function getSupportedFileExtensions() : array;

    /**
     * @throws FileAlreadyExistsException
     * @throws FileNotFoundException
     * @throws IOException
     */
    public function saveFromSourceFile(string $sourceFilePath) : void;

    /**
     * @throws IllegalStateException
     * @throws FileNotFoundException
     * @throws IOException
     */
    public function saveFromHttpRequest() : void;

    public function copy(int $targetObjId) : void;

    /**
     * Should be called if a consuming object is removed from system.
     * The implementer MUST delete all object specific custom icon data (folders, icons, persistent data)
     */
    public function delete() : void;

    /**
     * Should be called if a consuming object just wants to delete the icon
     * The implementer MUST only delete the icon itself and corresponding persistent data (e.g. stored in a database)
     */
    public function remove() : void;

    public function exists() : bool;

    public function getFullPath() : string;

    /**
     * @throws DirectoryNotFoundException
     * @throws FileNotFoundException
     * @throws IOException
     */
    public function createFromImportDir(string $source_dir) : void;
}
