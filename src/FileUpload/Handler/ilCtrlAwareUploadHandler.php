<<?php

namespace ILIAS\FileUpload\Handler;

/**
 * Class ilCtrlAwareUploadHandler
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilCtrlAwareUploadHandler
{

    public const DEFAULT_FILE_ID_PARAMETER = 'file_id';


    /**
     * @return string, defaults to self::DEFAULT_FILE_ID_PARAMETER
     */
    public function getFileIdentifierParameterName() : string;


    /**
     * @return string of the URL where dropped files are sent to. This URL must
     * make sure the upload is handled and a HandlerResult is returned as JSON.
     */
    public function getUploadURL() : string;


    /**
     * @return string of the URL where in GUI deleted files are handled. The URL
     * is called by POST with a field with name from getFileIdentifierParameterName()
     * and the FileID of the deleted file.
     */
    public function getFileRemovalURL() : string;


    /**
     * Sine this is a ilCtrl aware UploadHandler executeCommand MUST be
     * implemented. The Implementation MUST make sure, the Upload and the Removal
     * Command are handled correctly
     */
    public function executeCommand() : void;


    /**
     * @return HandlerResult
     */
    public function getResult() : HandlerResult;
}

