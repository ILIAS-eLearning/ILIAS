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

    public function getWikiPage(
        int $ref_id,
        int $pg_id,
        int $old_nr = 0,
        string $lang = "-"
    ): \ilWikiPage {
        $wp = new \ilWikiPage(
            $pg_id,
            $old_nr,
            $lang
        );
        $wp->setWikiRefId($ref_id);
        return $wp;
    }

    public function page(
        int $ref_id
    ): PageManager {
        return new PageManager(
            $this->data_service,
            $this->repo_service->page(),
            $this->domain_service->wiki(),
            $this,
            $ref_id
        );
    }

}
