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

namespace ILIAS\Export\ExportHandler\Repository\Stakeholder;

use ILIAS\Export\ExportHandler\I\Repository\Stakeholder\FactoryInterface as ilExportHandlerRepositoryStakeholderFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Stakeholder\HandlerInterface as ilExportHandlerRepositoryStakeholderInterface;
use ILIAS\Export\ExportHandler\Repository\Stakeholder\Handler as ilExportHandlerRepositoryStakeholder;

class Factory implements ilExportHandlerRepositoryStakeholderFactoryInterface
{
    public function handler(): ilExportHandlerRepositoryStakeholderInterface
    {
        return new ilExportHandlerRepositoryStakeholder();
    }
}
