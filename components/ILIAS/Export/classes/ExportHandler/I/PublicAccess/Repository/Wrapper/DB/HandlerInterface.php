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

namespace ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Wrapper\DB;

use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Element\CollectionInterface as ilExportHandlerPublicAccessRepositoryElementCollectionInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Element\HandlerInterface as ilExportHandlerPublicAccessRepositoryElementInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Key\CollectionInterface as ilExportHandlerPublicAccessRepositoryKeyCollectionInterface;

interface HandlerInterface
{
    public const TABLE_NAME = "export_public_access";

    public function storeElement(
        ilExportHandlerPublicAccessRepositoryElementInterface $element
    ): void;

    public function getElements(
        ilExportHandlerPublicAccessRepositoryKeyCollectionInterface $keys
    ): ilExportHandlerPublicAccessRepositoryElementCollectionInterface;

    public function getAllElements(): ilExportHandlerPublicAccessRepositoryElementCollectionInterface;

    public function deleteElements(
        ilExportHandlerPublicAccessRepositoryKeyCollectionInterface $keys
    ): void;

    public function buildSelectAllQuery(): string;

    public function buildSelectQuery(
        ilExportHandlerPublicAccessRepositoryKeyCollectionInterface $keys
    ): string;

    public function buldInsertQuery(
        ilExportHandlerPublicAccessRepositoryElementInterface $element
    ): string;

    public function buildDeleteQuery(
        ilExportHandlerPublicAccessRepositoryKeyCollectionInterface $keys
    );
}
