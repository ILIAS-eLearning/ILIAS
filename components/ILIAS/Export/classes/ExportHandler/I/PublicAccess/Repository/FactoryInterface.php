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

namespace ILIAS\Export\ExportHandler\I\PublicAccess\Repository;

use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Element\FactoryInterface as ilExportHandlerPublicAccessRepositoryElementFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\HandlerInterface as ilExportHandlerPublicAccessRepositoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Key\FactoryInterface as ilExportHandlerPublicAccessRepositoryKeyFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Values\FactoryInterface as ilExportHandlerPublicAccessRepositoryValuesFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Wrapper\FactoryInterface as ilExportHandlerPublicAccessRepositoryWrapperFactoryInterface;

interface FactoryInterface
{
    public function element(): ilExportHandlerPublicAccessRepositoryElementFactoryInterface;

    public function handler(): ilExportHandlerPublicAccessRepositoryInterface;

    public function key(): ilExportHandlerPublicAccessRepositoryKeyFactoryInterface;

    public function values(): ilExportHandlerPublicAccessRepositoryValuesFactoryInterface;

    public function wrapper(): ilExportHandlerPublicAccessRepositoryWrapperFactoryInterface;
}
