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

namespace ILIAS\Repository\Deletion;

use ILIAS\Repository\InternalRepoService;
use ILIAS\Repository\InternalDataService;
use ILIAS\Repository\InternalDomainService;

class DomainService
{
    protected static array $instance = [];

    public function __construct(
        protected InternalDataService $data,
        protected InternalRepoService $repo,
        protected InternalDomainService $domain
    ) {
    }

    public function deletion(): Deletion
    {
        $trash_enabled = (bool) $this->domain->settings()->get('enable_trash');
        return self::$instance['deletion'] ??= new Deletion(
            $this->tree(),
            $this->permission(),
            $this->event(),
            $this->object(),
            $trash_enabled
        );
    }

    protected function permission(): PermissionStandardAdapter
    {
        return self::$instance['permission'] ??=
            new PermissionStandardAdapter(
                $this->domain->access(),
                $this->domain->rbac()->admin(),
                $this->tree()
            );
    }

    protected function event(): EventStandardAdapter
    {
        return self::$instance['event'] ??=
            new EventStandardAdapter($this->domain);
    }

    protected function object(): ObjectStandardAdapter
    {
        return self::$instance['object'] ??=
            new ObjectStandardAdapter(0);
    }

    protected function tree(): TreeStandardAdapter
    {
        return self::$instance['tree'] ??=
            new TreeStandardAdapter(
                $this->repo,
                $this->domain->repositoryTree(),
                $this->domain->user()->getId()
            );
    }

}
