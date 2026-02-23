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

namespace ILIAS\Wiki\Media;

use Generator;
use ilCtrl;
use ILIAS\MediaObjects\OverviewGUI\SubObjectRetrieval;
use ILIAS\Wiki\Page\PageManager;

class PageRetrieval implements SubObjectRetrieval
{
    /**
     * @var string[]
     */
    protected array $page_titles_by_id;

    public function __construct(
        protected PageManager $page_manager,
        protected ilCtrl $ctrl
    ) {
    }

    /**
     * @return string[]
     */
    protected function getPageTitleByID(): array
    {
        if (isset($this->page_titles_by_id)) {
            return $this->page_titles_by_id;
        }
        $this->page_titles_by_id = [];
        foreach ($this->page_manager->getWikiPages() as $page) {
            $this->page_titles_by_id[$page->getId()] = $page->getTitle();
        }
        return $this->page_titles_by_id;
    }

    /**
     * @return string[]
     */
    public function getPossibleTypes(): Generator
    {
        yield 'wpg:pg';
    }

    /**
     * @return int[]
     */
    public function getAllIDsForType(string $type): Generator
    {
        if ($type !== 'wpg:pg') {
            return;
        }
        yield from array_keys($this->getPageTitleByID());
    }

    public function getLinkToSubObject(string $type, int $id): string
    {
        if ($type !== 'wpg:pg') {
            return '';
        }
        return $this->page_manager->getPermaLink($id);
    }

    public function getTitleOfSubObject(string $type, int $id): string
    {
        if ($type !== 'wpg:pg') {
            return '';
        }
        return $this->getPageTitleByID()[$id] ?? '';
    }
}
