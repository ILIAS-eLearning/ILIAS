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

namespace ILIAS\Blog\ReadingTime;

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
    protected $blog_set;

    public function __construct()
    {
        $this->blog_set = new \ilSetting("blga");
        $this->repo = new ReadingTimeDBRepo();
        $this->page_manager = new \ILIAS\COPage\ReadingTime\ReadingTimeManager();
    }

    public function isGloballyActivated(): bool
    {
        return (bool) $this->blog_set->get("est_reading_time");
    }

    public function isActivated(int $blog_id): bool
    {
        return $this->repo->isActivated($blog_id);
    }

    public function activate(int $blog_id, bool $activate): void
    {
        $is_active = $this->repo->isActivated($blog_id);
        $this->repo->activate($blog_id, $activate);
        if (!$is_active && $activate) {
            $this->page_manager->setMissingReadingTimes("blp", $blog_id);
        }
    }

    /**
     * @return int|null Null, if reading time is deactivated
     */
    public function getReadingTime(int $blog_id, int $bl_page_id): ?int
    {
        if (!$this->isActivated($blog_id)) {
            return null;
        }
        return max(1, $this->page_manager->getTimeForId("blp", $bl_page_id));
    }
}
