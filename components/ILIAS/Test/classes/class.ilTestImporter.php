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

use ILIAS\Data\ReferenceId;
use ILIAS\Test\ExportImport\Import\TestImporter;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Importing\ImportSessionRepository;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Serializing\XMLMemoryDeserializer;
use ILIAS\TestQuestionPool\ExportImport\Import\DetectLegacyImportStage;
use ILIAS\Test\TestDIC;

class ilTestImporter extends ilXmlImporter
{
    protected readonly ImportSessionRepository $session;
    protected readonly TestImporter $importer;
    protected readonly ilTestLegacyImporter $legacy_importer;

    public function __construct()
    {
        parent::__construct();
        $this->legacy_importer = new ilTestLegacyImporter();

        $local_dic = TestDIC::dic();
        $this->session = $local_dic['exportimport.session'];
        $this->importer = $local_dic['exportimport.importer'];
    }

    public function init(): void
    {
        /** @var ilCOPageImportConfig $co_config */
        $co_config = $this->imp->getConfig('components/ILIAS/COPage');
        $co_config->setUpdateIfExists(true);

        $this->legacy_importer->setImport($this->getImport());
        $this->legacy_importer->setImportDirectory($this->getImportDirectory());
        $this->legacy_importer->init();
    }

    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ): void {
        // Check if forward to legacy importer is needed
        $context = $this->session->getContext();
        if (DetectLegacyImportStage::isLegacyImport($context)) {
            $this->legacy_importer->setInstallId($this->getInstallId());
            $this->legacy_importer->setInstallUrl($this->getInstallUrl());
            $this->legacy_importer->setSchemaVersion($this->getSchemaVersion());
            $this->legacy_importer->setSkipEntities($this->getSkipEntities());
            $this->legacy_importer->importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping);
            return;
        }

        $result = $this->importer->import(
            new XMLMemoryDeserializer()->open($a_xml),
            $a_mapping,
            new ReferenceId($a_mapping->getTargetId()),
            $context,
        );
        $this->session->setContext($result);
    }

    public function finalProcessing(ilImportMapping $a_mapping): void
    {
        // Check if forward to legacy importer is needed
        $context = $this->session->getContext();
        if (DetectLegacyImportStage::isLegacyImport($context)) {
            $this->legacy_importer->finalProcessing($a_mapping);
            return;
        }

        $this->importer->finalize($a_mapping);
    }
}
