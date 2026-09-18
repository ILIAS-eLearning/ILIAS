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

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;

class WorkspaceShareTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected \ilWorkspaceAccessHandler|\ilPortfolioAccessHandler $handler,
        protected bool $portfolio_mode,
        protected int $parent_node_id,
        protected array $crs_ids,
        protected array $grp_ids,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd, $portfolio_mode ? false : true);
    }

    protected function getId(): string
    {
        return "workspace_share_" . (int) $this->portfolio_mode;
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt("wsp_shared_resources");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->workspaceShareRetrieval(
            $this->handler,
            $this->portfolio_mode,
            $this->crs_ids,
            $this->grp_ids
        );
    }

    protected function transformRow(array $data_row): array
    {
        $ui_factory = $this->gui->ui()->factory();
        $row = [
            "id" => $data_row["id"],
            "lastname" => $data_row["lastname"],
            "firstname" => $data_row["firstname"],
            "login" => $data_row["login"],
            "acl_date" => new \DateTimeImmutable("@" . $data_row["acl_date"]),
            "title" => $ui_factory->link()->standard(
                $data_row["title"],
                $this->getTitleLink($data_row)
            ),
            "acl_type" => implode(", ", $this->getAclTitles($data_row["acl_type"]))
        ];

        if (!$this->portfolio_mode) {
            $row["obj_type"] = $data_row["obj_type"];
            $row["type_icon"] = $ui_factory->symbol()->icon()->standard($data_row["type"], "");
        }

        return $row;
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();
        $table = $table
            ->textColumn("lastname", $lng->txt("lastname"), true)
            ->textColumn("firstname", $lng->txt("firstname"), true)
            ->textColumn("login", $lng->txt("login"), true);

        if (!$this->portfolio_mode) {
            $table = $table
                ->iconColumn("type_icon", $lng->txt("wsp_shared_object_type"))
                ->textColumn("obj_type", $lng->txt("wsp_shared_object_type"), true);
        }

        $table = $table
            ->dateColumn("acl_date", $lng->txt("wsp_shared_date"), true)
            ->linkColumn("title", $lng->txt("wsp_shared_title"), true)
            ->textColumn("acl_type", $lng->txt("wsp_shared_type"));

        if ($this->portfolio_mode) {
            return $table->singleAction(
                "redirectSendMailToSharer",
                $lng->txt("wsp_send_mail")
            );
        }

        return $table->singleRedirectAction(
            "copyShared",
            $lng->txt("copy"),
            [get_class($this->parent_gui)],
            "copyShared",
            "item_ref_id"
        );
    }

    protected function activeAction(string $action, array $data_row): bool
    {
        return $this->portfolio_mode || ($action === "copyShared" && $data_row["type"] === "file");
    }

    protected function getTitleLink(array $data_row): string
    {
        if ($this->portfolio_mode) {
            return \ilLink::_getStaticLink($data_row["obj_id"], "prtf", true);
        }

        return $this->handler->getGotoLink($data_row["wsp_id"], $data_row["obj_id"]);
    }

    protected function getAclTitles(array $acl_types): array
    {
        $titles = [];
        asort($acl_types);
        foreach ($acl_types as $obj_id) {
            switch ($obj_id) {
                case \ilWorkspaceAccessGUI::PERMISSION_REGISTERED:
                    $titles[] = $this->domain->lng()->txt("wsp_set_permission_registered");
                    break;
                case \ilWorkspaceAccessGUI::PERMISSION_ALL_PASSWORD:
                    $titles[] = $this->domain->lng()->txt("wsp_set_permission_all_password");
                    break;
                case \ilWorkspaceAccessGUI::PERMISSION_ALL:
                    $titles[] = $this->domain->lng()->txt("wsp_set_permission_all");
                    break;
                default:
                    $type = \ilObject::_lookupType($obj_id);
                    $titles[] = $type === "usr"
                        ? \ilUserUtil::getNamePresentation($obj_id, false, true)
                        : \ilObject::_lookupTitle($obj_id);
                    break;
            }
        }

        return $titles;
    }
}
