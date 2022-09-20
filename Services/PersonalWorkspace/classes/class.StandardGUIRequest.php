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

namespace ILIAS\PersonalWorkspace;

use ILIAS\Repository;

class StandardGUIRequest
{
    use Repository\BaseGUIRequest;

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

    public function getWspId(): int
    {
        return $this->int("wsp_id");
    }

    public function getRefId(): int
    {
        return $this->int("ref_id");
    }

    public function getPrtId(): int
    {
        return $this->int("prt_id");
    }

    public function getBlogGtp(): int
    {
        return $this->int("gtp");
    }

    public function getBlogEdt(): string
    {
        return $this->str("edt");
    }

    public function getBackUrl(): string
    {
        return $this->str("back_url");
    }

    public function getSelectPar(): string
    {
        return $this->str("select_par");
    }

    /**
     * @return int[]
     */
    public function getItemIds(): array
    {
        $ids = $this->intArray("id");
        if (count($ids) == 0) {
            $id = $this->int("item_ref_id");
            if ($id > 0) {
                $ids = [$id];
            }
        }
        return $ids;
    }

    public function getSortation(): int
    {
        return $this->int("sortation");
    }

    public function getNewType(): string
    {
        return $this->str("new_type");
    }

    public function getUser(): int
    {
        return $this->int("user");
    }

    public function getAction(): string
    {
        return $this->str("action");
    }

    public function getObjId(): int
    {
        return $this->int("obj_id");
    }

    public function getShareId(): int
    {
        return $this->int("shr_id");
    }

    public function getNode(): int
    {
        return $this->int("node");
    }

    public function getPasteExpand(string $mode): string
    {
        return $this->str('paste_' . $mode . '_repexpand');
    }
}
