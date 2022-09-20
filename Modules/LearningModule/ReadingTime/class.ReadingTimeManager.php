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

namespace ILIAS\LearningModule\ReadingTime;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ReadingTimeManager
{
    /**
     * @var \ILIAS\COPage\ReadingTime\ReadingTimeManager
     */
    protected $page_manager;
    /**
     * @var ReadingTimeDBRepo
     */
    protected $repo;
    /**
     * @var \ilSetting
     */
    protected $lm_set;

    public function __construct()
    {
        $this->lm_set = new \ilSetting("lm");
        $this->repo = new ReadingTimeDBRepo();
        $this->page_manager = new \ILIAS\COPage\ReadingTime\ReadingTimeManager();
    }

    public function isGloballyActivated(): bool
    {
        return (bool) $this->lm_set->get("est_reading_time");
    }

    public function isActivated(int $lm_id): bool
    {
        return $this->repo->isActivated($lm_id);
    }

    /**
     * Set activation. If switched from off to on, ensure all
     * missing page reading times are set.
     */
    public function activate(int $lm_id, bool $activate): void
    {
        $is_active = $this->repo->isActivated($lm_id);
        $this->repo->activate($lm_id, $activate);
        if (!$is_active && $activate) {
            $this->page_manager->setMissingReadingTimes("lm", $lm_id);
            $this->updateReadingTime($lm_id);
        }
    }

    /**
     * Gets the calculated reading time from all pages
     * of the LM and stores it (redundantly for quick access)
     * in the learning module
     */
    public function updateReadingTime(int $lm_id): void
    {
        $reading_time = $this->page_manager->getParentReadingTime("lm", $lm_id);
        $this->repo->saveReadingTime($lm_id, $reading_time);
    }

    public function loadData(array $lm_ids): void
    {
        $this->repo->loadData($lm_ids);
    }

    /**
     * @return int|null Null, if reading time is deactivated
     */
    public function getReadingTime(int $lm_id): ?int
    {
        return $this->repo->getReadingTime($lm_id);
    }
}
