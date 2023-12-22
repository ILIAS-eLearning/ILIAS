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

namespace ILIAS\Export\ImportHandler\File\XML;

use ilLogger;
use ILIAS\Export\ImportHandler\File\XML\Manifest\ilFactory as ilManifestFileFactory;
use ILIAS\Export\ImportHandler\I\File\XML\Export\ilFactoryInterface as ilXMLExportFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\ilFactoryInterface as ilXMLFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\ilCollectionInterface as ilXMLFileHanlderCollectionInterface;
use ILIAS\Export\ImportHandler\I\File\XML\ilHandlerInterface as ilXMLFileHanlderInterface;
use ILIAS\Export\ImportHandler\File\XML\ilHandler as ilXMLFileHanlder;
use ILIAS\Export\ImportHandler\File\XML\ilCollection as ilXMLFileHanlderCollection;
use ILIAS\Export\ImportHandler\I\File\XML\Manifest\ilFactoryInterface as ilManifestFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\ilFactoryInterface as ilFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Node\ilFactoryInterface as ilXMLFileNodeFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\ilFactoryInterface as ilParserFactoryInterface;
use ILIAS\Export\ImportStatus\ilFactory as ilStatusFactory;
use ILIAS\Export\ImportHandler\File\XML\Node\ilFactory as ilXMLFileNodeFactory;
use ILIAS\Export\ImportHandler\File\XML\Export\ilFactory as ilXMLExportFileFactory;
use ILIAS\Export\ImportHandler\File\Namespace\ilFactory as ilFileNamespaceFactory;
use SplFileInfo;

class ilFactory implements ilXMLFileFactoryInterface
{
    protected ilLogger $logger;
    protected ilFileFactoryInterface $file;
    protected ilParserFactoryInterface $parser;

    public function __construct(
        ilFileFactoryInterface $file,
        ilParserFactoryInterface $parser,
        ilLogger $logger
    ) {
        $this->logger = $logger;
        $this->file = $file;
        $this->parser = $parser;
    }

    /*public function handler(): ilXMLFileHanlderInterface
    {
        return new ilXMLFileHanlder(
            new ilFileNamespaceFactory(),
            new ilStatusFactory()
        );
    }*/

    public function withFileInfo(SplFileInfo $file_info): ilXMLFileHanlderInterface
    {
        return (new ilXMLFileHanlder(
            new ilFileNamespaceFactory(),
            new ilStatusFactory()
        ))->withFileInfo($file_info);
    }

    public function collection(): ilXMLFileHanlderCollectionInterface
    {
        return new ilXMLFileHanlderCollection();
    }

    public function manifest(): ilManifestFileFactoryInterface
    {
        return new ilManifestFileFactory(
            $this->file,
            $this->parser,
            $this->logger
        );
    }

    public function node(): ilXMLFileNodeFactoryInterface
    {
        return new ilXMLFileNodeFactory($this->logger);
    }

    public function export(): ilXMLExportFileFactoryInterface
    {
        return new ilXMLExportFileFactory($this->logger);
    }
}
