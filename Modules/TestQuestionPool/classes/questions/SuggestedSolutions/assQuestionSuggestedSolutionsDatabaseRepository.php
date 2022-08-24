<?php

declare(strict_types=1);

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
 */

namespace ILIAS\TA\Questions;

/**
 * Repository for suggested solutions
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class assQuestionSuggestedSolutionsDatabaseRepository
{
    public const TABLE_SUGGESTED_SOLUTIONS = 'qpl_sol_sug';

    protected \ilDBInterface $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function create(int $question_id, string $type): assQuestionSuggestedSolution
    {
        $solution = $this->buildSuggestedSolution(
            -1,
            $question_id,
            '',
            '',
            0,
            $type,
            '',
            $this->getNow()
        );
        return $solution;
    }

    /**
     * return assQuestionSuggestedSolution[]
     */
    public function selectFor(int $question_id): array
    {
        $ret = [];
        $query = 'SELECT' . PHP_EOL
            . 'suggested_solution_id, question_fi, internal_link, import_id, '
            . 'subquestion_index, type, tstamp, value' . PHP_EOL
            . 'FROM ' . self::TABLE_SUGGESTED_SOLUTIONS . PHP_EOL
            . 'WHERE question_fi = ' . $this->db->quote($question_id, 'integer');

        $result = $this->db->query($query);

        while ($row = $this->db->fetchAssoc($result)) {
            $last_update = \DateTimeImmutable::createFromFormat('U', $row['tstamp']);

            $ret[] = $this->buildSuggestedSolution(
                (int) $row['suggested_solution_id'],
                (int) $row['question_fi'],
                (string) $row['internal_link'],
                (string) $row['import_id'],
                (int) $row['subquestion_index'],
                (string) $row['type'],
                (string) $row['value'],
                $last_update
            );
        }

        return $ret;
    }

    public function update(array $suggested_solutions): void
    {
        foreach ($suggested_solutions as $solution) {
            if (!is_a($solution, assQuestionSuggestedSolution::class)) {
                throw new \Exception('cannot update other than assQuestionSuggestedSolution');
            }
        };

        foreach ($suggested_solutions as $solution) {
            $query = 'DELETE FROM ' . self::TABLE_SUGGESTED_SOLUTIONS . PHP_EOL
                . 'WHERE question_fi = ' . $this->db->quote($solution->getQuestionId(), 'integer') . PHP_EOL
                . 'AND subquestion_index = ' . $this->db->quote($solution->getSubQuestionIndex(), 'integer');
            $this->db->manipulate($query);

            $next_id = $this->db->nextId(self::TABLE_SUGGESTED_SOLUTIONS);
            $internal_link = '';
            if ($solution->isOfTypeLink()) {
                $internal_link = $solution->getInternalLink();
            }
            $this->db->insert(
                self::TABLE_SUGGESTED_SOLUTIONS,
                [
                   'suggested_solution_id' => ['integer', $next_id],
                   'question_fi' => ['integer', $solution->getQuestionId()],
                   'type' => ['text',$solution->getType()],
                   'value' => ['clob', $solution->getStorableValue()],
                   'internal_link' => ['text', $internal_link],
                   'import_id' => ['text', $solution->getImportId()],
                   'subquestion_index' => ['integer', $solution->getSubquestionIndex() ],
                   'tstamp' => ['integer', $this->getNow()->format('U')]
                ]
            );

            $this->additionalOnStore($solution);
        }
    }

    public function delete(int $suggested_solution_id): void
    {
        $query = 'DELETE FROM ' . self::TABLE_SUGGESTED_SOLUTIONS . PHP_EOL
            . 'WHERE suggested_solution_id = ' . $this->db->quote($suggested_solution_id, 'integer');
        $this->db->manipulate($query);
    }

    public function deleteForQuestion(int $question_id): void
    {
        $query = 'DELETE FROM ' . self::TABLE_SUGGESTED_SOLUTIONS . PHP_EOL
            . 'WHERE question_fi = ' . $this->db->quote($question_id, 'integer');
        $this->db->manipulate($query);
        $this->additionalOnDelete($question_id);
    }

    public function syncForQuestion(int $source_question_id, int $target_question_id): void
    {
        if ($source_question_id === $target_question_id) {
            throw new \LogicException('do not sync with same question');
        }
        $this->deleteForQuestion($target_question_id);
        foreach ($this->selectFor($source_question_id) as $solution) {
            $this->update($solution->withQuestionId($target_question_id));
        }
    }

    protected function buildSuggestedSolution(
        int $id,
        int $question_id,
        string $internal_link,
        string $import_id,
        int $subquestion_index,
        string $type,
        string $value,
        \DateTimeImmutable $last_update
    ): assQuestionSuggestedSolution {
        switch ($type) {
            case assQuestionSuggestedSolution::TYPE_FILE:
                $suggestion_class = assSuggestedSolutionFile::class;
                break;
            case assQuestionSuggestedSolution::TYPE_TEXT:
                $suggestion_class = assSuggestedSolutionText::class;
                break;
            case assQuestionSuggestedSolution::TYPE_LM:
            case assQuestionSuggestedSolution::TYPE_LM_CHAPTER:
            case assQuestionSuggestedSolution::TYPE_LM_PAGE:
            case assQuestionSuggestedSolution::TYPE_GLOSARY_TERM:
                $suggestion_class = assSuggestedSolutionLink::class;
                $value = $internal_link;
                break;
            default:
                throw new \LogicException('invalid suggestion-type in repo.');
        }

        return new $suggestion_class(
            $id,
            $question_id,
            $subquestion_index,
            $import_id,
            $last_update,
            $type,
            $value
        );
    }

    protected function getNow(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }


    protected function additionalOnDelete(int $question_id): void
    {
        \ilInternalLink::_deleteAllLinksOfSource("qst", $question_id);
    }

    protected function additionalOnStore(assQuestionSuggestedSolution $solution): void
    {
        if ($solution->isOfTypeLink()) {
            if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $solution->getInternalLink(), $matches)) {
                \ilInternalLink::_saveLink("qst", $solution->getQuestionId(), $matches[2], (int) $matches[3], (int) $matches[1]);
            }
        }

        if ($solution->isOfTypeText()) {
            \ilRTE::_cleanupMediaObjectUsage($solution->getValue(), "qpl:html", $solution->getQuestionId());
        }
    }
}
