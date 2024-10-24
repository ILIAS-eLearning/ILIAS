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

namespace ILIAS\Export\ImportHandler\File\XSD;

use ILIAS\Export\ImportHandler\File\Handler as ilImportHandlerFile;
use ILIAS\Export\ImportHandler\I\File\XSD\HandlerInterface as ilImportHandlerXSDFileInterface;
use SplFileInfo;

class Handler extends ilImportHandlerFile implements ilImportHandlerXSDFileInterface
{
    public function withFileInfo(SplFileInfo $file_info): ilImportHandlerXSDFileInterface
    {
        $clone = clone $this;
        $clone->spl_file_info = $file_info;
        return $clone;
    }
}
