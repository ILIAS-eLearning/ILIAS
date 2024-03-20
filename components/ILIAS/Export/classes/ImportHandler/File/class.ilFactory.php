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

use ilLanguage;
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
use ILIAS\Export\Schema\ilXmlSchemaFactory;

class ilFactory implements ilFileFactory
{
    protected ilLogger $logger;
    protected ilLanguage $lng;
    protected ilXmlSchemaFactory $schema_factory;

    public function __construct(
        ilLogger $logger,
        ilLanguage $lng,
        ilXmlSchemaFactory $schema_factory
    ) {
        $this->logger = $logger;
        $this->lng = $lng;
        $this->schema_factory = $schema_factory;
    }

    public function xml(): ilXMLFileFactoryInterface
    {
        return new ilXMLFileFactory(
            $this->logger,
            $this->lng,
            $this->schema_factory
        );
    }

    public function xsd(): ilXSDFileFactoryInterface
    {
        return new ilXSDFileFactory();
    }

    public function validation(): ilFileValidationFactoryInterface
    {
        return new ilFileValidationFactory($this->logger);
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
