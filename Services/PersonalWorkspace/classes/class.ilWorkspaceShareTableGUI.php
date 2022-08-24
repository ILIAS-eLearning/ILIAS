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

use ILIAS\PersonalWorkspace\StandardGUIRequest;

/**
 * Workspace share handler table GUI class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 */
class ilWorkspaceShareTableGUI extends ilTable2GUI
{
    protected ilSetting $settings;
    protected ilObjUser $user;
    /**
     * @var ilWorkspaceAccessHandler|ilPortfolioAccessHandler
     */
    protected $handler;
    protected ?int $parent_node_id = null;
    protected array $filter;
    protected array $crs_ids;
    protected array $grp_ids;
    protected bool $portfolio_mode = false;
    protected StandardGUIRequest $std_request;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        $a_handler,
        int $a_parent_node_id = null,
        bool $a_load_data = false
    ) {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->settings = $DIC->settings();
        $this->user = $DIC->user();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $this->handler = $a_handler;
        $this->std_request = new StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

        $this->parent_node_id = 0;

        if (stristr(get_class($a_parent_obj), "portfolio")) {
            $this->parent_node_id = (int) $a_parent_node_id;
            $this->portfolio_mode = true;
        }

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setId("il_tbl_wspsh" . (int) $this->portfolio_mode);

        $this->setTitle($lng->txt("wsp_shared_resources"));

        $this->addColumn($this->lng->txt("lastname"), "lastname");
        $this->addColumn($this->lng->txt("firstname"), "firstname");
        $this->addColumn($this->lng->txt("login"), "login");

        if (!$this->portfolio_mode) {
            $this->addColumn($this->lng->txt("wsp_shared_object_type"), "obj_type");
        }

        $this->addColumn($this->lng->txt("wsp_shared_date"), "acl_date");
        $this->addColumn($this->lng->txt("wsp_shared_title"), "title");
        $this->addColumn($this->lng->txt("wsp_shared_type"));

        if (!$this->portfolio_mode) {
            $this->addColumn($this->lng->txt("action"));
        }

        $this->setDefaultOrderField("acl_date");
        $this->setDefaultOrderDirection("desc");

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.shared_row.html", "Services/PersonalWorkspace");

        $this->setDisableFilterHiding(true);
        $this->setResetCommand("resetsharefilter", $this->lng->txt("wsp_shared_filter_reset_button"));
        $this->setFilterCommand("applysharefilter", $this->lng->txt("wsp_shared_filter_button"));

        $this->initFilter();

        // reset will remove all filters
        if ($this->portfolio_mode &&
            !$this->filter["obj_type"]) {
            $this->filter["obj_type"] = "prtf";
        }

        // incoming request:  check for validity
        if ($a_load_data) {
            /*
            if(($this->filter["user"] && strlen($this->filter["user"]) > 3) ||
                ($this->filter["title"] && strlen($this->filter["title"]) > 3) ||
                $this->filter["acl_date"] ||
                $this->filter["obj_type"] ||
                $this->filter["acl_type"] ||
                $this->filter["crsgrp"])
            {
            */

            // #16630
            $this->importData();
            return;
        } else {
            $main_tpl->setOnScreenMessage('info', $lng->txt("wsp_shared_mandatory_filter_info"));
        }

        // initial state: show filters only
        $this->disable("header");
        $this->disable("content");
    }

    public function initFilter(): void
    {
        $lng = $this->lng;
        $ilSetting = $this->settings;
        $ilUser = $this->user;

        $this->crs_ids = ilParticipants::_getMembershipByType($ilUser->getId(), ["crs"]);
        $this->grp_ids = ilParticipants::_getMembershipByType($ilUser->getId(), ["grp"]);

        $lng->loadLanguageModule("search");

        $item = $this->addFilterItemByMetaType("user", self::FILTER_TEXT, false, $lng->txt("wsp_shared_user_filter"));
        $this->filter["user"] = $item->getValue();

        // incoming back link (shared)
        $form_sess = ilSession::get("form_" . $this->getId());
        if ($this->std_request->getShareId() &&
            !is_array($form_sess) && // #17747
            !$this->filter["user"]) {
            $this->filter["user"] = ilObjUser::_lookupName($this->std_request->getShareId());
            $this->filter["user"] = $this->filter["user"]["login"];
            $item->setValue($this->filter["user"]);
        }

        $item = $this->addFilterItemByMetaType("title", self::FILTER_TEXT, false, $lng->txt("wsp_shared_title"));
        $this->filter["title"] = $item->getValue();

        $item = $this->addFilterItemByMetaType("acl_date", self::FILTER_DATE, false, $lng->txt("wsp_shared_date_filter"));
        $this->filter["acl_date"] = $item->getDate();

        if (!$this->portfolio_mode) {
            // see ilPersonalWorkspaceGUI::renderToolbar
            $options = array("" => $lng->txt("search_any"));
            $settings_map = array("blog" => "blogs",
                "file" => "files");
            // see ilObjWorkspaceFolderTableGUI
            foreach (array("file", "blog") as $type) {
                if (isset($settings_map[$type]) && $ilSetting->get("disable_wsp_" . $settings_map[$type])) {
                    continue;
                }
                $options[$type] = $lng->txt("wsp_type_" . $type);
            }
        } else {
            $options = array("prtf" => $lng->txt("obj_prtf"));
        }
        if (count($options) > 0) {
            asort($options);
            $item = $this->addFilterItemByMetaType("obj_type", self::FILTER_SELECT, false, $lng->txt("wsp_shared_object_type"));
            $item->setOptions($options);
            $this->filter["obj_type"] = $item->getValue();
        }

        // see ilWorkspaceAccessGUI::share
        $options = array();
        $options["user"] = $lng->txt("wsp_set_permission_single_user");

        if (sizeof($this->grp_ids)) {
            $options["group"] = $lng->txt("wsp_set_permission_group");
        }

        if (sizeof($this->crs_ids)) {
            $options["course"] = $lng->txt("wsp_set_permission_course");
        }

        if (!$this->handler->hasRegisteredPermission($this->parent_node_id)) {
            $options["registered"] = $lng->txt("wsp_set_permission_registered");
        }

        if ($ilSetting->get("enable_global_profiles")) {
            if (!$this->handler->hasGlobalPasswordPermission($this->parent_node_id)) {
                $options["password"] = $this->lng->txt("wsp_set_permission_all_password");
            }

            if (!$this->handler->hasGlobalPermission($this->parent_node_id)) {
                $options["all"] = $this->lng->txt("wsp_set_permission_all");
            }
        }

        if (count($options) > 0) {
            $item = $this->addFilterItemByMetaType("acl_type", self::FILTER_SELECT, false, $lng->txt("wsp_shared_type"));
            $item->setOptions(array("" => $lng->txt("search_any")) + $options);
            $this->filter["acl_type"] = $item->getValue();
        }

        if (sizeof($this->crs_ids) || sizeof($this->grp_ids)) {
            $options = array();
            foreach ($this->crs_ids as $crs_id) {
                $options[$crs_id] = $lng->txt("obj_crs") . " " . ilObject::_lookupTitle($crs_id);
            }
            foreach ($this->grp_ids as $grp_id) {
                $options[$grp_id] = $lng->txt("obj_grp") . " " . ilObject::_lookupTitle($grp_id);
            }
            asort($options);
            $item = $this->addFilterItemByMetaType("crsgrp", self::FILTER_SELECT, false, $lng->txt("wsp_shared_member_filter"));
            $item->setOptions(array("" => $lng->txt("search_any")) + $options);
            $this->filter["crsgrp"] = $item->getValue();
        }
    }

    protected function importData(): void
    {
        $lng = $this->lng;

        $data = array();

        $user_data = array();

        $objects = $this->handler->findSharedObjects($this->filter, $this->crs_ids, $this->grp_ids);
        if ($objects) {
            foreach ($objects as $wsp_id => $item) {
                if (!isset($user_data[$item["owner"]])) {
                    $user_data[$item["owner"]] = ilObjUser::_lookupName($item["owner"]);
                }

                // #18535 - deleted user?
                if (!$user_data[$item["owner"]]["login"]) {
                    continue;
                }
                $data[] = array(
                    "wsp_id" => $wsp_id,
                    "obj_id" => $item["obj_id"],
                    "type" => $item["type"] ?? "",
                    "obj_type" => $lng->txt("wsp_type_" . ($item["type"] ?? "")),
                    "title" => $item["title"],
                    "owner_id" => $item["owner"],
                    "lastname" => $user_data[$item["owner"]]["lastname"],
                    "firstname" => $user_data[$item["owner"]]["firstname"],
                    "login" => $user_data[$item["owner"]]["login"],
                    "acl_type" => $item["acl_type"],
                    "acl_date" => $item["acl_date"]
                );
            }
        }

        $this->setData($data);
    }

    /**
     * @param string[] $a_set
     */
    protected function fillRow(array $a_set): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $this->tpl->setVariable("LASTNAME", $a_set["lastname"]);
        $this->tpl->setVariable("FIRSTNAME", $a_set["firstname"]);
        $this->tpl->setVariable("LOGIN", $a_set["login"]);

        $this->tpl->setVariable("TITLE", $a_set["title"]);

        if (!$this->portfolio_mode) {
            $this->tpl->setVariable("TYPE", $a_set["obj_type"]);
            $this->tpl->setVariable("ICON_ALT", $a_set["obj_type"]);
            $this->tpl->setVariable("ICON", ilObject::_getIcon(0, "tiny", $a_set["type"]));

            $url = $this->handler->getGotoLink($a_set["wsp_id"], $a_set["obj_id"]);
        } else {
            $url = ilLink::_getStaticLink($a_set["obj_id"], "prtf", true);
        }
        $this->tpl->setVariable("URL_TITLE", $url);

        $this->tpl->setVariable(
            "ACL_DATE",
            ilDatePresentation::formatDate(new ilDateTime($a_set["acl_date"], IL_CAL_UNIX))
        );

        asort($a_set["acl_type"]);
        foreach ($a_set["acl_type"] as $obj_id) {
            // see ilWorkspaceAccessTableGUI
            switch ($obj_id) {
                case ilWorkspaceAccessGUI::PERMISSION_REGISTERED:
                    $title = $icon_alt = $this->lng->txt("wsp_set_permission_registered");
                    $type = "registered";
                    break;

                case ilWorkspaceAccessGUI::PERMISSION_ALL_PASSWORD:
                    $title = $icon_alt = $this->lng->txt("wsp_set_permission_all_password");
                    $type = "all_password";
                    break;

                case ilWorkspaceAccessGUI::PERMISSION_ALL:
                    $title = $icon_alt = $this->lng->txt("wsp_set_permission_all");
                    $type = "all_password";
                    break;

                default:
                    $type = ilObject::_lookupType($obj_id);
                    /*
                    $icon = ilUtil::getTypeIconPath($type, null, "tiny");
                    $icon_alt = $this->lng->txt("obj_".$type);
                    */
                    if ($type != "usr") {
                        $title = ilObject::_lookupTitle($obj_id);
                    } else {
                        $title = ilUserUtil::getNamePresentation($obj_id, false, true);
                    }
                    break;
            }

            /* #17758
            if($icon)
            {
                $this->tpl->setCurrentBlock("acl_type_icon_bl");
                $this->tpl->setVariable("ACL_ICON", $icon);
                $this->tpl->setVariable("ACL_ICON_ALT", $icon_alt);
                $this->tpl->parseCurrentBlock();
            }
            */

            $this->tpl->setCurrentBlock("acl_type_bl");
            $this->tpl->setVariable("ACL_TYPE", $title);
            $this->tpl->parseCurrentBlock();
        }

        if (!$this->portfolio_mode) {
            // files may be copied to own workspace
            if ($a_set["type"] == "file") {
                $ilCtrl->setParameter(
                    $this->parent_obj,
                    "wsp_id",
                    $this->parent_node_id
                );
                $ilCtrl->setParameter(
                    $this->parent_obj,
                    "item_ref_id",
                    $a_set["wsp_id"]
                );
                $url = $ilCtrl->getLinkTarget($this->parent_obj, "copyshared");

                $this->tpl->setCurrentBlock("action_bl");
                $this->tpl->setVariable("URL_ACTION", $url);
                $this->tpl->setVariable("ACTION", $lng->txt("copy"));
                $this->tpl->parseCurrentBlock();
            } else {
                $this->tpl->touchBlock("action_col_bl");
            }
        }
    }
}
