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

namespace ILIAS\Wiki\Navigation;

use ILIAS\Wiki\InternalDataService;
use ILIAS\Wiki\InternalRepoService;
use ILIAS\Wiki\Navigation\ImportantPageDBRepository;

/**
 * Page manager
 */
class ImportantPageManager
{
    protected int $wiki_ref_id;
    protected \ILIAS\Wiki\Wiki\DomainService $wiki_domain;
    protected \ILIAS\Wiki\Navigation\ImportantPageDBRepository $imp_page_repo;
    protected $ref_id;
    protected InternalDataService $data_service;

    public function __construct(
        InternalDataService $data_service,
        ImportantPageDBRepository $imp_page_repo,
        \ILIAS\Wiki\Wiki\DomainService $wiki_domain,
        int $ref_id
    ) {
        $this->wiki_ref_id = $ref_id;
        $this->data_service = $data_service;
        $this->imp_page_repo = $imp_page_repo;
        $this->wiki_domain = $wiki_domain;
    }

    protected function getWikiId(): int
    {
        return $this->wiki_domain->getObjId($this->wiki_ref_id);
    }

    /**
     * @return iterable<ImportantPage>
     */
    public function getList(): \Iterator
    {
        return $this->imp_page_repo->getList($this->getWikiId());
    }

    /**
     * @deprecated use getList() instead
     */
    public function getListAsArray(): array
    {
        return $this->imp_page_repo->getListAsArray($this->getWikiId());
    }

    public function add(
        int $page_id,
        int $nr = 0,
        int $indent = 0
    ): void {
        $this->imp_page_repo->add(
            $this->getWikiId(),
            $page_id,
            $nr,
            $indent
        );
    }

    public function isImportantPage(
        int $page_id
    ): bool {
        return $this->imp_page_repo->isImportantPage($this->getWikiId(), $page_id);
    }

    public function removeImportantPage(
        int $page_id
    ): void {
        $this->imp_page_repo->removeImportantPage($this->getWikiId(), $page_id);
    }

    public function saveOrderingAndIndentation(
        array $ord,
        array $indent
    ): bool {
        return $this->imp_page_repo->saveOrderingAndIndentation(
            $this->getWikiId(),
            $ord,
            $indent
        );
    }

    public function cloneTo(int $new_wiki_obj_id, array $page_id_map): void
    {
        foreach ($this->getList() as $ip) {
            $this->imp_page_repo->add(
                $new_wiki_obj_id,
                $page_id_map[$ip->getId()],
                $ip->getOrder(),
                $ip->getIndent()
            );
        }
    }

    /**
     * @return int[]
     */
    public function getImportantPageIds(): array
    {
        return $this->imp_page_repo->getImportantPageIds($this->getWikiId());
    }

}
