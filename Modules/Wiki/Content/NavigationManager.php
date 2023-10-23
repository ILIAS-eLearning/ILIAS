<?php

declare(strict_types=1);

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

namespace ILIAS\Wiki\Content;

use ILIAS\Wiki\InternalGUIService;
use ILIAS\Wiki\InternalDomainService;
use ILIAS\Wiki\WikiGUIRequest;
use ILIAS\Wiki\Page\PageManager;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class NavigationManager
{
    protected PageManager $pm;
    protected string $lang;
    protected string $page_title;
    protected int $wpg_id;
    protected \ilObjWiki $wiki;
    protected bool $initialised = false;

    public function __construct(
        PageManager $pm,
        \ilObjWiki $wiki,
        int $wpg_id = 0,
        string $page_title = "",
        string $lang = "-"
    ) {
        $this->pm = $pm;
        $this->wiki = $wiki;
        $this->wpg_id = $wpg_id;
        $this->page_title = trim($page_title);
        $this->lang = ($lang === "")
            ? "-"
            : $lang;
    }

    protected function init(): void
    {
        if (!$this->initialised) {

            // if nothing given, use start page
            if ($this->wpg_id === 0 && $this->page_title === "") {
                $this->page_title = $this->wiki->getStartPage();
            }

            // if no page id given, get page id from requested page title
            if ($this->wpg_id === 0 && $this->page_title !== "") {
                $this->wpg_id = (int) $this->pm->getPageIdForTitle($this->page_title, $this->lang);
            }

            // check if page exists and belongs to wiki
            if ($this->wpg_id > 0) {
                if (!$this->pm->exists($this->wpg_id, $this->lang)) {
                    throw new \ilWikiException("Wiki page does not exist (" .
                        $this->wpg_id . "," . $this->lang . ")");
                }
                if (!$this->pm->belongsToWiki($this->wpg_id)) {
                    throw new \ilWikiException("Wiki page does not belong to wiki (" .
                        $this->wpg_id . "," . $this->wiki->getId() . ")");
                }
            }
            $this->initialised = true;
        }
    }

    public function getCurrentPageId(): int
    {
        $this->init();
        return $this->wpg_id;
    }

    public function getCurrentPageLanguage(): string
    {
        $this->init();
        return $this->lang;
    }

}
