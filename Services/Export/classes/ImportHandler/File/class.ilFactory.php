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

namespace ImportHandler\File;

use ilLogger;
use ImportHandler\File\Validation\ilFactory as ilFileValidationFactory;
use ImportHandler\File\XML\ilFactory as ilXMLFileFactory;
use ImportHandler\File\XML\Manifest\ilFactory as ilManifestFileFactory;
use ImportHandler\File\XSD\ilFactory as ilXSDFileFactory;
use ImportHandler\I\File\ilFactoryInterface as ilFileFactory;
use ImportHandler\I\File\ilFactoryInterface as ilFileFactoryInterface;
use ImportHandler\I\File\Path\ilFactoryInterface as ilFilePathFactoryInterface;
use ImportHandler\I\File\Validation\ilFactoryInterface as ilFileValidationFactoryInterface;
use ImportHandler\I\File\XML\ilFactoryInterface as ilXMLFileFactoryInterface;
use ImportHandler\I\File\XML\Manifest\ilFactoryInterface as ilManifestFileFactoryInterface;
use ImportHandler\I\File\XSD\ilFactoryInterface as ilXSDFileFactoryInterface;
use ImportHandler\I\Parser\ilFactoryInterface as ilParserFactoryInterface;
use ImportHandler\File\Path\ilFactory as ilFilePathFactory;

class ilFactory implements ilFileFactory
{
    protected ilLogger $logger;
    protected ilParserFactoryInterface $parser;

    public function __construct(
        ilParserFactoryInterface $parser,
        ilLogger $logger
    ) {
        $this->logger = $logger;
        $this->parser = $parser;
    }

    public function xml(): ilXMLFileFactoryInterface
    {
        return new ilXMLFileFactory(
            $this,
            $this->parser,
            $this->logger
        );
    }

    public function xsd(): ilXSDFileFactoryInterface
    {
        return new ilXSDFileFactory();
    }

    public function validation(): ilFileValidationFactoryInterface
    {
        return new ilFileValidationFactory(
            $this->logger,
            $this->parser->handler(),
            new ilFilePathFactory($this->logger)
        );
    }

    public function path(): ilFilePathFactoryInterface
    {
        return new ilFilePathFactory($this->logger);
    }
}
