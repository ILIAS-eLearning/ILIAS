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

namespace ILIAS\Test\Repository;

use ilComponentInfo;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;

class QuestionRepository
{
    private readonly Transformation $strip_tags_transformer;

    public function __construct(
        private readonly \ilDBInterface $database,
        private readonly \ilComponentRepository $component_repository,
        private readonly \ilLanguage $lng,
        private readonly Refinery $refinery
    ) {
        $this->strip_tags_transformer = $this->refinery->string()->stripTags();
    }


    /**
     * @param array<string, mixed> $filter
     *
     * @return iterable<>
     */
    public function findByFilter(array $filter = []): iterable
    {
        $query = $this->buildQuery($filter);

        $result = $this->database->query($query);

        while ($row = $this->database->fetchAssoc($result)) {
            $row = $this->transformRow($row);

            if(!$row) {
                continue;
            }

            yield $row['question_id'] => $this->transformRow($row);
        }
    }


    private function transformRow(array $row): ?array
    {
        $row = \ilAssQuestionType::completeMissingPluginName($row);

        if (!$this->isActiveQuestionType($row)) {
            return null;
        }

        $row['title'] = $this->strip_tags_transformer->transform($row['title'] ?? '&nbsp;');
        $row['description'] = $this->strip_tags_transformer->transform($row['description'] !== '' && $row['description'] !== null ? $row['description'] : '&nbsp;');
        $row['author'] = $this->strip_tags_transformer->transform($row['author']);
        $row['taxonomies'] = $this->loadTaxonomyAssignmentData($row['obj_fi'], $row['question_id']);
        $row['ttype'] = $this->lng->txt($row['type_tag']);
        $row['feedback'] = $this->hasGenericFeedback((int) $row['question_id']);
        $row['hints'] = $this->hasHints((int) $row['question_id']);
        $row['comments'] = $this->getNumberOfCommentsForQuestion($row['question_id']);


        return $row;
    }

    private function buildQuery(array $filter): string
    {
        $select_fields = [
            'qpl_questions.*',
            'qpl_qst_type.type_tag',
            'qpl_qst_type.plugin',
            'qpl_qst_type.plugin_name',
            'qpl_questions.points max_points'
        ];
        $join_expressions = [];
        $where_expressions = [
            'qpl_questions.tstamp > 0'
        ];

        foreach($filter as $filter_name => $filter_options) {
            $this->applyFilter(
                $filter_name,
                $filter_options,
                $select_fields,
                $join_expressions,
                $where_expressions,
            );
        }

        $select_fields = join(', ', $select_fields);
        $where_expressions = join(' AND ', $where_expressions);
        $join_expressions = join(' ', $join_expressions);

        return "SELECT {$select_fields} FROM qpl_questions {$join_expressions} WHERE {$where_expressions}";
    }

    private function applyFilter(
        int|string $filter_name,
        mixed $filter_options,
        array &$select_fields,
        array &$join_expressions,
        array &$where_expressions
    ): void {

    }

    private function isActiveQuestionType(array $questionData): bool
    {
        if (!isset($questionData['plugin'])) {
            return false;
        }

        if (!$questionData['plugin']) {
            return true;
        }

        if (!$this->component_repository->getComponentByTypeAndName(
            ilComponentInfo::TYPE_MODULES,
            'TestQuestionPool'
        )->getPluginSlotById('qst')->hasPluginName($questionData['plugin_name'])) {
            return false;
        }

        return $this->component_repository
            ->getComponentByTypeAndName(
                ilComponentInfo::TYPE_MODULES,
                'TestQuestionPool'
            )
            ->getPluginSlotById(
                'qst'
            )
            ->getPluginByName(
                $questionData['plugin_name']
            )->isActive();
    }

}
