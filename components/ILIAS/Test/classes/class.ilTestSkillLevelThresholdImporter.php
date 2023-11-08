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

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package components\ILIAS/Test
 */
class ilTestSkillLevelThresholdImporter
{
    private ?int $targetTestId = null;
    private ?int $importInstallationId = null;

    private ?ilImportMapping $importMappingRegistry = null;
    private ?ilAssQuestionSkillAssignmentList $importedQuestionSkillAssignmentList = null;
    private ?ilTestSkillLevelThresholdImportList $importThresholdList = null;
    private ilAssQuestionAssignedSkillList $failedThresholdImportSkillList;

    public function __construct(
        private ilDBInterface $db
    ) {
        $this->failedThresholdImportSkillList = new ilAssQuestionAssignedSkillList();
    }

    public function getTargetTestId(): ?int
    {
        return $this->targetTestId;
    }

    public function setTargetTestId(int $targetTestId): void
    {
        $this->targetTestId = $targetTestId;
    }

    public function getImportInstallationId(): ?int
    {
        return $this->importInstallationId;
    }

    public function setImportInstallationId(int $importInstallationId): void
    {
        $this->importInstallationId = $importInstallationId;
    }

    public function getImportMappingRegistry(): ?ilImportMapping
    {
        return $this->importMappingRegistry;
    }

    public function setImportMappingRegistry(ilImportMapping $importMappingRegistry): void
    {
        $this->importMappingRegistry = $importMappingRegistry;
    }

    public function getImportedQuestionSkillAssignmentList(): ?ilAssQuestionSkillAssignmentList
    {
        return $this->importedQuestionSkillAssignmentList;
    }

    public function setImportedQuestionSkillAssignmentList(ilAssQuestionSkillAssignmentList $importedQuestionSkillAssignmentList): void
    {
        $this->importedQuestionSkillAssignmentList = $importedQuestionSkillAssignmentList;
    }

    public function getImportThresholdList(): ?ilTestSkillLevelThresholdImportList
    {
        return $this->importThresholdList;
    }

    public function setImportThresholdList(?ilTestSkillLevelThresholdImportList $importThresholdList): void
    {
        $this->importThresholdList = $importThresholdList;
    }

    public function getFailedThresholdImportSkillList(): ?ilAssQuestionAssignedSkillList
    {
        return $this->failedThresholdImportSkillList;
    }

    public function setFailedThresholdImportSkillList(ilAssQuestionAssignedSkillList $failedThresholdImportSkillList): void
    {
        $this->failedThresholdImportSkillList = $failedThresholdImportSkillList;
    }

    public function import(): void
    {
        $importedLevelThresholdList = new ilTestSkillLevelThresholdList($this->db);

        foreach ($this->getImportedQuestionSkillAssignmentList()->getUniqueAssignedSkills() as $skillData) {
            /* @var ilBasicSkill $skill */
            $skill = $skillData['skill'];

            $importSkillBaseId = $this->getImportMappingRegistry()->getMapping(
                'components/ILIAS/Test',
                'skl_base_id_reverse',
                $skillData['skill_base_id']
            );

            $importSkillTrefId = $this->getImportMappingRegistry()->getMapping(
                'components/ILIAS/Test',
                'skl_tref_id_reverse',
                $skillData['skill_tref_id']
            );

            $levelThresholds = $this->getImportThresholdList()->getThresholdsByImportSkill(
                $importSkillBaseId,
                $importSkillTrefId
            );

            $existingLevels = $skill->getLevelData();

            if (count($levelThresholds) != count($existingLevels)) {
                $this->getFailedThresholdImportSkillList()->addSkill(
                    $skillData['skill_base_id'],
                    $skillData['skill_tref_id']
                );

                continue;
            }

            for ($i = 0, $max = count($existingLevels); $i < $max; $i++) {
                $existingLevelData = $existingLevels[$i];

                /* @var ilTestSkillLevelThresholdImport $importLevelThreshold */
                $importLevelThreshold = $levelThresholds[$i];

                if ($importLevelThreshold->getOrderIndex() != $existingLevelData['nr']) {
                    $this->getFailedThresholdImportSkillList()->addSkill(
                        $skillData['skill_base_id'],
                        $skillData['skill_tref_id']
                    );

                    continue(2);
                }

                if (!is_numeric($importLevelThreshold->getThreshold())) {
                    continue(2);
                }

                $mappedLevelId = $this->getLevelIdMapping($importLevelThreshold->getImportLevelId());

                $threshold = new ilTestSkillLevelThreshold($this->db);
                $threshold->setTestId($this->getTargetTestId());
                $threshold->setSkillBaseId($skillData['skill_base_id']);
                $threshold->setSkillTrefId($skillData['skill_tref_id']);
                $threshold->setSkillLevelId($mappedLevelId);
                $threshold->setThreshold($importLevelThreshold->getThreshold());

                $importedLevelThresholdList->addThreshold($threshold);
            }
        }

        $importedLevelThresholdList->saveToDb();
    }

    protected function getLevelIdMapping(int $importLevelId): int
    {
        $result = ilBasicSkill::getLevelIdForImportId($this->getImportInstallationId(), $importLevelId);
        $mostNewLevelData = current($result);
        return $mostNewLevelData['level_id'];
    }
}
