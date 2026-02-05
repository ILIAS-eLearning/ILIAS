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

namespace ILIAS\Questions\AnswerForm\Migration;

use ILIAS\Questions\Persistence\CoreTables;
use ILIAS\Questions\Persistence\Insert;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Persistence\Value;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Setup\CLI\IOWrapper;

class MigrationInsert
{
    private ?float $available_points = null;
    private ?int $image_size = null;
    private ?bool $shuffle_answer_options = null;
    private string $additional_text = '';
    private ?string $additional_text_legacy = '';

    public function __construct(
        private readonly \ilDBInterface $db,
        private readonly IOWrapper $io,
        private readonly UuidFactory $uuid_factory,
        private readonly TableNameBuilder $table_name_builder,
        private array $inserts,
        private readonly int $old_question_id,
        private readonly Uuid $new_question_id,
        private readonly Uuid $answer_form_id,
        private readonly string $definition_class,
        private readonly bool $ilias_page_editor_used_for_additional_texts
    ) {
    }

    public function getDb(): \ilDBInterface
    {
        return $this->db;
    }

    public function getIO(): IOWrapper
    {
        return $this->io;
    }

    public function getUuid(): Uuid
    {
        return $this->uuid_factory->uuid4();
    }

    public function getTableNameBuilder(): TableNameBuilder
    {
        return $this->table_name_builder;
    }

    public function getOldQuestionId(): int
    {
        return $this->old_question_id;
    }

    public function getAnswerFormId(): Uuid
    {
        return $this->answer_form_id;
    }

    public function wasIliasPageEditorUsedForAdditionalTexts(): bool
    {
        return $this->ilias_page_editor_used_for_additional_texts;
    }

    public function withAvailablePoints(
        float $available_points
    ): self {
        $clone = clone $this;
        $clone->available_points = $available_points;
        return $clone;
    }

    public function withImageSize(
        int $image_size
    ): self {
        $clone = clone $this;
        $clone->image_size = $image_size;
        return $clone;
    }

    public function withShuffleAnswerOptions(
        bool $shuffle_answer_options
    ): self {
        $clone = clone $this;
        $clone->shuffle_answer_options = $shuffle_answer_options;
        return $clone;
    }

    public function withAdditionalText(
        string $additional_text
    ): self {
        $clone = clone $this;
        $clone->additional_text = $additional_text;
        return $clone;
    }

    public function withAdditionalTextLegacy(
        string $additional_text_legacy
    ): self {
        $clone = clone $this;
        $clone->additional_text_legacy = $additional_text_legacy;
        return $clone;
    }

    public function withAdditionalInsert(
        Insert $insert
    ): self {
        $clone = clone $this;
        $clone->inserts[] = $insert;
        return $clone;
    }

    public function run(): void
    {
        $this->inserts[] = $this->buildCoreAnswerFormInsertStatement();
        $atom_query = $this->db->buildAtomQuery();

        $manipulates = [];
        $locked_tables = [];
        foreach ($this->inserts as $statement) {
            $table_to_lock = $statement->getTableToLock();
            if (!in_array($table_to_lock, $locked_tables)) {
                $atom_query->addTableLock($table_to_lock);
                $locked_tables[] = $table_to_lock;
            }
            $manipulates[] = $statement->toManipulateString($this->db);
        }
        $atom_query->addQueryCallable(
            function (\ilDBInterface $db) use ($manipulates): void {
                foreach ($manipulates as $manipulate) {
                    $db->manipulate($manipulate);
                }
            }
        );
        $atom_query->run();
    }

    private function buildCoreAnswerFormInsertStatement(): Insert
    {
        return new Insert(
            CoreTables::AnswerForms->getColumns(),
            [
                new Value(\ilDBConstants::T_TEXT, $this->answer_form_id->toString()),
                new Value(\ilDBConstants::T_TEXT, $this->definition_class),
                new Value(\ilDBConstants::T_TEXT, $this->new_question_id->toString()),
                new Value(\ilDBConstants::T_FLOAT, $this->available_points),
                new Value(\ilDBConstants::T_INTEGER, $this->image_size),
                new Value(
                    \ilDBConstants::T_INTEGER,
                    $this->shuffle_answer_options === null
                        ? null
                        : ($this->shuffle_answer_options ? 1 : 0)
                ),
                new Value(\ilDBConstants::T_TEXT, $this->additional_text),
                new Value(\ilDBConstants::T_TEXT, $this->additional_text_legacy)

            ]
        );
    }
}
