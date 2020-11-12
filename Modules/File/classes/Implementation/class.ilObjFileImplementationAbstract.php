<?php

/**
 * Class ilObjFileImplementationAbstract
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilObjFileImplementationAbstract implements ilObjFileImplementationInterface
{

    /**
     * @inheritDoc
     */
    public function createDirectory()
    {
        // noting to do
    }

    /**
     * @inheritDoc
     */
    public function replaceFile($a_upload_file, $a_filename)
    {

    }

    /**
     * @inheritDoc
     */
    public function addFileVersion($a_upload_file, $a_filename)
    {

    }

    /**
     * @inheritDoc
     */
    public function clearDataDirectory()
    {
        // noting to do here
    }

    /**
     * @inheritDoc
     */
    public function setFileType($a_type)
    {

    }

    /**
     * @inheritDoc
     */
    public function setFileSize($a_size)
    {

    }

    public function getFileSize()
    {

    }

    /**
     * @inheritDoc
     */
    public function setVersion($a_version)
    {

    }

    public function getVersion()
    {

    }

    /**
     * @inheritDoc
     */
    public function setMaxVersion($a_max_version)
    {

    }

    public function getMaxVersion()
    {

    }

    /**
     * @inheritDoc
     */
    public function storeUnzipedFile($a_upload_file, $a_filename)
    {

    }

    /**
     * @inheritDoc
     */
    public function getSpecificVersion($version_id)
    {

    }

    /**
     * @inheritDoc
     */
    public function rollback($version_id)
    {

    }

}
