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

use ILIAS\Repository\GlobalDICGUIServices;

/**
 * PersonalWorkspace internal ui service
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalGUIService
{
    use GlobalDICGUIServices;

    protected InternalDomainService $domain_service;

    public function __construct(
        InternalDomainService $domain_service
    ) {
        global $DIC;

        $this->initGUIServices($DIC);
        $this->domain_service = $domain_service;
    }

    public function workspaceShareTableBuilder(
        \ilWorkspaceAccessHandler|\ilPortfolioAccessHandler $handler,
        bool $portfolio_mode,
        int $parent_node_id,
        object $parent_gui,
        string $parent_cmd
    ): WorkspaceShareTableBuilder {
        [$crs_ids, $grp_ids] = $this->workspaceShareMembershipIds();

        return new WorkspaceShareTableBuilder(
            $this->domain_service,
            $this,
            $handler,
            $portfolio_mode,
            $parent_node_id,
            $crs_ids,
            $grp_ids,
            $parent_gui,
            $parent_cmd
        );
    }

    public function workspaceAccessTableBuilder(
        \ilWorkspaceAccessHandler|\ilPortfolioAccessHandler $handler,
        int $node_id,
        object $parent_gui,
        string $parent_cmd
    ): WorkspaceAccessTableBuilder {
        return new WorkspaceAccessTableBuilder(
            $this->domain_service,
            $this,
            $handler,
            $node_id,
            $parent_gui,
            $parent_cmd
        );
    }

    public function standardRequest(): StandardGUIRequest
    {
        return new StandardGUIRequest(
            $this->http(),
            $this->domain_service->refinery()
        );
    }

    public function workspaceShareFilter(
        string $filter_id,
        object $parent_gui,
        string $parent_cmd,
        \ilWorkspaceAccessHandler|\ilPortfolioAccessHandler $handler,
        bool $portfolio_mode,
        int $parent_node_id = 0
    ): \ILIAS\Repository\Filter\FilterAdapterGUI {
        $lng = $this->domain_service->lng();
        $filter = $this->filter($filter_id, [get_class($parent_gui)], $parent_cmd);
        $share_id = $this->standardRequest()->getShareId();
        $user = $share_id > 0 ? \ilObjUser::_lookupLogin($share_id) : null;
        $filter = $filter->text(
            "user",
            $lng->txt("wsp_shared_user_filter"),
            true,
            $user ?: null
        );
        $filter = $filter->text("title", $lng->txt("wsp_shared_title"));
        $filter = $filter->duration("acl_date", $lng->txt("wsp_shared_date_filter"), false);

        if (!$portfolio_mode) {
            //$options = ["" => $lng->txt("search_any")];
            foreach (["file", "blog"] as $type) {
                $settings_map = ["blog" => "blogs", "file" => "files"];
                if ($this->domain_service->settings()->get("disable_wsp_" . $settings_map[$type])) {
                    continue;
                }
                $options[$type] = $lng->txt("wsp_type_" . $type);
            }
        } else {
            $options = ["prtf" => $lng->txt("obj_prtf")];
        }
        asort($options);
        $filter = $filter->select("obj_type", $lng->txt("wsp_shared_object_type"), $options);

        $permission_options = [
            /* "" => $lng->txt("search_any"), */
            "user" => $lng->txt("wsp_set_permission_single_user")
        ];
        [$crs_ids, $grp_ids] = $this->workspaceShareMembershipIds();
        if (count($grp_ids)) {
            $permission_options["group"] = $lng->txt("wsp_set_permission_group");
        }
        if (count($crs_ids)) {
            $permission_options["course"] = $lng->txt("wsp_set_permission_course");
        }
        if (!$handler->hasRegisteredPermission($parent_node_id)) {
            $permission_options["registered"] = $lng->txt("wsp_set_permission_registered");
        }
        if ($this->domain_service->settings()->get("enable_global_profiles")) {
            if (!$handler->hasGlobalPasswordPermission($parent_node_id)) {
                $permission_options["password"] = $lng->txt("wsp_set_permission_all_password");
            }
            if (!$handler->hasGlobalPermission($parent_node_id)) {
                $permission_options["all"] = $lng->txt("wsp_set_permission_all");
            }
        }
        $filter = $filter->select("acl_type", $lng->txt("wsp_shared_type"), $permission_options);

        $member_options = ["" => $lng->txt("search_any")];
        foreach ($crs_ids as $crs_id) {
            $member_options[$crs_id] = $lng->txt("obj_crs") . " " . \ilObject::_lookupTitle($crs_id);
        }
        foreach ($grp_ids as $grp_id) {
            $member_options[$grp_id] = $lng->txt("obj_grp") . " " . \ilObject::_lookupTitle($grp_id);
        }
        if (count($member_options) > 1) {
            asort($member_options);
            $filter = $filter->select("crsgrp", $lng->txt("wsp_shared_member_filter"), $member_options);
        }

        return $filter;
    }

    protected function workspaceShareMembershipIds(): array
    {
        $user_id = $this->domain_service->user()->getId();
        return [
            \ilParticipants::_getMembershipByType($user_id, ["crs"]),
            \ilParticipants::_getMembershipByType($user_id, ["grp"])
        ];
    }
}
