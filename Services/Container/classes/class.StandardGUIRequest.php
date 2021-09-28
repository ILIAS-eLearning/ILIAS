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
}
