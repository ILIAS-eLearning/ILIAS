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

namespace ILIAS\Test\Settings\Templates;

use ILIAS\FileDelivery\Services as FileDeliveryServices;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Test\ExportImport\Exporter;
use ILIAS\Test\Scoring\Marks\MarksRepository;
use ILIAS\Test\Settings\MainSettings\MainSettingsRepository;
use ILIAS\Test\Settings\ScoreReporting\ScoreSettingsRepository;
use ILIAS\Test\Settings\SettingsFactory;

class PersonalSettingsExporter implements Exporter
{
    private int $template_id;
    private ?PersonalSettingsTemplate $template = null;

    public function __construct(
        private readonly FileDeliveryServices $file_delivery,
        private readonly PersonalSettingsRepository $repository,
        private readonly MainSettingsRepository $main_settings_repository,
        private readonly ScoreSettingsRepository $score_settings_repository,
        private readonly MarksRepository $marks_repository
    ) {
    }

    public function setTemplateId(int $template_id): void
    {
        $this->template_id = $template_id;
        $this->template = null;
    }

    private function getTemplate(): PersonalSettingsTemplate
    {
        return $this->template ??= $this->repository->getById($this->template_id);
    }

    public function deliver(): void
    {
        if (($xml_content = $this->write()) === null) {
            return;
        }

        $this->file_delivery->delivery()->attached(
            Streams::ofString($xml_content),
            "{$this->escapeName($this->getTemplate()->getName())}.xml",
            'text/xml',
        );
    }

    public function write(): ?string
    {
        if (($template = $this->getTemplate()) === null) {
            return null;
        }

        $main_settings = $this->main_settings_repository->getById($template->getSettingsId());
        $score_settings = $this->score_settings_repository->getById($template->getSettingsId());
        $mark_schema = $this->marks_repository->getMarkSchemaBySteps(
            $this->repository->lookupMarkSteps($template->getId())
        );


        $xml_writer = new \XMLWriter();
        $xml_writer->openMemory();

        $xml_writer->setIndent(true);
        $xml_writer->startDocument('1.0', 'UTF-8');
        $xml_writer->writeDTD('PTST', null, 'http://www.ilias.uni-koeln.de/download/dtd/ilias_co.dtd');
        $xml_writer->writeComment("Export of Personal Test Settings Template for installation " . IL_INST_ID);

        $xml_writer->startElement('template');
        $xml_writer->writeAttribute('ilias-version', ILIAS_VERSION_NUMERIC);
        foreach ($template->toExport() as $name => $value) {
            $xml_writer->writeAttribute(str_replace('_', '-', $name), (string) $value);
        }

        $xml_writer->startElement('main-settings');
        $this->writeRecursive($xml_writer, $main_settings->toExport(), ['settings-group', 'settings-entry']);
        $xml_writer->endElement();

        $xml_writer->startElement('score-settings');
        $this->writeRecursive($xml_writer, $score_settings->toExport(), ['settings-group', 'settings-entry']);
        $xml_writer->endElement();

        $xml_writer->startElement('mark-schema');
        $this->writeRecursive($xml_writer, $mark_schema->toExport(), ['mark-steps', 'mark']);
        $xml_writer->endElement();

        $xml_writer->endElement();
        $xml_writer->endDocument();

        return $xml_writer->outputMemory(true);
    }

    /**
     * Writes the provided array as a nested tree of elements to the XML writer.
     *
     * @param array<array-key, mixed> $values
     * @param string[] $elements Name of the elements per tree level, 'entry' by default
     */
    private function writeRecursive(\XMLWriter $xml_writer, array $values, array $elements = []): void
    {
        $element = array_shift($elements) ?? 'entry';

        foreach ($values as $name => $value) {
            $type = gettype($value);
            $is_nested = is_array($value);

            $xml_writer->startElement($element);
            if (!$is_nested) {
                $xml_writer->writeAttribute('type', $type);
            }
            if (is_string($name)) {
                $xml_writer->writeAttribute('name', $name);
            }

            if (!$is_nested) {
                $value = match ($type) {
                    'NULL' => 'NULL',
                    'boolean' => $value ? 'true' : 'false',
                    default => htmlspecialchars((string) $value),
                };

                $xml_writer->writeRaw($value);
            } else {
                $this->writeRecursive($xml_writer, $value, $elements);
            }

            $xml_writer->endElement();
        }
    }

    private function escapeName(string $name): string
    {
        // Replace all special characters except "_" from the string for safe filename usage
        return preg_replace('/[\W_]/', '-', $name);
    }
}
