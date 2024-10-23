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

use ILIAS\Data\Factory as ilDataFactory;
use ILIAS\Export\ImportHandler\File\Factory as ilImportHandlerFileFactory;
use ILIAS\Export\ImportHandler\I\FactoryInterface as ilImportHandlerFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\FactoryInterface as ilImportHandlerFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\FactoryInterface as ilImportHandlerParserFactoryInterface;
use ILIAS\Export\ImportHandler\I\Path\FactoryInterface as ilImportHandlerPathFactoryInterface;
use ILIAS\Export\ImportHandler\I\Schema\FactoryInterface as ilImportHandlerSchemaFactoryInterface;
use ILIAS\Export\ImportHandler\I\Validation\FactoryInterface as ilImportHandlerValidationFactoryInterface;
use ILIAS\Export\ImportHandler\Parser\Factory as ilImportHandlerParserFactory;
use ILIAS\Export\ImportHandler\Path\Factory as ilImportHandlerPathFactory;
use ILIAS\Export\ImportHandler\Schema\Factory as ilImportHandlerSchemaFactory;
use ILIAS\Export\ImportHandler\Validation\Factory as ilImportHandlerValidationFactory;
use ILIAS\Export\ImportStatus\ilFactory as ilImportStatusFactory;
use ilLanguage;
use ilLogger;

class Factory implements ilImportHandlerFactoryInterface
{
    protected ilLogger $logger;
    protected ilLanguage $lng;
    protected ilImportStatusFactory $import_status_factory;
    protected ilDataFactory $data_factory;

    public function __construct()
    {
        global $DIC;
        $this->logger = $DIC->logger()->root();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("exp");
        $this->import_status_factory = new ilImportStatusFactory();
        $this->data_factory = new ilDataFactory();
    }

    public function parser(): ilImportHandlerParserFactoryInterface
    {
        return new ilImportHandlerParserFactory(
            $this,
            $this->logger
        );
    }

    public function file(): ilImportHandlerFileFactoryInterface
    {
        return new ilImportHandlerFileFactory(
            $this,
            $this->import_status_factory,
            $this->logger,
            $this->lng,
            $this->data_factory
        );
    }

    public function schema(): ilImportHandlerSchemaFactoryInterface
    {
        return new ilImportHandlerSchemaFactory(
            $this,
            $this->data_factory,
            $this->logger
        );
    }

    public function path(): ilImportHandlerPathFactoryInterface
    {
        return new ilImportHandlerPathFactory(
            $this->logger
        );
    }

    public function validation(): ilImportHandlerValidationFactoryInterface
    {
        return new ilImportHandlerValidationFactory(
            $this->import_status_factory,
            $this,
            $this->logger
        );
    }
}
