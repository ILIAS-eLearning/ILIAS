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

namespace ImportHandler\File\XSD;

use ImportHandler\I\File\XSD\ilFactoryInterface as ilXSDFileHandlerFactoryInterface;
use ImportHandler\I\File\XSD\ilHandlerInterface as ilXSDFileHandlerInterface;
use ImportHandler\File\XSD\ilHandler as ilXSDFileHandler;
use ImportHandler\File\Namespace\ilFactory as ilFileNamespaceFactory;
use SplFileInfo;

class ilFactory implements ilXSDFileHandlerFactoryInterface
{
    public function withFileInfo(SplFileInfo $file_info): ilXSDFileHandlerInterface
    {
        return (new ilXSDFileHandler(new ilFileNamespaceFactory()))->withFileInfo($file_info);
    }
}
