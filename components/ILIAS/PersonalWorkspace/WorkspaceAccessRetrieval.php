<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of the license along with the
 * source code, too.
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\PersonalWorkspace;

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;

class WorkspaceAccessRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    public function __construct(
        protected \ilWorkspaceAccessHandler|\ilPortfolioAccessHandler $handler,
        protected int $node_id,
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
        $data = $this->collectData();
        $data = $this->applyOrder($data, $order);
        $data = $this->applyRange($data, $range);

        foreach ($data as $row) {
            yield $row;
        }
    }

    public function count(array $filter, array $parameters): int
    {
        return count($this->collectData());
    }

    public function isFieldNumeric(string $field): bool
    {
        return $field === "id";
    }

    protected function collectData(): array
    {
        $data = [];

        foreach ($this->handler->getPermissions($this->node_id) as $obj_id) {
            $title = null;
            $caption = "";
            $type_txt = "";

            switch ($obj_id) {
                case \ilWorkspaceAccessGUI::PERMISSION_REGISTERED:
                    $caption = $this->lng->txt("wsp_set_permission_registered");
                    $title = "0" . $caption;
                    break;

                case \ilWorkspaceAccessGUI::PERMISSION_ALL_PASSWORD:
                    $caption = $this->lng->txt("wsp_set_permission_all_password");
                    $title = "0" . $caption;
                    break;

                case \ilWorkspaceAccessGUI::PERMISSION_ALL:
                    $caption = $this->lng->txt("wsp_set_permission_all");
                    $title = "0" . $caption;
                    break;

                default:
                    $type = \ilObject::_lookupType($obj_id);
                    if ($type === "") {
                        continue 2;
                    }

                    $type_txt = $this->lng->txt("obj_" . $type);
                    if ($type !== "usr") {
                        $title = $caption = \ilObject::_lookupTitle($obj_id);
                    } else {
                        $caption = \ilUserUtil::getNamePresentation($obj_id, false, true);
                        $title = strip_tags($caption);
                    }
                    break;
            }

            if ($title) {
                $data[] = [
                    "id" => $obj_id,
                    "title" => $title,
                    "caption" => $caption,
                    "type" => $type_txt
                ];
            }
        }

        return $data;
    }
}
