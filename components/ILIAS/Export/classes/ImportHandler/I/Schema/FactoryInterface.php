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

namespace ILIAS\Export\ImportHandler\I\Schema;

use ILIAS\Export\ImportHandler\I\File\XML\HandlerInterface as ilImportHandlerXMLFileInterface;
use ILIAS\Export\ImportHandler\I\Path\HandlerInterface as ilImportHandlerPathInterface;
use ILIAS\Export\ImportHandler\I\Schema\CollectionInterface as ilImportHandlerSchemaCollectionInterface;
use ILIAS\Export\ImportHandler\I\Schema\Folder\FactoryInterface as ilImportHandlerSchemaFolderFactoryInterface;
use ILIAS\Export\ImportHandler\I\Schema\HandlerInterface as ilImportHandlerSchemaInterface;
use ILIAS\Export\ImportHandler\I\Schema\Info\FactoryInterface as ilImportHandlerSchemaInfoFactoryInterface;

interface FactoryInterface
{
    public function handler(): ilImportHandlerSchemaInterface;

    public function collection(): ilImportHandlerSchemaCollectionInterface;

    public function collectionFrom(
        ilImportHandlerXMLFileInterface $xml_file_handler,
        ilImportHandlerPathInterface $path_to_entities
    ): ilImportHandlerSchemaCollectionInterface;

    public function folder(): ilImportHandlerSchemaFolderFactoryInterface;

    public function info(): ilImportHandlerSchemaInfoFactoryInterface;
}
