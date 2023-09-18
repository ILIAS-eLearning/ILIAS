<?php

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

declare(strict_types=1);

namespace ILIAS\FileUpload\Handler;

use JsonSerializable;

/**
 * Interface FileInfoResult
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface FileInfoResult extends JsonSerializable
{
    public function getFileIdentifier(): string;


    public function getName(): string;

    /**
     * @return int in Bytes, we will change this to DataSize in the future
     */
    public function getSize(): int;


    public function getMimeType(): string;
}
