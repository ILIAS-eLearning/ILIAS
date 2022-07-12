<?php declare(strict_types=1);
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
 
/**
 * Class ilRbacLogTableGUI
 * @author       Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilRbacLogTableGUI:
 * @ingroup      ServicesAccessControl
 */
class ilRbacLogTableGUI extends ilTable2GUI
{
    protected array $operations = [];
    protected array $filter = [];
    protected array $action_map = [];
    private int $ref_id;

    public function __construct(object $a_parent_obj, string $a_parent_cmd, int $a_ref_id)
    {
        global $DIC;

        $this->setId("rbaclog");
        $this->ref_id = $a_ref_id;

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setTitle($this->lng->txt("rbac_log"));
        $this->setLimit(5);

        $this->addColumn($this->lng->txt("date"), "", "15%");
        $this->addColumn($this->lng->txt("name"), "", "10%");
        $this->addColumn($this->lng->txt("login"), "", "10%");
        $this->addColumn($this->lng->txt("action"), "", "15%");
        $this->addColumn($this->lng->txt("rbac_changes"), "", "50%");

        $this->setExternalSegmentation(true);
        $this->setEnableHeader(true);
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate("tpl.rbac_log_row.html", "Services/AccessControl");
        $this->setFilterCommand("applyLogFilter");
        $this->setResetCommand("resetLogFilter");

        $this->action_map = array(ilRbacLog::EDIT_PERMISSIONS => $this->lng->txt("rbac_log_edit_permissions"),
                                  ilRbacLog::MOVE_OBJECT => $this->lng->txt("rbac_log_move_object"),
                                  ilRbacLog::LINK_OBJECT => $this->lng->txt("rbac_log_link_object"),
                                  ilRbacLog::COPY_OBJECT => $this->lng->txt("rbac_log_copy_object"),
                                  ilRbacLog::CREATE_OBJECT => $this->lng->txt("rbac_log_create_object"),
                                  ilRbacLog::EDIT_TEMPLATE => $this->lng->txt("rbac_log_edit_template"),
                                  ilRbacLog::EDIT_TEMPLATE_EXISTING => $this->lng->txt("rbac_log_edit_template_existing"),
                                  ilRbacLog::CHANGE_OWNER => $this->lng->txt("rbac_log_change_owner")
        );

        $this->initFilter();
        $this->getItems($this->ref_id, $this->filter);
    }

    public function initFilter() : void
    {
        $item = $this->addFilterItemByMetaType("action", ilTable2GUI::FILTER_SELECT);
        $item->setOptions(array("" => $this->lng->txt("all")) + $this->action_map);
        $this->filter["action"] = $item->getValue();

        $item = $this->addFilterItemByMetaType("date", ilTable2GUI::FILTER_DATE_RANGE);
        $this->filter["date"] = $item->getDate();
    }

