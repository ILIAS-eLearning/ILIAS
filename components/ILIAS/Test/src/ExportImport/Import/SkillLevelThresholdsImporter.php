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

namespace ILIAS\Test\ExportImport\Import;

use ilDBInterface;
use ilImportMapping;
use ilSkillTreeRepository;
use ilTestSkillLevelThreshold;
use ilTestSkillLevelThresholdList;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Transformations;
use Psr\Log\LoggerInterface;

/**
 * Imports skill level thresholds from normalized data. It maps imported skill level ids to local skill level ids using
 * the source installation id. If a skill level cannot be mapped, the threshold is added to the failed list.
 *
 * Depends on SkillAssignmentsImporter having run first, so that skill_base and skill_tref id mappings are already
 * registered in ilImportMapping before thresholds are denormalized.
 *
 * @phpstan-type ImportResultData array{skill_base_id: int, skill_tref_id: int, skill_level_id: int, threshold: int}
 */
class SkillLevelThresholdsImporter
{
    public function __construct(
        private readonly LoggerInterface $log,
        private readonly ilDBInterface $db,
        private readonly ilSkillTreeRepository $skill_repo,
        private readonly string $component,
        private readonly int $local_install_id
    ) {
    }

    /**
     * Import skill level thresholds from normalized data. It will map imported skill level ids to local skill level ids
     * and persist them to the database. If a skill level id cannot be mapped, the threshold is added to the failed list.
     *
     * @param array<array<string, mixed>> $normalized_thresholds
     * @return array{failed: list<ImportResultData>, success: list<ImportResultData>}
     */
    public function import(
        array $normalized_thresholds,
        int $import_install_id,
        Transformations $transformations,
        ilImportMapping $mapping,
    ): array {
        $result = ['failed' => [], 'success' => []];
        $threshold_list = new ilTestSkillLevelThresholdList($this->db);

        foreach ($normalized_thresholds as $item) {
            // The mapping pipe replaces TestID and Skill BaseID/TRefID
            $threshold = $transformations->denormalize($item, ilTestSkillLevelThreshold::class);

            $local_level_id = $this->getLevelIdMapping($import_install_id, $threshold->getSkillLevelId());
            if ($local_level_id === null) {
                $this->log->warning("Failed to find skill level id mapping for threshold: {$threshold->getSkillLevelId()}");
                $result['failed'][] = $this->buildResultData($threshold);
                continue;
            }
            $this->log->debug("Found skill level id mapping for threshold: {$threshold->getSkillLevelId()} -> {$local_level_id}");

            $mapping->addMapping(
                $this->component,
                'skill_level',
                (string) $threshold->getSkillLevelId(),
                (string) $local_level_id,
            );
            $threshold->setSkillLevelId($local_level_id);

            $threshold_list->addThreshold($threshold);
            $result['success'][] = $this->buildResultData($threshold);
        }

        $threshold_list->saveToDb();
        $this->log->debug('Saved skill level thresholds');

        return $result;
    }

    protected function getLevelIdMapping(int $import_install_id, int $import_level_id): ?int
    {
        if ($import_install_id === $this->local_install_id) {
            return $import_level_id;
        }

        $result = $this->skill_repo->getCommonSkillIdForImportId($import_install_id, $import_level_id);
        $most_new_level_data = current($result);
        if (!is_array($most_new_level_data)) {
            return null;
        }

        return $most_new_level_data['level_id'];
    }

    /**
     * @return ImportResultData
     */
    private function buildResultData(ilTestSkillLevelThreshold $threshold): array
    {
        return [
            'skill_base_id' => $threshold->getSkillBaseId() ?? 0,
            'skill_tref_id' => $threshold->getSkillTrefId() ?? 0,
            'skill_level_id' => $threshold->getSkillLevelId() ?? 0,
            'threshold' => $threshold->getThreshold() ?? 0,
        ];
    }
}
