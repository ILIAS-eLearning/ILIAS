<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilObjectCustomIcon
 */
interface ilObjectCustomIcon
{
    /**
     * @return string[]
     */
    public function getSupportedFileExtensions() : array ;

    /**
     * @param string $sourceFilePath
     * @throws \ILIAS\Filesystem\Exception\FileAlreadyExistsException
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    public function saveFromSourceFile(string $sourceFilePath);

    /**
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    public function saveFromHttpRequest();

    /**
     * @param int $targetObjId
     */
    public function copy(int $targetObjId);

    /**
     * Should be called if a consuming object is removed from system.
     * The implementer MUST delete all object specific custom icon data (folders, icons, persistent data)
     */
    public function delete();

    /**
     * Should be called if a consuming object just wants to delete the icon
     * The implementer MUST only delete the icon itself and corresponding persistent data (e.g. stored in a database)
     */
    public function remove();

    /**
     * @return bool
     */
    public function exists() : bool ;

    /**
     * @return string
     */
    public function getFullPath() : string ;
}
