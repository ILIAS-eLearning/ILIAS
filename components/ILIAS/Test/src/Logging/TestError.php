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
use ILIAS\StaticURL\Services as StaticURLServices;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Table\DataRow;

class TestError implements TestUserInteraction
{
    public const IDENTIFIER = 'te';

    private int $unique_id;

    public function __construct(
        private readonly int $test_ref_id,
        private readonly ?int $question_id,
        private readonly ?int $admin_id,
        private readonly ?int $pax_id,
        private readonly TestErrorTypes $interaction_type,
        private readonly int $modification_timestamp,
        private readonly string $error_message
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
        GeneralQuestionPropertiesRepository $properties_repository,
        UIFactory $ui_factory,
        DataRowBuilder $row_builder,
        array $environment
    ): DataRow {
        $test_obj_id = \ilObject::_lookupObjId($this->test_ref_id);

        $admin = '';
        $pax = '';
        $question = '';

        if ($this->admin_id !== null) {
            $admin = \ilUserUtil::getNamePresentation(
                $this->admin_id,
                false,
                false,
                false,
                true
            );
        }

        if ($this->pax_id !== null) {
            $pax = \ilUserUtil::getNamePresentation(
                $this->pax_id,
                false,
                false,
                false,
                true
            );
        }

        if ($this->question_id !== null) {
            $question = $properties_repository->getForQuestionId($this->question_id)->getTitle();
        }

        return $row_builder->buildDataRow(
            $this->getUniqueIdentifier(),
            [
                'date_and_time' => new \DateTimeImmutable($this->modification_timestamp, $environment['timezone']),
                'corresponding_test' => $ui_factory->link()->standard(
                    \ilObject::_lookupTitle($test_obj_id),
                    $static_url->builder()->build('tst', $this->test_ref_id)
                ),
                'author' => $admin,
                'participant' => $pax,
                'ip' => '',
                'question' => $question,
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
            'ref_id' => [\ilDBConstants::T_INTEGER , $this->test_ref_id],
            'qst_id' => [\ilDBConstants::T_INTEGER , $this->question_id],
            'admin_id' => [\ilDBConstants::T_INTEGER , $this->admin_id],
            'pax_id' => [\ilDBConstants::T_INTEGER , $this->pax_id],
            'interaction_type' => [\ilDBConstants::T_TEXT , $this->interaction_type->value],
            'modification_ts' => [\ilDBConstants::T_INTEGER , $this->modification_timestamp],
            'error_message' => [\ilDBConstants::T_TEXT , $this->error_message]
        ];
    }
}
