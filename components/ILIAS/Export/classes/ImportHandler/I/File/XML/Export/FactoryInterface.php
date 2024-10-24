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

namespace ILIAS\Export\ImportHandler\I\File\XML\Export;

use ILIAS\Export\ImportHandler\I\File\XML\Export\CollectionInterface as XMLExportFileCollectionInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\Component\FactoryInterface as ComponentXMLExportFileHandlerFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\DataSet\FactoryInterface as DataSetXMLExportFileHandlerFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\HandlerInterface as XMLExportFileHandlerInterface;
use SplFileInfo;

interface FactoryInterface
{
    public function withFileInfo(SplFileInfo $file_info): XMLExportFileHandlerInterface;

    public function collection(): XMLExportFileCollectionInterface;

    public function component(): ComponentXMLExportFileHandlerFactoryInterface;

    public function dataSet(): DataSetXMLExportFileHandlerFactoryInterface;
}
