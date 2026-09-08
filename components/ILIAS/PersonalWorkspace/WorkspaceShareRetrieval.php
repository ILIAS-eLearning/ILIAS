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

namespace ILIAS\PersonalWorkspace;

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;

class WorkspaceShareRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    public function __construct(
        protected \ilWorkspaceAccessHandler|\ilPortfolioAccessHandler $handler,
        protected bool $portfolio_mode,
        protected array $crs_ids,
        protected array $grp_ids,
        protected \ilLanguage $lng
    ) {
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): \Generator {
        $data = $this->collectData($filter);
        if ($order === null) {
            $data = array_reverse($data);
        } else {
            $data = $this->applyOrder($data, $order);
        }
        $data = $this->applyRange($data, $range);

        foreach ($data as $row) {
            yield $row;
        }
    }

    public function count(
        array $filter = [],
        array $parameters = []
    ): int {
        return count($this->collectData($filter));
    }

    public function isFieldNumeric(string $field): bool
    {
        return in_array($field, ["wsp_id", "obj_id", "owner_id", "acl_date"], true);
    }

    protected function collectData(array $filter): array
    {
        $filter = $this->normalizeFilter($filter);
        $objects = $this->handler->findSharedObjects($filter, $this->crs_ids, $this->grp_ids);
        if (!is_array($objects)) {
            return [];
        }
        $user_data = [];
        $data = [];

        foreach ($objects as $wsp_id => $item) {
            $owner_id = (int) $item["owner"];
            if (!isset($user_data[$owner_id])) {
                $user_data[$owner_id] = \ilObjUser::_lookupName($owner_id);
            }
            if (($user_data[$owner_id]["login"] ?? "") === "") {
                continue;
            }

            $type = (string) ($item["type"] ?? "");
            $data[] = [
                "id" => $this->portfolio_mode
                    ? $owner_id . "_" . (int) $item["obj_id"]
                    : (string) $wsp_id,
                "wsp_id" => (int) $wsp_id,
                "obj_id" => (int) $item["obj_id"],
                "type" => $type,
                "obj_type" => $this->lng->txt("wsp_type_" . $type),
                "title" => (string) $item["title"],
                "owner_id" => $owner_id,
                "lastname" => (string) $user_data[$owner_id]["lastname"],
                "firstname" => (string) $user_data[$owner_id]["firstname"],
                "login" => (string) $user_data[$owner_id]["login"],
                "acl_type" => $item["acl_type"],
                "acl_date" => (int) $item["acl_date"]
            ];
        }

        return $data;
    }

    protected function normalizeFilter(array $filter): array
    {
        if (isset($filter["acl_date"]) && is_array($filter["acl_date"])) {
            $start = $filter["acl_date"]["start"] ?? null;
            $filter["acl_date"] = $start instanceof \DateTimeInterface
                ? new \ilDateTime($start->format("Y-m-d"), IL_CAL_DATE)
                : null;
        }

        return $filter;
    }
}
