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

class TestAdministrationInteraction implements TestUserInteraction
{
    use CSVExportTrait;
    use ColumnsHelperFunctionsTrait;

    public const IDENTIFIER = 'tai';

    private int $id;

    /**
    * @param array<string label_lang_var => mixed value> $additional_data
    */
    public function __construct(
        private readonly int $test_ref_id,
        private readonly int $admin_id,
        private readonly TestAdministrationInteractionTypes $interaction_type,
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
        DataRowBuilder $row_builder,
        array $environment
    ): DataRow {
        return $row_builder->buildDataRow(
            $this->getUniqueIdentifier(),
            [
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
            ]
        )->withDisabledAction(
            LogTable::ACTION_ID_SHOW_ADDITIONAL_INFO,
            $this->additional_data === []
        );
    }

    public function getParsedAdditionalInformation(
        AdditionalInformationGenerator $additional_info,
        UIFactory $ui_factory,
        array $environment
    ): DescriptiveListing {
        return $additional_info->parseForTable($this->additional_data, $environment);
    }

    public function getLogEntryAsCsvRow(
        \ilLanguage $lng,
        GeneralQuestionPropertiesRepository $properties_repository,
        AdditionalInformationGenerator $additional_info,
        array $environment
    ): string {
        return implode(
            ';',
            $this->processCSVRow(
                [
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
                    '',
                    $lng->txt(self::LANG_VAR_PREFIX . self::IDENTIFIER),
                    $lng->txt(self::LANG_VAR_PREFIX . $this->interaction_type->value),
                    $additional_info->parseForRowArray($this->additional_data, $environment)
                ]
            )
        ) . "\n";
    }

    public function toStorage(): array
    {
        return [
            'ref_id' => [\ilDBConstants::T_INTEGER , $this->test_ref_id],
            'admin_id' => [\ilDBConstants::T_INTEGER , $this->admin_id],
            'interaction_type' => [\ilDBConstants::T_TEXT , $this->interaction_type->value],
            'modification_ts' => [\ilDBConstants::T_INTEGER , $this->modification_timestamp],
            'additional_data' => [\ilDBConstants::T_CLOB , json_encode($this->additional_data)]
        ];
    }
}
