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

namespace ImportHandler\I\File\XML;

use ImportHandler\I\File\XML\ilHandlerInterface as ilXMLFileHanlderInterface;
use ImportHandler\I\File\XML\ilCollectionInterface as ilXMLFileHanlderCollectionInterface;
use ImportHandler\I\File\XML\Manifest\ilFactoryInterface as ilManifestFileFactoryInterface;
use ImportHandler\I\File\XML\Reader\ilFactoryInterface as ilXMLFileReaderFactoryInterface;
use ImportHandler\I\File\XML\Node\ilFactoryInterface as ilXMLFileNodeFactoryInterface;
use ImportHandler\I\File\XML\Export\ilFactoryInterface as ilXMLExportFileFactoryInterface;

interface ilFactoryInterface
{
    public function handler(): ilXMLFileHanlderInterface;

    public function collection(): ilXMLFileHanlderCollectionInterface;

    public function manifest(): ilManifestFileFactoryInterface;

    public function node(): ilXMLFileNodeFactoryInterface;

    public function export(): ilXMLExportFileFactoryInterface;
}
