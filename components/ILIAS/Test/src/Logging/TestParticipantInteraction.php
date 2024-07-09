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

namespace ILIAS\Test\Logging;

use ILIAS\Test\Export\CSVExportTrait;

use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Listing\Descriptive as DescriptiveListing;
use ILIAS\StaticURL\Services as StaticURLServices;
use ILIAS\Data\ReferenceId;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Table\DataRow;

class TestParticipantInteraction implements TestUserInteraction
{
    use CSVExportTrait;

    public const IDENTIFIER = 'pi';

    private int $id;

    /**
    * @param array<string label_lang_var => mixed value> $additional_data
    */
    public function __construct(
        private readonly int $test_ref_id,
        private readonly ?int $question_id,
        private readonly int $pax_id,
        private readonly string $source_ip,
        private readonly TestParticipantInteractionTypes $interaction_type,
        private readonly int $modification_timestamp,
        private readonly array $additional_data
    ) {

    }

    public function getUniqueIdentifier(): ?string
    {
        return self::IDENTIFIER . '_' . $this->id;
    }

    public function withId(int $id): self
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function getLogEntryAsDataTableRow(
        \ilLanguage $lng,
        StaticURLServices $static_url,
        GeneralQuestionPropertiesRepository $properties_repository,
        UIFactory $ui_factory,
        UIRenderer $ui_renderer,
        DataRowBuilder $row_builder,
        array $environment
    ): DataRow {
        $test_obj_id = \ilObject::_lookupObjId($this->test_ref_id);
        $question = '';
        if ($this->question_id !== null) {
            $question = $ui_renderer->render($ui_factory->button()->shy(
                $properties_repository->getForQuestionId($this->question_id)->getTitle(),
                $static_url->builder()->build(
                    'tst',
                    new ReferenceId($this->test_ref_id),
                    ['qst', $this->question_id]
                )->__toString()
            ));
        }

        return $row_builder->buildDataRow(
            $this->getUniqueIdentifier(),
            [
                'date_and_time' => new \DateTimeImmutable(
                    "@{$this->modification_timestamp}",
                    $environment['timezone']
                ),
                'corresponding_test' => $ui_factory->link()->standard(
                    \ilObject::_lookupTitle($test_obj_id),
                    $static_url->builder()->build('tst', new ReferenceId($this->test_ref_id))->__toString()
                ),
                'admin' => '',
                'participant' => \ilUserUtil::getNamePresentation(
                    $this->pax_id,
                    false,
                    false,
                    '',
                    true
                ),
                'ip' => $this->source_ip,
                'question' => $question,
                'log_entry_type' => $lng->txt(self::LANG_VAR_PREFIX . self::IDENTIFIER),
                'interaction_type' => $lng->txt(self::LANG_VAR_PREFIX . $this->interaction_type->value)
            ]
        )->withDisabledAction(
            LogTable::ACTION_ID_SHOW_ADDITIONAL_INFO,
            $this->additional_data === []
        );
    }

    public function getParsedAdditionalInformation(
        AdditionalInformationGenerator $additional_info,
        UIFactory $ui_factory
    ): DescriptiveListing {
        return $additional_info->parseForTable($this->additional_data);
    }

    public function getLogEntryAsCsvRow(
        \ilLanguage $lng,
        GeneralQuestionPropertiesRepository $properties_repository,
        AdditionalInformationGenerator $additional_info,
        array $environment
    ): string {
        $test_obj_id = \ilObject::_lookupObjId($this->test_ref_id);
        $question = '';
        if ($this->question_id !== null) {
            $question = $properties_repository->getForQuestionId($this->question_id)->getTitle();
        }
        return implode(
            ';',
            $this->processCSVRow(
                [
                    (new \DateTimeImmutable(
                        "@{$this->modification_timestamp}",
                        $environment['timezone']
                    ))->format($environment['date_format']),
                    \ilObject::_lookupTitle($test_obj_id),
                    '',
                    \ilUserUtil::getNamePresentation(
                        $this->pax_id,
                        false,
                        false,
                        '',
                        true
                    ),
                    $this->source_ip,
                    $question,
                    $lng->txt(self::LANG_VAR_PREFIX . self::IDENTIFIER),
                    $lng->txt(self::LANG_VAR_PREFIX . $this->interaction_type->value),
                    $additional_info->parseForCSV($this->additional_data)
                ]
            )
        ) . "\n";
    }

    public function toStorage(): array
    {
        return [
            'ref_id' => [\ilDBConstants::T_INTEGER , $this->test_ref_id],
            'qst_id' => [\ilDBConstants::T_INTEGER , $this->question_id],
            'pax_id' => [\ilDBConstants::T_INTEGER , $this->pax_id],
            'source_ip' => [\ilDBConstants::T_TEXT , $this->source_ip],
            'interaction_type' => [\ilDBConstants::T_TEXT , $this->interaction_type->value],
            'modification_ts' => [\ilDBConstants::T_INTEGER , $this->modification_timestamp],
            'additional_data' => [\ilDBConstants::T_CLOB , json_encode($this->additional_data)]
        ];
    }
}
