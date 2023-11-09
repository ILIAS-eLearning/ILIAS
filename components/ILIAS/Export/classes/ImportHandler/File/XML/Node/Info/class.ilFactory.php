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

namespace ImportHandler\File\XML\Node\Info;

use ilLogger;
use ImportHandler\I\File\XML\Node\Info\Attribute\ilFactoryInterface as ilXMLFileNodeInfoAttributeFactoryInterface;
use ImportHandler\I\File\XML\Node\Info\ilFactoryInterface as ilXMLFileNodeInfoFactoryInterface;
use ImportHandler\I\File\XML\Node\Info\ilCollectionInterface as ilXMLFileNodeInfoCollectionInterface;
use ImportHandler\I\File\XML\Node\Info\ilHandlerInterface as ilXMLFileNodeInfoHandlerInterface;
use ImportHandler\File\XML\Node\Info\ilHandler as ilXMLFileNodeInfo;
use ImportHandler\File\XML\Node\Info\ilCollection as ilFileNodeInfoCollection;
use ImportHandler\I\File\XML\Node\Info\ilTreeInterface as ilXMLFileNodeInfoTreeInterface;
use ImportHandler\File\XML\Node\Info\ilTree as ilXMLFileNodeInfoTree;
use ImportHandler\Parser\ilFactory as ilParserFactory;
use ImportHandler\File\XML\Node\Info\Attribute\ilFactory as ilXMLFileNodeInfoAttributeFactory;

class ilFactory implements ilXMLFileNodeInfoFactoryInterface
{
    protected ilLogger $logger;

    public function __construct(
        ilLogger $logger
    ) {
        $this->logger = $logger;
    }

    public function handler(): ilXMLFileNodeInfoHandlerInterface
    {
        return new ilXMLFileNodeInfo(
            new ilFactory($this->logger)
        );
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
}
