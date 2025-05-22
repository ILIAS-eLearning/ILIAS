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

use ILIAS\Test\Utilities\TitleColumnsBuilder;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Legacy\Content;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Table\DataRow;

class TestError implements TestUserInteraction
{
    public const IDENTIFIER = 'te';

    private int $id;

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
        TitleColumnsBuilder $title_builder,
        DataRowBuilder $row_builder,
        array $environment
    ): DataRow {
        $admin = $this->getUserForPresentation($this->admin_id);
        $pax = $this->getUserForPresentation($this->pax_id);

        $values = [
            'date_and_time' => \DateTimeImmutable::createFromFormat('U', (string) $this->modification_timestamp)
                ->setTimezone($environment['timezone']),
            'corresponding_test' => $title_builder->buildTestTitleAsLink(
                $this->test_ref_id
            ),
            'admin' => $admin,
            'participant' => $pax,
            'log_entry_type' => $lng->txt(self::LANG_VAR_PREFIX . self::IDENTIFIER),
            'interaction_type' => $lng->txt(self::LANG_VAR_PREFIX . $this->interaction_type->value)
        ];

        if ($this->question_id !== null) {
            $values['question'] = $title_builder->buildQuestionTitleAsLink(
                $this->question_id,
                $this->test_ref_id
            );
        }

        return $row_builder->buildDataRow(
            $this->getUniqueIdentifier(),
            $values
        );
    }

    public function getLogEntryAsExportRow(
        \ilLanguage $lng,
        TitleColumnsBuilder $title_builder,
        AdditionalInformationGenerator $additional_info,
        array $environment
    ): array {
        $admin = $this->getUserForPresentation($this->admin_id);
        $pax = $this->getUserForPresentation($this->pax_id);

        return  [
            \DateTimeImmutable::createFromFormat('U', (string) $this->modification_timestamp)
                ->setTimezone($environment['timezone'])
                ->format($environment['date_format']),
            $title_builder->buildTestTitleAsText($this->test_ref_id),
            $admin,
            $pax,
            '',
            $title_builder->buildQuestionTitleAsText($this->question_id),
            $lng->txt(self::LANG_VAR_PREFIX . self::IDENTIFIER),
            $lng->txt(self::LANG_VAR_PREFIX . $this->interaction_type->value),
            $this->error_message
        ];
    }

    public function getParsedAdditionalInformation(
        AdditionalInformationGenerator $additional_info,
        UIFactory $ui_factory,
        array $environment
    ): Content {
        return $ui_factory->legacy()->content($this->error_message);
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

    private function getUserForPresentation(?int $user_id): string
    {
        if ($user_id === null) {
            return '';
        }
        return \ilUserUtil::getNamePresentation(
            $user_id,
            false,
            false,
            '',
            true
        );
    }
}
