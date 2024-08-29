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

use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Listing\Descriptive as DescriptiveListing;
use ILIAS\StaticURL\Services as StaticURLServices;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Table\DataRow;

class TestQuestionAdministrationInteraction implements TestUserInteraction
{
    use ColumnsHelperFunctionsTrait;

    public const IDENTIFIER = 'qai';

    private int $id;

    /**
    * @param array<string label_lang_var => mixed value> $additional_data
    */
    public function __construct(
        private int $test_ref_id,
        private int $question_id,
        private int $admin_id,
        private TestQuestionAdministrationInteractionTypes $interaction_type,
        private int $modification_timestamp,
        private array $additional_data
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
        DataRowBuilder $row_builder,
        array $environment
    ): DataRow {
        $values = [
            'date_and_time' => \DateTimeImmutable::createFromFormat('U', (string) $this->modification_timestamp)
                ->setTimezone($environment['timezone']),
            'corresponding_test' => $this->buildTestTitleColumnContent(
                $lng,
                $static_url,
                $ui_factory->link(),
                $this->test_ref_id
            ),
            'admin' => \ilUserUtil::getNamePresentation(
                $this->admin_id,
                false,
                false,
                '',
                true
            ),
            'log_entry_type' => $lng->txt(self::LANG_VAR_PREFIX . self::IDENTIFIER),
            'interaction_type' => $lng->txt(self::LANG_VAR_PREFIX . $this->interaction_type->value)
        ];

        if ($this->question_id !== null) {
            $values['question'] = $this->buildQuestionTitleColumnContent(
                $properties_repository,
                $lng,
                $static_url,
                $ui_factory->link(),
                $this->question_id,
                $this->test_ref_id
            );
        }
        return $row_builder->buildDataRow(
            $this->getUniqueIdentifier(),
            $values
        )->withDisabledAction(
            LogTable::ACTION_ID_SHOW_ADDITIONAL_INFO,
            $this->additional_data === []
        );
    }

    public function getLogEntryAsExportRow(
        \ilLanguage $lng,
        GeneralQuestionPropertiesRepository $properties_repository,
        AdditionalInformationGenerator $additional_info,
        array $environment
    ): array {
        return [
            \DateTimeImmutable::createFromFormat('U', (string) $this->modification_timestamp)
                ->setTimezone($environment['timezone'])
                ->format($environment['date_format']),
            $this->buildTestTitleCSVContent($lng, $this->test_ref_id),
            \ilUserUtil::getNamePresentation(
                $this->admin_id,
                false,
                false,
                '',
                true
            ),
            '',
            '',
            $this->buildQuestionTitleCSVContent(
                $properties_repository,
                $lng,
                $this->question_id
            ),
            $lng->txt(self::LANG_VAR_PREFIX . self::IDENTIFIER),
            $lng->txt(self::LANG_VAR_PREFIX . $this->interaction_type->value),
            $additional_info->parseForExport($this->additional_data, $environment)
        ];
    }

    public function getParsedAdditionalInformation(
        AdditionalInformationGenerator $additional_info,
        UIFactory $ui_factory,
        array $environment
    ): DescriptiveListing {
        return $additional_info->parseForTable($this->additional_data, $environment);
    }

    public function toStorage(): array
    {
        return [
            'ref_id' => [\ilDBConstants::T_INTEGER , $this->test_ref_id],
            'qst_id' => [\ilDBConstants::T_INTEGER , $this->question_id],
            'admin_id' => [\ilDBConstants::T_INTEGER , $this->admin_id],
            'interaction_type' => [\ilDBConstants::T_TEXT , $this->interaction_type->value],
            'modification_ts' => [\ilDBConstants::T_INTEGER , $this->modification_timestamp],
            'additional_data' => [\ilDBConstants::T_CLOB , json_encode($this->additional_data)]
        ];
    }
}
