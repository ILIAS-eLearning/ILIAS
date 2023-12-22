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

namespace ILIAS\Export\ImportHandler\File;

use ilLogger;
use ILIAS\Export\ImportHandler\File\Validation\ilFactory as ilFileValidationFactory;
use ILIAS\Export\ImportHandler\File\XML\ilFactory as ilXMLFileFactory;
use ILIAS\Export\ImportHandler\File\XML\Manifest\ilFactory as ilManifestFileFactory;
use ILIAS\Export\ImportHandler\File\XSD\ilFactory as ilXSDFileFactory;
use ILIAS\Export\ImportHandler\I\File\ilFactoryInterface as ilFileFactory;
use ILIAS\Export\ImportHandler\I\File\ilFactoryInterface as ilFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\Namespace\ilFactoryInterface as ilFileNamespaceFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\Path\ilFactoryInterface as ilFilePathFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\Validation\ilFactoryInterface as ilFileValidationFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\ilFactoryInterface as ilXMLFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Manifest\ilFactoryInterface as ilManifestFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XSD\ilFactoryInterface as ilXSDFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\ilFactoryInterface as ilParserFactoryInterface;
use ILIAS\Export\ImportHandler\File\Path\ilFactory as ilFilePathFactory;
use ILIAS\Export\ImportHandler\File\Namespace\ilFactory as ilFileNamespaceFactory;

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
            $this->parser,
            new ilFilePathFactory($this->logger)
        );
    }

    public function path(): ilFilePathFactoryInterface
    {
        return new ilFilePathFactory($this->logger);
    }

    public function namespace(): ilFileNamespaceFactoryInterface
    {
        return new ilFileNamespaceFactory();
    }
}
