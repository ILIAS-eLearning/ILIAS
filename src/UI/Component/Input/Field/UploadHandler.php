<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Input\Field;

use ILIAS\FileUpload\Handler\BasicFileInfoResult;

/**
 * Interface UploadHandler
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface UploadHandler
{
    public const DEFAULT_FILE_ID_PARAMETER = 'file_id';


    /**
     * @return string, defaults to self::DEFAULT_FILE_ID_PARAMETER
     */
    public function getFileIdentifierParameterName() : string;


    /**
     * @return string of the URL where dropped files are sent to. This URL must
     * make sure the upload is handled and a \ILIAS\FileUpload\Handler\HandlerResult is returned as JSON.
     */
    public function getUploadURL() : string;


    /**
     * @return string of the URL where in GUI deleted files are handled. The URL
     * is called by POST with a field with name from getFileIdentifierParameterName()
     * and the FileID of the deleted file.
     */
    public function getFileRemovalURL() : string;


    /**
     * @return string of the URL where in GUI existing files are handled. The URL
     * is called by GET with a field with name from getFileIdentifierParameterName()
     * and the FileID of the desired file. Return a FI
     */
    public function getExistingFileInfoURL() : string;


    /**
     * @param array $file_ids
     *
     * @return BasicFileInfoResult[]
     */
    public function getInfoForExistingFiles(array $file_ids) : array;
}
