<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
namespace ILIAS\UI\Component\Input\Field;

use ILIAS\FileUpload\Handler\BasicFileInfoResult;
use ILIAS\FileUpload\Handler\FileInfoResult;

/**
 * Interface UploadHandler
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface UploadHandler
{
    public const DEFAULT_FILE_ID_PARAMETER = 'file_id';


    /**
     * @return string defaults to self::DEFAULT_FILE_ID_PARAMETER
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

    public function getInfoResult(string $identifier) : ?FileInfoResult;
}
