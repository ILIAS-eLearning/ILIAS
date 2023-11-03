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
 * @package     Modules/Test(QuestionPool)
 */
class ilTestSkillLevelThresholdImportList implements Iterator
{
    protected $originalSkillTitles = [];
    protected $originalSkillPaths = [];
    protected $importedSkillLevelThresholds = [];

    public function addOriginalSkillTitle($skillBaseId, $skillTrefId, $originalSkillTitle)
    {
        $this->originalSkillTitles["{$skillBaseId}:{$skillTrefId}"] = $originalSkillTitle;
    }

    public function addOriginalSkillPath($skillBaseId, $skillTrefId, $originalSkillPath)
    {
        $this->originalSkillPaths["{$skillBaseId}:{$skillTrefId}"] = $originalSkillPath;
    }

    public function addSkillLevelThreshold(ilTestSkillLevelThresholdImport $importedSkillLevelThreshold)
    {
        $this->importedSkillLevelThresholds[] = $importedSkillLevelThreshold;
    }

    public function getThresholdsByImportSkill($importSkillBaseId, $importSkillTrefId): array
    {
        $thresholds = [];

        foreach ($this as $skillLevelThreshold) {
            if ($skillLevelThreshold->getImportSkillBaseId() != $importSkillBaseId) {
                continue;
            }

            if ($skillLevelThreshold->getImportSkillTrefId() != $importSkillTrefId) {
                continue;
            }

            $thresholds[] = $skillLevelThreshold;
        }

        return $thresholds;
    }

    public function current(): ?ilTestSkillLevelThresholdImport
    {
        $current = current($this->importedSkillLevelThresholds);
        return $current !== false ? $current : null;
    }

    public function next(): void
    {
        next($this->importedSkillLevelThresholds);
    }

    public function key(): ?int
    {
        return key($this->importedSkillLevelThresholds);
    }

    public function valid(): bool
    {
        return key($this->importedSkillLevelThresholds) !== null;
    }

    public function rewind(): void
    {
        reset($this->importedSkillLevelThresholds);
    }
}
