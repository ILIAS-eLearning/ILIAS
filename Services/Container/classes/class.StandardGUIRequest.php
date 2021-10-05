<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

namespace ILIAS\Container;

use ILIAS\Repository;

class StandardGUIRequest
{
    use Repository\BaseGUIRequest;

    public function __construct(
        \ILIAS\HTTP\Services $http,
        \ILIAS\Refinery\Factory $refinery
    ) {
        $this->initRequest(
            $http,
            $refinery
        );
    }

    public function getRefId() : int
    {
        return $this->int("ref_id");
    }

    public function getItemRefId() : int
    {
        return $this->int("item_ref_id");
    }

    public function getRedirectSource() : string
    {
        return $this->str("redirectSource");
    }

    public function getNewType() : string
    {
        return $this->str("new_type");
    }

    public function getType() : string
    {
        return $this->str("type");
    }

    public function getBaseClass() : string
    {
        return $this->str("baseClass");
    }

    /** @return int [] */
    public function getSelectedIds() : array
    {
        // initially these came per $_GET["item_ref_id"] or $_POST["id"];
        if ($this->int("item_ref_id") > 0) {
            $ids = [$this->int("item_ref_id")];
        } else {
            $ids = $this->intArray("id");
        }
        return $ids;
    }

    public function getCloneSource() : int
    {
        return $this->int("clone_source");
    }

    public function getCmdRefId() : int
    {
        return $this->int("cmdrefid");
    }

    public function getChildRefId() : int
    {
        return $this->int("child_ref_id");
    }

    public function getParentRefId() : int
    {
        return $this->int("parent_ref_id");
    }

    public function getExpand() : int
    {
        return $this->int("expand");
    }

    public function getBlockAction() : string
    {
        return $this->str("act");
    }

    public function getBlockId() : string
    {
        return $this->str("cont_block_id");
    }

    public function getPreviousSession() : int
    {
        return $this->int("crs_prev_sess");
    }

    public function getNextSession() : int
    {
        return $this->int("crs_next_sess");
    }

    public function getObjectiveId() : int
    {
        return $this->int("oobj");
    }

    public function getNodes() : array
    {
        if ($this->int("node") > 0) {
            return [$this->int("node")];
        }
        return $this->intArray("nodes");
    }

    public function getCopyOptions() : array
    {
        return $this->arrayArray("cp_options");
    }

    public function getPositions() : array
    {
        return $this->arrayArray("position");
    }

    public function getTrashIds() : array
    {
        return $this->intArray("trash_id");
    }

    public function getAlreadyRenderedRefIds() : array
    {
        $ids = $this->strArray("ids");
        $ref_ids = array_map(function ($i) {
            $parts = explode("_", $i);
            return $parts[2];
        }, $ids);
        return $ref_ids;
    }

    public function getStartObjPositions() : array
    {
        return $this->intArray("pos");
    }

    public function getStartObjIds() : array
    {
        return $this->intArray("starter");
    }
}
