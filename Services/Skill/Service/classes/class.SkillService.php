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

namespace ILIAS\Skill\Service;

use ILIAS\Container;
use ILIAS\Survey;
use ILIAS\Test;
use ILIAS\TestQuestionPool;

/**
 * Skill service
 * @author famula@leifos.de
 */
class SkillService implements SkillServiceInterface
{
    /**
     * @var int ref id of skill management administration node
     */
    protected int $skmg_ref_id = 0;
    protected \ilTree $repository_tree;
    protected \ilRbacSystem $rbac_system;
    protected int $usr_id = 0;

    public function __construct()
    {
        global $DIC;

        $this->repository_tree = $DIC->repositoryTree();
        $skmg_obj = current(\ilObject::_getObjectsByType("skmg"));
        if ($skmg_obj) {
            $this->skmg_ref_id = (int) current(\ilObject::_getAllReferences((int) $skmg_obj["obj_id"]));
        }
        $this->rbac_system = $DIC->rbac()->system();
        $this->usr_id = $DIC->user()->getId();
    }

    /**
     * External user service facade
     */
    public function user(int $id): SkillUserService
    {
        return new SkillUserService($id);
    }

    /**
     * External ui service facade
     */
    public function ui(): SkillUIService
    {
        return new SkillUIService();
    }

    /**
     * External tree service facade
     */
    public function tree(): SkillTreeService
    {
        return new SkillTreeService($this->internal());
    }

    /**
     * External profile service facade
     */
    public function profile(): SkillProfileService
    {
        return new SkillProfileService($this->internal());
    }

    /**
     * External personal service facade
     */
    public function personal(): SkillPersonalService
    {
        return new SkillPersonalService($this->internal());
    }

    /**
     * @inheritDoc
     */
    public function internal(): SkillInternalService
    {
        return new SkillInternalService(
            $this->skmg_ref_id,
            $this->repository_tree,
            $this->rbac_system,
            $this->usr_id
        );
    }

    /**
     * Internal service for Skill classes in Container Service
     */
    public function internalContainer(): Container\Skills\SkillInternalService
    {
        return new Container\Skills\SkillInternalService();
    }

    /**
     * Internal service for Skill classes in Survey Module
     */
    /*public function internalSurvey(): Survey\Skills\SkillInternalService
    {
        return new Survey\Skills\SkillInternalService();
    }*/
}
