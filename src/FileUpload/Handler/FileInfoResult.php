<?php declare(strict_types=1);

namespace ILIAS\FileUpload\Handler;

use JsonSerializable;

/**
 * Interface FileInfoResult
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface FileInfoResult extends JsonSerializable
{

    public function getFileIdentifier() : string;


    public function getName() : string;


    public function getSize() : int;


    public function getMimeType() : string;
}
