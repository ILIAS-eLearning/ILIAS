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

namespace ILIAS\Export\ImportHandler\File\XML\Node\Info;

use DOMNode;
use ilLogger;
use ILIAS\Export\ImportHandler\File\XML\Node\Info\Attribute\ilFactory as ilXMLFileNodeInfoAttributeFactory;
use ILIAS\Export\ImportHandler\File\XML\Node\Info\ilCollection as ilFileNodeInfoCollection;
use ILIAS\Export\ImportHandler\File\XML\Node\Info\ilTree as ilXMLFileNodeInfoTree;
use ILIAS\Export\ImportHandler\I\File\XML\Node\Info\Attribute\ilFactoryInterface as ilXMLFileNodeInfoAttributeFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Node\Info\DOM\ilFactoryInterface as ilXMLFileDOMNodeInfoFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Node\Info\ilCollectionInterface as ilXMLFileNodeInfoCollectionInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Node\Info\ilFactoryInterface as ilXMLFileNodeInfoFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Node\Info\ilTreeInterface as ilXMLFileNodeInfoTreeInterface;
use ILIAS\Export\ImportHandler\Parser\ilFactory as ilParserFactory;
use ILIAS\Export\ImportHandler\File\XML\Node\Info\DOM\ilFactory as ilXMLFileDOMNodeInfoFactory;

class ilFactory implements ilXMLFileNodeInfoFactoryInterface
{
    protected ilLogger $logger;

    public function __construct(
        ilLogger $logger
    ) {
        $this->logger = $logger;
    }

    public function collection(): ilXMLFileNodeInfoCollectionInterface
    {
        return new ilFileNodeInfoCollection();
    }

    public function tree(): ilXMLFileNodeInfoTreeInterface
    {
        return new ilXMLFileNodeInfoTree(
            new ilFactory($this->logger),
            new ilParserFactory($this->logger),
            $this->logger
        );
    }

    public function attribute(): ilXMLFileNodeInfoAttributeFactoryInterface
    {
        return new ilXMLFileNodeInfoAttributeFactory($this->logger);
    }

    public function DOM(): ilXMLFileDOMNodeInfoFactoryInterface
    {
        return new ilXMLFileDOMNodeInfoFactory($this);
    }
}
