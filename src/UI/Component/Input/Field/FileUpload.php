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

/**
 * @author  Thibeau Fuhrer <thf@studer-raimann.ch>
 */
interface FileUpload
{
    public function getUploadHandler() : UploadHandler;

    public function withMaxFileSize(int $size_in_bytes) : FileUpload;

    public function getMaxFileSize() : int;

    public function withMaxFiles(int $max_file_amount) : FileUpload;

    public function getMaxFiles() : int;

    /**
     * @param string[] $mime_types
     */
    public function withAcceptedMimeTypes(array $mime_types) : FileUpload;

    /**
     * @return string[]
     */
    public function getAcceptedMimeTypes() : array;
}
