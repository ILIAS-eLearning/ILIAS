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

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test(QuestionPool)
 */
class ilTestSkillLevelThresholdImportList implements Iterator
{
    protected $originalSkillTitles = array();
    protected $originalSkillPaths = array();
    protected $importedSkillLevelThresholds = array();

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
        $thresholds = array();

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

    /**
     * @return ilTestSkillLevelThresholdImport
     */
    public function current(): ilTestSkillLevelThresholdImport
    {
        return current($this->importedSkillLevelThresholds);
    }

    public function next()
    {
        return next($this->importedSkillLevelThresholds);
    }

    /**
     * @return integer|bool
     */
    public function key()
    {
        return key($this->importedSkillLevelThresholds);
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return key($this->importedSkillLevelThresholds) !== null;
    }

    /**
     * @return ilTestSkillLevelThresholdImport|bool
     */
    public function rewind()
    {
        return reset($this->importedSkillLevelThresholds);
    }
}
