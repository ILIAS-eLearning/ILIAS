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

namespace ILIAS\Export\ImportHandler\Schema\Folder;

use ILIAS\Export\ImportHandler\I\FactoryInterface as ImportHandlerFactoryInterface;
use ILIAS\Export\ImportHandler\I\Schema\Folder\FactoryInterface as SchemaFolderFactoryInterface;
use ILIAS\Export\ImportHandler\I\Schema\Folder\HandlerInterface as SchemaFolderInterface;
use ILIAS\Export\ImportHandler\Schema\Folder\Handler as SchemaFolder;
use ilLogger;

class Factory implements SchemaFolderFactoryInterface
{
    protected ImportHandlerFactoryInterface $import_handler;
    protected ilLogger $logger;

    public function __construct(
        ImportHandlerFactoryInterface $import_handler,
        ilLogger $logger
    ) {
        $this->import_handler = $import_handler;
        $this->logger = $logger;
    }

    public function handler(): SchemaFolderInterface
    {
        return new SchemaFolder(
            $this->import_handler,
            $this->logger
        );
    }
}
