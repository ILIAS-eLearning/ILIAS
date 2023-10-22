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

namespace ILIAS\Wiki\Wiki;

use ILIAS\Wiki\InternalDomainService;
use ILIAS\Wiki\InternalRepoService;
use ILIAS\Wiki\InternalDataService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class DomainService
{
    protected InternalDomainService $domain_service;
    protected InternalRepoService $repo_service;
    protected InternalDataService $data_service;

    public function __construct(
        InternalDataService $data_service,
        InternalRepoService $repo_service,
        InternalDomainService $domain_service
    ) {
        $this->repo_service = $repo_service;
        $this->data_service = $data_service;
        $this->domain_service = $domain_service;
    }

    public function checkRefId(int $ref_id): void
    {
        $obj_id = \ilObject::_lookupObjId($ref_id);
        if (\ilObject::_lookupType($obj_id) !== "wiki") {
            throw new \ilWikiException("Not a wiki ref id (" . $ref_id . ").");
        }
    }

    public function getObjId(int $ref_id): int
    {
        return \ilObject::_lookupObjId($ref_id);
    }

    public function object(
        int $ref_id
    ): \ilObjWiki {
        $this->checkRefId($ref_id);
        return new \ilObjWiki($ref_id);
    }

    public function translation(int $obj_id): \ilObjectTranslation
    {
        return \ilObjectTranslation::getInstance($obj_id);
    }

    public function getStartingPageId(int $wiki_ref_id): ?int
    {
        $pm = $this->domain_service->page()->page($wiki_ref_id);
        $wiki = $this->object($wiki_ref_id);
        $start_page = $wiki->getStartPage();
        if ($start_page === "") {
            return null;
        }
        return $pm->getPageIdForTitle($start_page);
    }

}
