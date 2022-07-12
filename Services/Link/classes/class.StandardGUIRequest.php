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

namespace ILIAS\Link;

use ILIAS\Repository\BaseGUIRequest;

class StandardGUIRequest
{
    use BaseGUIRequest;

    public function __construct(
        \ILIAS\HTTP\Services $http,
        \ILIAS\Refinery\Factory $refinery,
        ?array $passed_query_params = null,
        ?array $passed_post_data = null
    ) {
        $this->initRequest(
            $http,
            $refinery,
            $passed_query_params,
            $passed_post_data
        );
    }

    public function getLinkParentRefId() : int
    {
        return $this->int("link_par_ref_id");
    }

    public function getLinkParentFolderId() : int
    {
        return $this->int("link_par_fold_id");
    }

    public function getLinkParentObjId() : int
    {
        return $this->int("link_par_obj_id");
    }

    public function getLinkType() : string
    {
        return $this->str("link_type");
    }

    public function getMediaPoolFolder() : int
    {
        return $this->int("mep_fold");
    }

    public function getDo() : string
    {
        return $this->str("do");
    }

    public function getSelectedId() : int
    {
        return $this->int("sel_id");
    }

    public function getUserSearchStr() : string
    {
        return $this->str("usr_search_str");
    }
}
