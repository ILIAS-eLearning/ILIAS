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

use ILIAS\Export\ImportHandler\I\File\XML\Export\ilHandlerInterface as ilXMLExportFileHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\ilCollectionInterface as ilXMLExportFileCollectionInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\Component\ilFactoryInterface as ilComponentXMLExportFileHandlerFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\DataSet\ilFactoryInterface as ilDataSetXMLExportFileHandlerFactoryInterface;
use SplFileInfo;

interface ilFactoryInterface
{
    public function withFileInfo(SplFileInfo $file_info): ilXMLExportFileHandlerInterface;

    public function collection(): ilXMLExportFileCollectionInterface;

    public function component(): ilComponentXMLExportFileHandlerFactoryInterface;

    public function dataSet(): ilDataSetXMLExportFileHandlerFactoryInterface;
}
