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

namespace ILIAS\Export\ExportHandler\I;

use ILIAS\Export\ExportHandler\I\Consumer\FactoryInterface as ilExportHandlderConsumerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Info\FactoryInterface as ilExportHandlerInfoFactoryInterface;
use ILIAS\Export\ExportHandler\I\Manager\FactoryInterface as ilExportHandlerManagerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Part\FactoryInterface as ilExportHandlerPartFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\FactoryInterface as ilExportHandlerPublicAccessFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\FactoryInterface as ilExportHandlerRepositoryFactoryInterface;
use ILIAS\Export\ExportHandler\I\Table\FactoryInterface as ilExportHandlerTableFactoryInterface;
use ILIAS\Export\ExportHandler\I\Target\FactoryInterface as ilExportHandlerTargetFactoryInterface;
use ILIAS\Export\ExportHandler\I\Wrapper\FactoryInterface as ilExportHandlerWrapperFactoryInterface;

interface FactoryInterface
{
    public function part(): ilExportHandlerPartFactoryInterface;

    public function info(): ilExportHandlerInfoFactoryInterface;

    public function target(): ilExportHandlerTargetFactoryInterface;

    public function repository(): ilExportHandlerRepositoryFactoryInterface;

    public function publicAccess(): ilExportHandlerPublicAccessFactoryInterface;

    public function manager(): ilExportHandlerManagerFactoryInterface;

    public function consumer(): ilExportHandlderConsumerFactoryInterface;

    public function table(): ilExportHandlerTableFactoryInterface;

    public function wrapper(): ilExportHandlerWrapperFactoryInterface;
}
