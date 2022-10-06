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
class ilTestSkillLevelThresholdImport
{
    /**
     * @var integer
     */
    protected $importSkillBaseId = null;
    /**
     * @var integer
     */
    protected $importSkillTrefId = null;

    /**
     * @var integer
     */
    protected $importLevelId = null;
    /**
     * @var integer
     */
    protected $orderIndex = null;

    /**
     * @var integer
     */
    protected $threshold = null;

    /**
     * @var string
     */
    protected $originalLevelTitle = null;
    /**
     * @var string
     */
    protected $originalLevelDescription = null;

    /**
     * @return int
     */
    public function getImportSkillBaseId(): ?int
    {
        return $this->importSkillBaseId;
    }

    /**
     * @param int $importSkillBaseId
     */
    public function setImportSkillBaseId($importSkillBaseId)
    {
        $this->importSkillBaseId = $importSkillBaseId;
    }

    /**
     * @return int
     */
    public function getImportSkillTrefId(): ?int
    {
        return $this->importSkillTrefId;
    }

    /**
     * @param int $importSkillTrefId
     */
    public function setImportSkillTrefId($importSkillTrefId)
    {
        $this->importSkillTrefId = $importSkillTrefId;
    }

    /**
     * @return int
     */
    public function getImportLevelId(): ?int
    {
        return $this->importLevelId;
    }

    /**
     * @param int $importLevelId
     */
    public function setImportLevelId($importLevelId)
    {
        $this->importLevelId = $importLevelId;
    }

    /**
     * @return int
     */
    public function getOrderIndex(): ?int
    {
        return $this->orderIndex;
    }

    /**
     * @param int $orderIndex
     */
    public function setOrderIndex($orderIndex)
    {
        $this->orderIndex = $orderIndex;
    }

    /**
     * @return int
     */
    public function getThreshold(): ?int
    {
        return $this->threshold;
    }

    /**
     * @param int $threshold
     */
    public function setThreshold($threshold)
    {
        $this->threshold = $threshold;
    }

    /**
     * @return string
     */
    public function getOriginalLevelTitle(): ?string
    {
        return $this->originalLevelTitle;
    }

    /**
     * @param string $originalLevelTitle
     */
    public function setOriginalLevelTitle($originalLevelTitle)
    {
        $this->originalLevelTitle = $originalLevelTitle;
    }

    /**
     * @return string
     */
    public function getOriginalLevelDescription(): ?string
    {
        return $this->originalLevelDescription;
    }

    /**
     * @param string $originalLevelDescription
     */
    public function setOriginalLevelDescription($originalLevelDescription)
    {
        $this->originalLevelDescription = $originalLevelDescription;
    }
}
