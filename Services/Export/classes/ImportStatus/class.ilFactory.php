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

namespace ILIAS\Export\ImportStatus;

use ILIAS\Export\ImportStatus\I\Content\ilFactoryInterface as ilImportStatusContentFactoryInterface;
use ILIAS\Export\ImportStatus\I\Exception\ilExceptionInterface as ilImportStatusExceptionInterface;
use ILIAS\Export\ImportStatus\I\ilFactoryInterface as ilImportStatusFactoryInterface;
use ILIAS\Export\ImportStatus\I\ilCollectionInterface as ilImportStatusHandlerCollectionInterface;
use ILIAS\Export\ImportStatus\I\ilHandlerInterface as ilImportStatusHandlerInterface;
use ILIAS\Export\ImportStatus\Content\ilFactory as ilImportStatusContentFactory;
use ILIAS\Export\ImportStatus\ilCollection as ilImportStatusHandlerCollection;
use ILIAS\Export\ImportStatus\ilHandler as ilImportStatusHandler;
use ILIAS\Export\ImportStatus\Exception\ilException as ilImportStatusException;

class ilFactory implements ilImportStatusFactoryInterface
{
    public function content(): ilImportStatusContentFactoryInterface
    {
        return new ilImportStatusContentFactory();
    }

    public function handler(): ilImportStatusHandlerInterface
    {
        return new ilImportStatusHandler();
    }

    public function collection(): ilImportStatusHandlerCollectionInterface
    {
        return new ilImportStatusHandlerCollection($this);
    }

    public function exception(string $msg): ilImportStatusExceptionInterface
    {
        return new ilImportStatusException($msg);
    }
}
