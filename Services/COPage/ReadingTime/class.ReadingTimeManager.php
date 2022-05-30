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

namespace ILIAS\COPage\ReadingTime;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ReadingTimeManager
{
    /**
     * @var \ILIAS\Refinery\Factory
     */
    protected $refinery;

    /**
     * @var ReadingTimeDBRepo
     */
    protected $repo;

    public function __construct()
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->refinery = $DIC->refinery();
        $this->repo = new ReadingTimeDBRepo();
    }

    public function saveTime(\ilPageObject $page) : void
    {
        $minutes = 0;

        $page->buildDom();
        $xml = $page->getXMLFromDom();

        $transf = $this->refinery->string()->estimatedReadingTime(true);
        $minutes = $transf->transform(strip_tags($xml));
        $minutes = max($minutes, 1);

        $this->repo->saveTime(
            $page->getId(),
            $page->getParentType(),
            $page->getLanguage(),
            $minutes
        );
    }

    public function getTime(\ilPageObject $page) : int
    {
        return $this->repo->getTime(
            $page->getId(),
            $page->getParentType(),
            $page->getLanguage()
        );
    }

    public function getTimeForId(
        string $parent_type,
        int $page_id,
        string $lang = "-"
    ) : int {
        return $this->repo->getTime(
            $page_id,
            $parent_type,
            $lang
        );
    }

    public function setMissingReadingTimes(string $parent_type, int $parent_id) : void
    {
        $pages = $this->repo->getPagesWithMissingReadingTime($parent_type, $parent_id);
        foreach ($pages as $p) {
            $page = \ilPageObjectFactory::getInstance(
                $p["parent_type"],
                (int) $p["page_id"],
                0,
                $p["lang"]
            );
            $this->saveTime($page);
        }
    }

    public function getParentReadingTime(
        string $a_parent_type,
        int $a_parent_id
    ) : int {
        return $this->repo->getParentReadingTime($a_parent_type, $a_parent_id);
    }
}
