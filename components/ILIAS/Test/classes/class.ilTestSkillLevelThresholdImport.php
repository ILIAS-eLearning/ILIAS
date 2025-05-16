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
 * @package components\ILIAS/Test(QuestionPool)
 */
class ilTestSkillLevelThresholdImport
{
    protected ?int $import_skill_base_id = null;
    protected ?int $import_skill_tref_id = null;
    protected ?int $import_level_id = null;
    protected ?int $order_index = null;
    protected ?int $threshold = null;
    protected ?string $original_level_title = null;
    protected ?string $original_level_description = null;

    public function getImportSkillBaseId(): ?int
    {
        return $this->import_skill_base_id;
    }

    public function setImportSkillBaseId(int $import_skill_base_id): void
    {
        $this->import_skill_base_id = $import_skill_base_id;
    }

    public function getImportSkillTrefId(): ?int
    {
        return $this->import_skill_tref_id;
    }

    public function setImportSkillTrefId(int $import_skill_tref_id): void
    {
        $this->import_skill_tref_id = $import_skill_tref_id;
    }

    /**
     * @return int
     */
    public function getImportLevelId(): ?int
    {
        return $this->import_level_id;
    }

    public function setImportLevelId(int $import_level_id): void
    {
        $this->import_level_id = $import_level_id;
    }

    public function getOrderIndex(): ?int
    {
        return $this->order_index;
    }

    public function setOrderIndex(int $order_index): void
    {
        $this->order_index = $order_index;
    }

    public function getThreshold(): ?int
    {
        return $this->threshold;
    }

    public function setThreshold(int $threshold): void
    {
        $this->threshold = $threshold;
    }

    public function getOriginalLevelTitle(): ?string
    {
        return $this->original_level_title;
    }

    public function setOriginalLevelTitle(string $original_level_title): void
    {
        $this->original_level_title = $original_level_title;
    }

    public function getOriginalLevelDescription(): ?string
    {
        return $this->original_level_description;
    }

    public function setOriginalLevelDescription(string $original_level_description): void
    {
        $this->original_level_description = $original_level_description;
    }
}
