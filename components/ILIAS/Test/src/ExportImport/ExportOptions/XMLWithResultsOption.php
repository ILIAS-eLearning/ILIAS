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

namespace ILIAS\Test\ExportImport\ExportOptions;

use ilExportGUI;
use ILIAS\Export\ExportHandler\Consumer\ExportOption\BasicLegacyHandler as BasicLegacyExportOption;
use ILIAS\Export\ExportHandler\Factory as ExportHandlerLocator;
use ILIAS\Export\ExportHandler\I\Consumer\Context\HandlerInterface as ConsumerContext;
use ILIAS\DI\Container;
use ILIAS\Language\Language;
use ILIAS\Test\ExportImport\Types;
use ILIAS\Test\TestDIC;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Bridge\StateHolder;

class XMLWithResultsOption extends BasicLegacyExportOption
{
    private Language $lng;
    private StateHolder $state_holder;

    public function init(
        Container $DIC
    ): void {
        parent::init($DIC);

        $this->lng = $DIC->language();
        $this->state_holder = TestDIC::dic()['exportimport.state_holder'];
    }

    public function getExportType(): string
    {
        return 'ZIP Results';
    }

    public function getExportOptionId(): string
    {
        return Types::XML_WITH_RESULTS->value;
    }

    public function getSupportedRepositoryObjectTypes(): array
    {
        return ['tst'];
    }

    public function getLabel(): string
    {
        $this->lng->loadLanguageModule('exp');
        $this->lng->loadLanguageModule('assessment');

        return $this->lng->txt('exp_format_dropdown-xml') . ' (' . $this->lng->txt('ass_create_export_file_with_results') . ')';
    }

    public function onExportOptionSelected(ConsumerContext $context): void
    {
        $handler = new ExportHandlerLocator();
        $manager = $handler->manager()->handler();

        $export_info = $manager->getExportInfoWithObject(
            $context->exportObject(),
            time(),
            $handler->consumer()->exportConfig()->allExportConfigs()
        );

        // Prepare export state to bridge between export option and the xml exporter
        $this->state_holder->create(
            $export_info->getTarget(),
            $handler->consumer()->exportConfig()->allExportConfigs(),
            Types::XML_WITH_RESULTS->value
        );

        // Delegate the export to the manager which will call ilTestExporter
        $manager->createExport(
            1,
            $export_info,
            ''
        );

        $this->ctrl->redirectByClass(ilExportGUI::class, ilExportGUI::CMD_LIST_EXPORT_FILES);
    }
}
