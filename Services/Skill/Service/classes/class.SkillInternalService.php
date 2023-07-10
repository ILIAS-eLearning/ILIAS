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
 ********************************************************************
 */

namespace ILIAS\Skill\Service;

use ILIAS\HTTP;
use ILIAS\Refinery;

/**
 * Skill internal service
 * @author famula@leifos.de
 */
class SkillInternalService
{
    /**
     * @var int ref id of skill management administration node
     */
    protected int $skmg_ref_id = 0;
    protected \ilTree $repository_tree;
    protected \ilRbacSystem $rbac_system;
    protected int $usr_id = 0;
    protected HTTP\Services $http;
    protected Refinery\Factory $refinery;

    public function __construct(int $skmg_ref_id, \ilTree $repository_tree, \ilRbacSystem $rbac_system, int $usr_id)
    {
        global $DIC;

        $this->skmg_ref_id = $skmg_ref_id;
        $this->repository_tree = $repository_tree;
        $this->rbac_system = $rbac_system;
        $this->usr_id = $usr_id;
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
    }

    public function repo(): SkillInternalRepoService
    {
        return new SkillInternalRepoService($this->factory());
    }

    public function manager(): SkillInternalManagerService
    {
        return new SkillInternalManagerService(
            $this->skmg_ref_id,
            $this->repository_tree,
            $this->factory()->tree(),
            $this->rbac_system,
            $this->usr_id
        );
    }

    /**
     * Skill service repos
     */
    public function factory(): SkillInternalFactoryService
    {
        return new SkillInternalFactoryService();
    }

    public function gui(
        array $query_params = null,
        array $post_data = null
    ): SkillInternalGUIService {
        return new SkillInternalGUIService(
            $this->http,
            $this->refinery,
            $query_params = null,
            $post_data = null
        );
    }
}
