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
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\StaticURL\Services as StaticURLServices;
use ILIAS\Data\ReferenceId;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Table\DataRow;

class TestQuestionAdministrationInteraction implements TestUserInteraction
{
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
        UIRenderer $ui_renderer,
        DataRowBuilder $row_builder,
        array $environment
    ): DataRow {
        $test_obj_id = \ilObject::_lookupObjId($this->test_ref_id);
        return $row_builder->buildDataRow(
            $this->getUniqueIdentifier(),
            [
                'date_and_time' => new \DateTimeImmutable("@{$this->modification_timestamp}", $environment['timezone']),
                'corresponding_test' => $ui_factory->link()->standard(
                    \ilObject::_lookupTitle($test_obj_id),
                    $static_url->builder()->build('tst', new ReferenceId($this->test_ref_id))->__toString()
                ),
                'admin' => \ilUserUtil::getNamePresentation(
                    $this->admin_id,
                    false,
                    false,
                    '',
                    true
                ),
                'participant' => '',
                'ip' => '',
                'question' => $ui_renderer->render(
                    $ui_factory->link()->standard(
                        $properties_repository->getForQuestionId($this->question_id)->getTitle(),
                        $static_url->builder()->build(
                            'tst',
                            new ReferenceId($this->test_ref_id),
                            ['qst', $this->question_id]
                        )->__toString()
                    )
                ),
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
            'interaction_type' => [\ilDBConstants::T_TEXT , $this->interaction_type->value],
            'modification_ts' => [\ilDBConstants::T_INTEGER , $this->modification_timestamp],
            'additional_data' => [\ilDBConstants::T_CLOB , json_encode($this->additional_data)]
        ];
    }

    public function withPresentationData(): void
    {

    }
}