    protected function getItems(int $a_ref_id, array $a_current_filter = null) : void
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];

        $this->determineOffsetAndOrder();

        foreach ($rbacreview->getOperations() as $op) {
            $this->operations[$op["ops_id"]] = $op["operation"];
        }

        // special case: role folder should display root folder entries
        if ($a_ref_id == ROLE_FOLDER_ID) {
            $a_ref_id = ROOT_FOLDER_ID;
        }

        $data = ilRbacLog::getLogItems($a_ref_id, $this->getLimit(), $this->getOffset(), $a_current_filter);

        $this->setData($data["set"]);
        $this->setMaxCount((int) $data["cnt"]);
    }

    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable("DATE", ilDatePresentation::formatDate(new ilDateTime($a_set["created"] ?? 0, IL_CAL_UNIX)));
        $name = ilObjUser::_lookupName((int) ($a_set["user_id"]) ?? 0);
        $this->tpl->setVariable("LASTNAME", $name["lastname"] ?? '');
        $this->tpl->setVariable("FIRSTNAME", $name["firstname"] ?? '');
        $this->tpl->setVariable("LOGIN", $name["login"] ?? '');
        $this->tpl->setVariable("ACTION", $this->action_map[$a_set["action"]] ?? '');

        if ($a_set["action"] == ilRbacLog::CHANGE_OWNER) {
            $user = ilObjUser::_lookupFullname($a_set["data"][0] ?? 0);
            $changes = array(array("action" => $this->lng->txt("rbac_log_changed_owner"), "operation" => $user));
        } elseif ($a_set["action"] == ilRbacLog::EDIT_TEMPLATE) {
            $changes = $this->parseChangesTemplate($a_set["data"] ?? []);
        } else {
            $changes = $this->parseChangesFaPa($a_set["data"] ?? []);
        }

        $this->tpl->setCurrentBlock("changes");
        foreach ($changes as $change) {
            $this->tpl->setVariable("CHANGE_ACTION", $change["action"] ?? '');
            $this->tpl->setVariable("CHANGE_OPERATION", $change["operation"] ?? '');
            $this->tpl->parseCurrentBlock();
        }
    }

    protected function parseChangesFaPa(array $raw) : array
    {
        $result = array();

        $type = ilObject::_lookupType($this->ref_id, true);

        if (isset($raw["src"])) {
            $obj_id = ilObject::_lookupObjectId($raw["src"]);
            if ($obj_id) {
                $result[] = array("action" => $this->lng->txt("rbac_log_source_object"),
                                  "operation" => "<a href=\"" . ilLink::_getLink($raw["src"]) . "\">" . ilObject::_lookupTitle($obj_id) . "</a>"
                );
            }

            // added only
            foreach ($raw["ops"] as $role_id => $ops) {
                foreach ($ops as $op) {
                    $result[] = array("action" => sprintf($this->lng->txt("rbac_log_operation_add"), ilObjRole::_getTranslation(ilObject::_lookupTitle($role_id))),
                        "operation" => $this->getOPCaption($type, $op));
                }
            }
        } elseif (isset($raw["ops"])) {
            foreach ($raw["ops"] as $role_id => $actions) {
                foreach ($actions as $action => $ops) {
                    foreach ((array) $ops as $op) {
                        $result[] = array("action" => sprintf($this->lng->txt("rbac_log_operation_" . $action), ilObjRole::_getTranslation(ilObject::_lookupTitle($role_id))),
                            "operation" => $this->getOPCaption($type, $op));
                    }
                }
            }
        }

        if (isset($raw["inht"])) {
            foreach ($raw["inht"] as $action => $role_ids) {
                foreach ((array) $role_ids as $role_id) {
                    $result[] = array("action" => sprintf($this->lng->txt("rbac_log_inheritance_" . $action), ilObjRole::_getTranslation(ilObject::_lookupTitle($role_id))));
                }
            }
        }

        return $result;
    }

    protected function parseChangesTemplate(array $raw) : array
    {
        $result = array();
        foreach ($raw as $type => $actions) {
            foreach ($actions as $action => $ops) {
                foreach ($ops as $op) {
                    $result[] = array("action" => sprintf(
                        $this->lng->txt("rbac_log_operation_" . $action),
                        $this->lng->txt("obj_" . $type)
                    ),
                                      "operation" => $this->getOPCaption($type, $op)
                    );
                }
            }
        }
        return $result;
    }

    /**
     * @param array|int|string $a_op
     */
    protected function getOPCaption(string $a_type, $a_op) : string
    {
        // #11717
        if (is_array($a_op)) {
            $res = array();
            foreach ($a_op as $op) {
                $res[] = $this->getOPCaption($a_type, $op);
            }
            return implode(", ", $res);
        }

        if (is_numeric($a_op) && isset($this->operations[$a_op])) {
            $op_id = $this->operations[$a_op];
            if (substr($op_id, 0, 7) != "create_") {
                $perm = $this->getTranslationFromPlugin($a_type, $op_id);

                if ($this->notTranslated($perm, $op_id)) {
                    if ($this->lng->exists($a_type . '_' . $op_id . '_short')) {
                        $perm = $this->lng->txt($a_type . '_' . $op_id . '_short');
                    } else {
                        $perm = $this->lng->txt($op_id);
                    }
                }

                return $perm;
            } else {
                $type = substr($op_id, 7, strlen($op_id));
                $perm = $this->getTranslationFromPlugin($type, $op_id);

                if ($this->notTranslated($perm, $op_id)) {
                    $perm = $this->lng->txt("rbac_" . $op_id);
                }

                return $perm;
            }
        }
        return '';
    }

    /**
     * Check the type for plugin and get the translation for op_id
     */
    protected function getTranslationFromPlugin(string $type, string $op_id) : ?string
    {
        global $objDefinition;

        if ($objDefinition->isPlugin($type)) {
            return ilObjectPlugin::lookupTxtById($type, $op_id);
        }
        return null;
    }

    /**
     * Check the op is translated correctly
     */
    protected function notTranslated(?string $perm, string $op_id) : bool
    {
        return is_null($perm) || (strpos($perm, $op_id) !== false);
    }
}
