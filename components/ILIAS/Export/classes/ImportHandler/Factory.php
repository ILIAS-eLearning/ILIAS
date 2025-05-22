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

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Export\ImportHandler\File\Factory as FileFactory;
use ILIAS\Export\ImportHandler\I\FactoryInterface as ImportHandlerFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\FactoryInterface as FileFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\FactoryInterface as ParserFactoryInterface;
use ILIAS\Export\ImportHandler\I\Path\FactoryInterface as PathFactoryInterface;
use ILIAS\Export\ImportHandler\I\Schema\FactoryInterface as SchemaFactoryInterface;
use ILIAS\Export\ImportHandler\I\SchemaFolder\FactoryInterface as SchemaFolderFactoryInterface;
use ILIAS\Export\ImportHandler\I\Validation\FactoryInterface as ValidationFactoryInterface;
use ILIAS\Export\ImportHandler\Parser\Factory as ParserFactory;
use ILIAS\Export\ImportHandler\Path\Factory as PathFactory;
use ILIAS\Export\ImportHandler\Schema\Factory as SchemaFactory;
use ILIAS\Export\ImportHandler\SchemaFolder\Factory as SchemaFolderFactory;
use ILIAS\Export\ImportHandler\I\SchemaFolder\HandlerInterface as SchemaFolderInterface;
use ILIAS\Export\ImportHandler\Validation\Factory as ValidationFactory;
use ILIAS\Export\ImportStatus\ilFactory as ImportStatusFactory;
use ilLanguage;
use ilLogger;

class Factory implements ImportHandlerFactoryInterface
{
    protected ilLogger $logger;
    protected ilLanguage $lng;
    protected ImportStatusFactory $import_status_factory;
    protected DataFactory $data_factory;
    protected SchemaFolderInterface $schema_folder;

    public function __construct()
    {
        global $DIC;
        $this->logger = $DIC->logger()->root();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("exp");
        $this->import_status_factory = new ImportStatusFactory();
        $this->data_factory = new DataFactory();
        $this->schema_folder = $this->schemaFolder()->handler();
    }

    public function parser(): ParserFactoryInterface
    {
        return new ParserFactory(
            $this,
            $this->logger
        );
    }

    public function file(): FileFactoryInterface
    {
        return new FileFactory(
            $this,
            $this->import_status_factory,
            $this->logger,
            $this->lng,
            $this->data_factory,
            $this->schema_folder
        );
    }

    public function schema(): SchemaFactoryInterface
    {
        return new SchemaFactory(
            $this->schema_folder,
            $this,
            $this->data_factory,
            $this->logger
        );
    }

    public function schemaFolder(): SchemaFolderFactoryInterface
    {
        return new SchemaFolderFactory(
            $this,
            $this->logger
        );
    }

    public function path(): PathFactoryInterface
    {
        return new PathFactory(
            $this->logger
        );
    }

    public function validation(): ValidationFactoryInterface
    {
        return new ValidationFactory(
            $this->import_status_factory,
            $this,
            $this->logger
        );
    }
}
