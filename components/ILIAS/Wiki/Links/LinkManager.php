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

namespace ILIAS\Wiki\Links;

use ILIAS\Wiki\InternalDataService;
use ILIAS\Wiki\InternalRepoService;
use ILIAS\Wiki\Page\PageDBRepository;
use ILIAS\Wiki\Page\DomainService;
use ILIAS\Wiki\InternalDomainService;
use ILIAS\Wiki\Page\PageManager;
use ILIAS\Wiki\Page\PageInfo;

/**
 * Page manager
 */
class LinkManager
{
    protected int $wiki_id;
    protected PageManager $pm;
    protected \ilLogger $log;
    protected InternalDomainService $domain;
    protected MissingPageDBRepository $missing_page_repo;
    protected InternalDataService $data_service;

    public function __construct(
        InternalDataService $data_service,
        MissingPageDBRepository $missing_page_repo,
        InternalDomainService $domain,
        int $ref_id
    ) {
        $this->missing_page_repo = $missing_page_repo;
        $this->data_service = $data_service;
        $this->domain = $domain;
        $this->log = $this->domain->log();
        $this->pm = $domain->page()->page($ref_id);
        $this->wiki_id = $this->pm->getWikiId();
    }

    public function saveInternalLinksForPage(
        \DOMDocument $domdoc,
        int $page_id,
        string $title,
        string $lang
    ): void {

        $wiki_id = $this->wiki_id;

        // Check, whether ANOTHER page links to this page as a "missing" page
        // (this is the case, when this page is created newly)
        foreach($this->missing_page_repo->getSourcesOfMissingTarget($wiki_id, $title, $lang) as $i) {	// insert internal links instead
            //echo "adding link";
            \ilInternalLink::_saveLink(
                "wpg:pg",
                $i,
                "wpg",
                $page_id,
                0,
                $lang
            );
        }

        // now remove the missing page entries for our $title (since it exists now)
        $this->missing_page_repo->deleteForTarget($wiki_id, $title, $lang);

        // remove the existing "missing page" links for THIS page (they will be re-inserted below)
        $this->missing_page_repo->deleteForSourceId($wiki_id, $page_id, $lang);

        // collect the wiki links of the page
        $xml = $domdoc->saveXML();
        $int_wiki_links = \ilWikiUtil::collectInternalLinks($xml, $wiki_id, true);
        foreach ($int_wiki_links as $wlink) {
            $target_page_id = (int) $this->pm->getPageIdForTitle($wlink, $lang);

            if ($target_page_id > 0) {		// save internal link for existing page
                \ilInternalLink::_saveLink(
                    "wpg:pg",
                    $page_id,
                    "wpg",
                    $target_page_id,
                    0,
                    $lang
                );
            } else {		// save missing link for non-existing page
                $this->missing_page_repo->save(
                    $wiki_id,
                    $page_id,
                    $wlink,
                    $lang
                );
            }
        }
    }

    /**
     * @return iterable<PageInfo>
     */
    public function getLinksToPage(
        int $a_page_id,
        string $lang = "-"
    ): \Iterator {

        $wiki_id = $this->wiki_id;
        if ($lang === "") {
            $lang = "-";
        }
        $sources = \ilInternalLink::_getSourcesOfTarget("wpg", $a_page_id, 0);
        $ids = array();
        foreach ($sources as $source) {
            if ($source["type"] === "wpg:pg" && $source["lang"] === $lang) {
                $ids[] = (int) $source["id"];
            }
        }

        // get wiki page record
        foreach ($this->pm->getInfoOfSelected($ids, $lang) as $p) {
            yield $p;
        }
    }

}
