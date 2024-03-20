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

namespace ILIAS\Export\ImportHandler;

use ilLanguage;
use ilLogger;
use ILIAS\Export\ImportHandler\File\ilFactory as ilFileFactory;
use ILIAS\Export\ImportHandler\I\File\ilFactoryInterface as ilFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\ilFactoryInterface as ilImportHandlerFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\ilFactoryInterface as ilParserFactoryInterface;
use ILIAS\Export\ImportHandler\Parser\ilFactory as ilParserFactory;
use ILIAS\Export\Schema\ilXmlSchemaFactory;

class ilFactory implements ilImportHandlerFactoryInterface
{
    protected ilLogger $logger;
    protected ilLanguage $lng;
    protected ilXmlSchemaFactory $schema_factory;


    public function __construct()
    {
        global $DIC;
        $this->logger = $DIC->logger()->root();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("exp");
        $this->schema_factory = new ilXmlSchemaFactory();
    }

    public function parser(): ilParserFactoryInterface
    {
        return new ilParserFactory($this->logger);
    }

    public function file(): ilFileFactoryInterface
    {
        return new ilFileFactory(
            $this->logger,
            $this->lng,
            $this->schema_factory
        );
    }
}
