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
 * Interface HandlerResult
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface HandlerResult extends JsonSerializable
{
    public const STATUS_OK = 1;
    public const STATUS_FAILED = 2;
    public const STATUS_PARTIAL = 3;


    public function getStatus(): int;


    public function getFileIdentifier(): string;


    public function getMessage(): string;
}
