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

namespace ILIAS\Wiki\Page;

use ILIAS\Wiki\InternalDataService;
use ILIAS\Wiki\InternalRepoService;

/**
 * Page manager
 */
class PageManager
{
    protected DomainService $page_domain;
    protected \ILIAS\Wiki\Wiki\DomainService $wiki_domain;
    protected PageDBRepository $page_repo;
    protected int $wiki_ref_id;
    protected \ilObjWiki $wiki;
    protected int $ref_id;
    protected InternalDataService $data_service;

    public function __construct(
        InternalDataService $data_service,
        PageDBRepository $page_repo,
        \ILIAS\Wiki\Wiki\DomainService $wiki_domain,
        DomainService $page_domain,
        int $ref_id
    ) {
        $this->wiki_ref_id = $ref_id;
        $this->data_service = $data_service;
        $this->page_repo = $page_repo;
        $this->wiki_domain = $wiki_domain;
        $this->page_domain = $page_domain;
        $this->wiki = $this->wiki_domain->object($ref_id);
    }

    public function getWikiId(): int
    {
        return $this->wiki_domain->getObjId($this->wiki_ref_id);
    }

    public function createWikiPage(
        string $title,
        int $wpg_id = 0,
        string $lang = "-",
        int $template_page = 0
    ): int {
        if ($lang === "") {
            $lang = "-";
        }
        // todo check if $wpg_id is given if lang is set
        // todo check if $wpg_id is not given if lang is not set
        // todo check if $wpg_id belongs to wiki, if given
        // todo check if page (id/lang) does not exist already
        // todo check if title is not empty

        // check if template has to be used
        if ($template_page === 0) {
            if (!$this->wiki->getEmptyPageTemplate()) {
                $wt = new \ilWikiPageTemplate($this->getWikiId());
                $ts = $wt->getAllInfo(\ilWikiPageTemplate::TYPE_NEW_PAGES, $lang);
                if (count($ts) === 1) {
                    $t = current($ts);
                    $template_page = (int) $t["wpage_id"];
                }
            }
        }

        // master language
        if ($lang === "-") {
            $page = new \ilWikiPage(0);
            $page->setWikiId($this->getWikiId());
            $page->setWikiRefId($this->wiki_ref_id);
            $page->setTitle(\ilWikiUtil::makeDbTitle($title));
            if ($this->wiki->getRating() && $this->wiki->getRatingForNewPages()) {
                $page->setRating(true);
            }
            // needed for notification
            $page->create();
        } else {
            $orig_page = $this->page_domain->getWikiPage($this->wiki_ref_id, $wpg_id, 0, "-");
            $orig_page->copyPageToTranslation($lang);

            $page = $this->page_domain->getWikiPage($this->wiki_ref_id, $wpg_id, 0, $lang);
            $page->setTitle(\ilWikiUtil::makeDbTitle($title));
            $page->update();
        }

        // copy template into new page
        if ($template_page > 0) {
            $t_page = $this->page_domain->getWikiPage($this->wiki_ref_id, $template_page, 0, $lang);
            $t_page->copy(
                $page->getId(),
                "",
                0,
                false,
                0,
                false
            );

            // #15718
            if ($lang === "-") {
                \ilAdvancedMDValues::_cloneValues(
                    0,
                    $this->getWikiId(),
                    $this->getWikiId(),
                    "wpg",
                    $template_page,
                    $page->getId()
                );
            }
        }
        return $page->getId();
    }

    /**
     * @return iterable<Page>
     */
    public function getWikiPages(string $lang = "-"): \Iterator
    {
        return $this->page_repo->getWikiPages(
            $this->getWikiId(),
            $lang
        );
    }

    /**
     * @return iterable<PageInfo>
     */
    public function getInfoOfSelected(array $ids, string $lang = "-"): \Iterator
    {
        return $this->page_repo->getInfoOfSelected(
            $this->getWikiId(),
            $ids,
            $lang
        );
    }

    /**
     * @return iterable<Page>
     */
    public function getMasterPagesWithoutTranslation(string $trans): \Iterator
    {
        return $this->page_repo->getMasterPagesWithoutTranslation(
            $this->getWikiId(),
            $trans
        );
    }

    /**
     * @return iterable<PageInfo>
     */
    public function getAllPagesInfo(): \Iterator
    {
        return $this->page_repo->getAllPagesInfo(
            $this->getWikiId()
        );
    }

    /**
     * @return iterable<PageInfo>
     */
    public function getRecentChanges(): \Iterator
    {
        return $this->page_repo->getRecentChanges(
            $this->getWikiId()
        );
    }

    /**
     * @return iterable<PageInfo>
     */
    public function getNewPages(): \Iterator
    {
        return $this->page_repo->getNewPages(
            $this->getWikiId()
        );
    }

    /**
     * @return iterable<PageInfo>
     */
    public function getPopularPages(): \Iterator
    {
        return $this->page_repo->getPopularPages(
            $this->getWikiId()
        );
    }

    /**
     * @return string[]
     */
    public function getLanguages(int $wpg_id): array
    {
        return $this->page_repo->getLanguages($wpg_id);
    }

    /**
     * @return iterable<PageInfo>
     */
    public function getOrphanedPages(): \Iterator
    {
        $starting_page_id = $this->wiki_domain->getStartingPageId($this->wiki_ref_id);
        foreach ($this->getAllPagesInfo() as $pi) {
            // find wiki page sources that link to page
            $sources = \ilInternalLink::_getSourcesOfTarget("wpg", $pi->getId(), 0);
            $ids = [];
            foreach ($sources as $source) {
                if ($source["type"] === "wpg:pg") {
                    $ids[] = $source["id"];
                }
            }

            // cross check existence of sources in il_wiki_page
            if (count($ids) === 0 || !$this->page_repo->doesAtLeastOnePageExist($this->getWikiId(), $ids)) {
                continue;
            }

            if ($pi->getId() !== $starting_page_id) {
                yield $pi;
            }
        }
    }

    public function getPageIdForTitle(
        string $title,
        string $lang = "-"
    ): ?int {
        return $this->page_repo->getPageIdForTitle(
            $this->getWikiId(),
            $title,
            $lang
        );
    }

    public function getPermaLink(int $id, string $lang = "-"): string
    {
        $lang = (!in_array($lang, ["", "-"]))
            ? "_" . $lang
            : "";
        return \ilLink::_getStaticLink(
            null,
            "wiki",
            true,
            "wpage_" . $id . "_" . $this->wiki_ref_id . $lang
        );
    }

    public function getPermaLinkByTitle(string $title, string $lang = "-"): string
    {
        $id = $this->getPageIdForTitle($title, $lang);
        if (!is_null($id)) {
            return $this->getPermaLink($id, $lang);
        }
        return \ilLink::_getStaticLink(
            $this->wiki_ref_id,
            "wiki"
        );
    }

    public function exists(int $id, string $lang = "-"): bool
    {
        return $this->page_repo->exists($id, $lang);
    }

    public function existsByTitle(
        string $title,
        string $lang = "-"
    ): bool {
        return $this->page_repo->existsByTitle($this->getWikiId(), $title, $lang);
    }

    public function getTitle(int $id, string $lang = "-"): string
    {
        return $this->page_repo->getTitle($id, $lang);
    }

    public function belongsToWiki(
        int $id
    ): bool {
        return $this->page_repo->getWikiIdByPageId($id) === $this->getWikiId();
    }

}
