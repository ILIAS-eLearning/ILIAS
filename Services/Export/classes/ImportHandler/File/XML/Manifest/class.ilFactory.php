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

namespace ImportHandler\File\XML\Manifest;

use ilLogger;
use ImportHandler\File\XML\Manifest\ilHandler as ilManifestXMLFileHandler;
use ImportHandler\I\File\XML\Manifest\ilFactoryInterface as ilManifestFileFactoryInterface;
use ImportHandler\I\File\ilFactoryInterface as ilFileFactoryInterface;
use ImportHandler\I\File\XML\Manifest\ilHandlerCollectionInterface as ilManifestXMLFileHandlerCollectionInterface;
use ImportHandler\File\XML\Manifest\ilHandlerCollection as ilManifestXMLFileHandlerCollection;
use ImportHandler\I\File\XML\Manifest\ilHandlerInterface as ilManifestXMLFileHandlerInterface;
use ImportHandler\I\Parser\ilFactoryInterface as ilParserFactoryInterface;
use ImportStatus\ilFactory as ilImportStatusFactory;
use Schema\ilXmlSchemaFactory;

class ilFactory implements ilManifestFileFactoryInterface
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

    public function handler(): ilManifestXMLFileHandlerInterface
    {
        return new ilManifestXMLFileHandler(
            new ilXmlSchemaFactory(),
            new ilImportStatusFactory(),
            $this->file,
            $this->parser,
            $this->logger,
        );
    }

    public function handlerCollection(): ilManifestXMLFileHandlerCollectionInterface
    {
        return new ilManifestXMLFileHandlerCollection(
            new ilImportStatusFactory()
        );
    }
}
