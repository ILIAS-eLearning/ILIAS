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

namespace ILIAS\TestQuestionPool\ExportImport\Import;

use ilAssQuestionSkillAssignment;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Transformations;
use ILIAS\Skill\Service\SkillUsageService;
use ilImportMapping;
use ilSkillTreeRepository;

/**
 * Imports skill assignments from normalized data. It will map imported skill using the source installation id. If a
 * skill is not found in the local installation, the assignment will be added to the failed list.
 *
 * @phpstan-type ImportResultData array{skill_id: int, tref_id: int, title: string, path: string}
 */
class SkillAssignmentsImporter
{
    public function __construct(
        private readonly ilSkillTreeRepository $skill_repo,
        private readonly SkillUsageService $skill_usage_service,
        private readonly string $component,
        private readonly int $local_install_id
    ) {
    }

    /**
     * Import skill assignments from normalized data. It will map imported skill ids to local skill ids and save the
     * assignments to the database. If the skill ids cannot be mapped, the assignment will be added to the failed list.
     *
     * @param array<array<string, mixed>> $normalized_assignments
     * @return array{failed: list<ImportResultData>, success: list<ImportResultData>}
     */
    public function import(
        array $normalized_assignments,
        int $import_install_id,
        Transformations $transformations,
        ilImportMapping $mapping,
    ): array {
        $result = ['failed' => [], 'success' => []];

        foreach ($normalized_assignments as $item) {
            // ParentObjID and QuestionID will be replaced by the mapping pipe
            $assignment = $transformations->denormalize($item, ilAssQuestionSkillAssignment::class);

            $skill_data = $this->getSkillIdMapping(
                $assignment->getSkillBaseId(),
                $assignment->getSkillTrefId(),
                $import_install_id
            );
            if ($skill_data === null) {
                $result['failed'][] = $this->buildResultData($assignment);
                continue;
            }

            $mapping->addMapping(
                $this->component,
                'skill_base',
                (string) $assignment->getSkillBaseId(),
                (string) $skill_data['skill_id']
            );
            $mapping->addMapping(
                $this->component,
                'skill_tref',
                (string) $assignment->getSkillTrefId(),
                (string) $skill_data['tref_id']
            );
            $assignment->setSkillBaseId($skill_data['skill_id']);
            $assignment->setSkillTrefId($skill_data['tref_id']);

            $assignment->initSolutionComparisonExpressionList();
            foreach ($assignment->getSolutionComparisonExpressionList()->get() as $expression) {
                $expression->setSkillBaseId($assignment->getSkillBaseId());
                $expression->setSkillTrefId($assignment->getSkillTrefId());
            }

            $assignment->saveToDb();
            $assignment->saveComparisonExpressions();

            $this->skill_usage_service->addUsage(
                $assignment->getParentObjId(),
                $assignment->getSkillBaseId(),
                $assignment->getSkillTrefId()
            );

            $result['success'][] = $this->buildResultData($assignment);
        }

        return $result;
    }

    protected function getSkillIdMapping(int $skill_base_id, int $skill_tref_id, int $import_install_id): ?array
    {
        if ($import_install_id === $this->local_install_id) {
            return [
                'skill_id' => $skill_base_id,
                'tref_id' => $skill_tref_id,
            ];
        }

        $found_skill_data = $this->skill_repo->getCommonSkillIdForImportId(
            $import_install_id,
            $skill_base_id,
            $skill_tref_id
        );

        $skill_data = current($found_skill_data);
        if (!is_array($skill_data) || !isset($skill_data['skill_id']) || !isset($skill_data['tref_id'])) {
            return null;
        }

        return $skill_data;
    }

    /**
     * @return ImportResultData
     */
    protected function buildResultData(ilAssQuestionSkillAssignment $assignment): array
    {
        return [
            'skill_id' => $assignment->getSkillBaseId(),
            'tref_id' => $assignment->getSkillTrefId(),
            'title' => $assignment->getSkillTitle(),
            'path' => $assignment->getSkillPath(),
        ];
    }
}
