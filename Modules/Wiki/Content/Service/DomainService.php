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

namespace ILIAS\Wiki\Content;

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

    public function navigation(
        \ilObjWiki $wiki,
        int $wpg_id = 0,
        string $page_title = "",
        string $lang = "-"
    ): NavigationManager {
        return new NavigationManager(
            $this->domain_service->page()->page($wiki->getRefId()),
            $wiki,
            $wpg_id,
            $page_title,
            $lang
        );
    }
}
