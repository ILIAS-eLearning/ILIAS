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

class TestAdministrationInteraction implements TestUserInteraction
{
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
        return self::TEXTUAL_REPRESENATION . '_' . $this->unique_id;
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
        \ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository $properties_repository,
        UIFactory $ui_factory,
        DataRowBuilder $row_builder,
        array $environment
    ): DataRow {
        $test_obj_id = \ilObject::_lookupObjId($this->test_ref_id);

        return $row_builder->buildDataRow(
            $this->getUniqueIdentifier(),
            [
                'date_and_time' => new \DateTimeImmutable($this->modification_timestamp, $environment['timezone']),
                'corresponding_test' => $ui_factory->link()->standard(
                    \ilObject::_lookupTitle($test_obj_id),
                    $static_url->builder()->build('tst', $this->test_ref_id)
                ),
                'author' => \ilUserUtil::getNamePresentation(
                    $this->admin_id,
                    false,
                    false,
                    false,
                    true
                ),
                'participant' => '',
                'ip' => '',
                'question' => '',
                'log_entry_type' => $lng->txt('logging_' . self::IDENTIFIER),
                'interaction_type' => $lng->txt('logging_' . $this->interaction_type->value)
            ]
        );
    }

    public function getLogEntryAsCsvRow(): string
    {

    }

    public function toStorage(): array
    {
        return [
            'ref_id' => [\ilDBConstants::T_INTEGER , $this->getTestRefId()],
            'admin_id' => [\ilDBConstants::T_INTEGER , $this->getAdministratorId()],
            'interaction_type' => [\ilDBConstants::T_TEXT , $this->getInteractionType()->value],
            'modification_ts' => [\ilDBConstants::T_INTEGER , $this->getModificationTimestamp()],
            'additional_data' => [\ilDBConstants::T_CLOB , serialize($this->additional_data)]
        ];
    }
}
