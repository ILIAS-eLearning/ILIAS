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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Migration;

use ILIAS\Questions\AnswerForm\Migration\Migration;
use ILIAS\Questions\AnswerForm\Migration\MigrationInsert;
use ILIAS\Questions\AnswerFormTypes\Cloze\Definition;
use ILIAS\Questions\AnswerFormTypes\Cloze\Persistence;
use ILIAS\Questions\Persistence\TableNameSpace;
use ILIAS\Setup\Environment;

class MigrationLongMenu implements Migration
{
    use BasicMigrationFunctions;

    public function __construct(
        private readonly Persistence $persistence
    ) {
    }

    #[\Override]
    public function getOldQuestionIdentifier(): string
    {
        return 'assLongMenu';
    }

    #[\Override]
    public function getDefinitionClass(): string
    {
        return Definition::class;
    }

    #[\Override]
    public function getTableNameSpace(): TableNameSpace
    {
        return $this->persistence->getTableNameSpace();
    }

    #[\Override]
    public function completeMigrationInsert(
        Environment $environment,
        MigrationInsert $migration_insert
    ): ?MigrationInsert {
        $answer_form_id = $migration_insert->getAnswerFormId();

        $answer_input_mapping = [];
        $gaps_insert = null;
        $answer_options_to_insert = [];

        foreach ($this->fetchDBValues(
            $migration_insert->getDb(),
            $migration_insert->getOldQuestionId()
        ) as $db_row) {
            if (!isset($answer_input_mapping[$db_row->gap_number])) {
                $answer_input_id = $migration_insert->getUuid();
                $answer_input_mapping[$db_row->gap_number] = $answer_input_id;

                $gaps_insert = $this->buildGapInsertStatement(
                    $this->persistence,
                    $migration_insert->getPersistenceFactory(),
                    $migration_insert->getTableNameBuilder(),
                    $gaps_insert,
                    $answer_input_id,
                    $answer_form_id,
                    $db_row->gap_number,
                    $this->buildNewGapTypeIdentifierFromOld($db_row->type),
                    null,
                    null,
                    null,
                    $db_row->min_auto_complete,
                    $db_row->shuffle_answers === '1' ? 1 : 0
                );

                $answers_from_file = $this->loadAnswersFromFile(
                    $environment->getResource(Environment::RESOURCE_ILIAS_INI),
                    $environment->getResource(Environment::RESOURCE_CLIENT_ID),
                    $migration_insert->getOldQuestionId(),
                    $db_row->gap_number
                );

                $answer_options_to_insert[$answer_input_id->toString()] = array_map(
                    fn(int $v) => [
                        'answer_option_id' => $migration_insert->getUuid(),
                        'answer_input_id' => $answer_input_id,
                        'position' => $v,
                        'text' => trim($answers_from_file[$v]),
                        'points' => 0.0
                    ],
                    array_keys($answers_from_file)
                );
            }

            $answer_input_id_string = $answer_input_mapping[$db_row->gap_number]->toString();
            $position = array_find_key(
                $answer_options_to_insert[$answer_input_id_string],
                fn(array $v, int $k): bool => trim($v['text']) === trim($db_row->answer_text)
            );

            if ($position !== null) {
                $answer_options_to_insert[$answer_input_id_string][$position]['points'] = $db_row->points;
                ;
            }


        }

        if (!isset($db_row)) {
            return null;
        }

        $answer_options_insert = null;
        foreach (array_reduce(
            $answer_options_to_insert,
            fn(array $c, array $v): array => [...$c, ...$v],
            []
        ) as $answer) {
            $answer_options_insert = $this->buildAnswerOptionInsertStatement(
                $this->persistence,
                $migration_insert->getPersistenceFactory(),
                $migration_insert->getTableNameBuilder(),
                $answer_options_insert,
                $answer['answer_option_id'],
                $answer['answer_input_id'],
                $answer['position'],
                $answer['text'],
                $answer['points'],
                null,
                null
            );
        }

        return $migration_insert
            ->withAdditionalInsert(
                $this->buildAnswerFormInsertStatement(
                    $this->persistence,
                    $migration_insert->getPersistenceFactory(),
                    $migration_insert->getTableNameBuilder(),
                    $answer_form_id,
                    $this->buildScoringIdenticalFromOld($db_row->identical_scoring),
                    0
                )
            )->withAdditionalTextLegacy(
                $this->replaceGapsAndSantizeLegacyClozeText(
                    $migration_insert->getDb(),
                    '\[Longmenu \d+\]',
                    $db_row->long_menu_text,
                    $answer_input_mapping,
                    $migration_insert->wasIliasPageEditorUsedForAdditionalTexts()
                )
            )->withAdditionalInsert($gaps_insert)
            ->withAdditionalInsert($answer_options_insert);
    }

    private function fetchDBValues(
        \ilDBInterface $db,
        int $old_question_id
    ): \Generator {
        $query = $db->query(
            'SELECT * FROM qpl_qst_lome q' . PHP_EOL
            . 'JOIN qpl_a_lome a ON q.question_fi = a.question_fi' . PHP_EOL
            . "WHERE q.question_fi = {$db->quote($old_question_id)}" . PHP_EOL
            . 'ORDER BY a.gap_number, a.position'
        );

        while (($row = $db->fetchObject($query)) !== null) {
            yield $row;
        }
    }

    private function loadAnswersFromFile(
        \ilIniFile $ini,
        string $client_id,
        int $old_question_id,
        int $gap_id
    ): array {
        $file = "{$ini->readVariable('clients', 'datadir')}/{$client_id}/assessment/longMenuQuestion/{$old_question_id}/{$gap_id}.txt";

        if (!file_exists($file)) {
            return [];
        }

        return explode(
            "\n",
            file_get_contents($file)
        );
    }

    private function buildNewGapTypeIdentifierFromOld(
        int $old_gap_type
    ): string {
        return match($old_gap_type) {
            \assLongMenu::ANSWER_TYPE_TEXT_VAL => 'long_menu',
            \assLongMenu::ANSWER_TYPE_SELECT_VAL => 'select'
        };
    }
}
