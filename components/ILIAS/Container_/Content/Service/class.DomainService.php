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

namespace ILIAS\Container\Content;

use ILIAS\Container\InternalRepoService;
use ILIAS\Container\InternalDataService;
use ILIAS\Container\InternalDomainService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class DomainService
{
    protected InternalRepoService $repo_service;
    protected InternalDataService $data_service;
    protected InternalDomainService $domain_service;

    protected ItemSessionRepository $item_repo;
    protected ViewSessionRepository $view_repo;

    public function __construct(
        InternalRepoService $repo_service,
        InternalDataService $data_service,
        InternalDomainService $domain_service
    ) {
        $this->repo_service = $repo_service;
        $this->data_service = $data_service;
        $this->domain_service = $domain_service;
        $this->item_repo = $this->repo_service->content()->item();
        $this->view_repo = $this->repo_service->content()->view();
    }

    public function items(\ilContainer $container): ItemManager
    {
        return new ItemManager(
            $container,
            $this->item_repo
        );
    }

    public function view(): ViewManager
    {
        return new ViewManager(
            $this->view_repo
        );
    }

    /*
    public function access(int $ref_id, int $user_id) : Access\AccessManager
    {
        return new Access\AccessManager(
            $this,
            $this->access,
            $ref_id,
            $user_id
        );
    }*/
}
