<?php

declare(strict_types=0);
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
 * name table
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup Services
 */
class ilTrMatrixTableGUI extends ilLPTableBaseGUI
{
    protected int $ref_id;
    protected array $ref_ids = [];
    protected string $type;
    protected int $obj_id;
    protected ?array $obj_ids = null;
    protected array $objective_ids = [];
    protected array $sco_ids = [];
    protected array $subitem_ids = [];
    protected int $in_course = 0;
    protected int $in_group = 0;
    protected array $privacy_fields = [];
    protected array $privacy_cols = [];
    protected array $perc_map = [];
    protected bool $has_multi;

    protected ilTree $tree;
    protected ilRbacSystem $rbacsystem;
    protected ilObjectDataCache $ilObjDataCache;

    /**
     * Constructor
     */
    public function __construct(
        ?object $a_parent_obj,
        string $a_parent_cmd,
        int $ref_id
    ) {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->ilObjDataCache = $DIC['ilObjDataCache'];

        $this->setId("trsmtx_" . $ref_id);
        $this->ref_id = $ref_id;
        $this->obj_id = ilObject::_lookupObjId($ref_id);
        $this->type = ilObject::_lookupType($this->obj_id); // #17188

        $this->in_group = $this->tree->checkForParentType($this->ref_id, "grp");
        if ($this->in_group) {
            $this->in_group = ilObject::_lookupObjId($this->in_group);
        } else {
            $this->in_course = $this->tree->checkForParentType(
                $this->ref_id,
                "crs"
            );
            if ($this->in_course) {
                $this->in_course = ilObject::_lookupObjId($this->in_course);
            }
        }

        $this->lng = $DIC->language();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->initFilter();
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->parseTitle($this->obj_id, "trac_matrix");
        $this->setEnableHeader(true);
        $this->setFormAction(
            $this->ctrl->getFormActionByClass(get_class($this))
        );
        $this->setRowTemplate(
            "tpl.user_object_matrix_row.html",
            "Services/Tracking"
        );
        $this->setDefaultOrderField("login");
        $this->setDefaultOrderDirection("asc");
        $this->setShowTemplates(true);

        // see ilObjCourseGUI::addMailToMemberButton()
        $mail = new ilMail($DIC->user()->getId());
        if ($this->rbacsystem->checkAccess(
            "internal_mail",
            $mail->getMailObjectReferenceId()
        )) {
            $this->addMultiCommand(
                "mailselectedusers",
                $this->lng->txt("send_mail")
            );
        }

        $this->lng->loadLanguageModule('user');
        $this->addMultiCommand(
            'addToClipboard',
            $this->lng->txt('clipboard_add_btn')
        );
        $this->addColumn("", "", '1');
        $this->has_multi = true;

        $this->addColumn($this->lng->txt("login"), "login");

        $labels = $this->getSelectableColumns();
        $selected = $this->getSelectedColumns();
        foreach ($selected as $c) {
            $title = $labels[$c]["txt"];

            if (isset($labels[$c]["no_permission"]) && $labels[$c]["no_permission"]) {
                $title .= " (" . $this->lng->txt("status_no_permission") . ")";
            }

            $tooltip = array();
            if (isset($labels[$c]["icon"])) {
                $alt = $this->lng->txt($labels[$c]["type"] ?? "");
                $icon = '<img class="ilListItemIcon" src="' . $labels[$c]["icon"] . '" alt="' . $alt . '" />';
                if (sizeof($selected) > 5) {
                    $tooltip[] = $title;
                    $title = $icon;
                } else {
                    $title = $icon . ' ' . $title;
                }
                if ($labels[$c]["path"] ?? false) {
                    $tooltip[] = $labels[$c]["path"];
                }
            }

            if (isset($labels[$c]["id"])) {
                $sort_id = $labels[$c]["id"];
            } else {
                // list cannot be sorted by udf fields (separate query)
                $sort_id = (substr($c, 0, 4) == "udf_") ? "" : $c;
            }

            $this->addColumn(
                $title,
                $sort_id,
                "",
                false,
                "",
                implode(" - ", $tooltip)
            );
        }
        $this->setExportFormats(array(self::EXPORT_CSV, self::EXPORT_EXCEL));
    }

    public function initFilter(): void
    {
        $item = $this->addFilterItemByMetaType(
            "name",
            ilTable2GUI::FILTER_TEXT,
            false,
            $this->lng->txt('login')
        );
        $this->filter["name"] = $item->getValue();

        // #14949 - is called before constructor, so we have to do it ourselves
        if ($this->http->wrapper()->query()->has($this->prefix . '_tpl')) {
            $this->filter["name"] = null;
            $this->setFilterValue($item, '');
        }
    }

    public function getSelectableColumns(): array
    {
        $user_cols = $this->getSelectableUserColumns(
            $this->in_course,
            $this->in_group
        );
        $columns = [];
        if ($this->obj_ids === null) {
            // we cannot use the selected columns because they are not ready yet
            // so we use all available columns, should be ok anyways
            $this->obj_ids = $this->getItems(
                array_keys($user_cols[0]),
                $user_cols[1]
            );
        }
        $parent = [];
        if (is_array($this->obj_ids)) {
            $tmp_cols = array();
            foreach ($this->obj_ids as $obj_id) {
                if ($obj_id == $this->obj_id) {
                    $parent = array("txt" => $this->lng->txt("status"),
                                    "default" => true
                    );
                } else {
                    $no_perm = false;

                    $ref_id = $this->ref_ids[$obj_id];
                    if ($ref_id &&
                        !ilLearningProgressAccess::checkPermission(
                            'read_learning_progress',
                            $ref_id
                        )) {
                        $no_perm = true;
                        $this->privacy_cols[] = $obj_id;
                    }

                    $title = $this->ilObjDataCache->lookupTitle((int) $obj_id);
                    $type = $this->ilObjDataCache->lookupType((int) $obj_id);
                    $icon = ilObject::_getIcon((int) $obj_id, "tiny", $type);
                    if ($type == "sess") {
                        $sess = new ilObjSession($obj_id, false);
                        $title = $sess->getPresentationTitle();
                    }

                    // #16453
                    $relpath = null;
                    $path = new ilPathGUI();
                    $path = $path->getPath($this->ref_id, $ref_id);
                    if ($path) {
                        $relpath = $this->lng->txt('path') . ': ' . $path;
                    }

                    $tmp_cols[strtolower($title) . "#~#obj_" . $obj_id] = array(
                        "txt" => $title,
                        "icon" => $icon,
                        "type" => $type,
                        "default" => true,
                        "no_permission" => $no_perm,
                        "path" => $relpath
                    );
                }
            }
            if (sizeof($this->objective_ids)) {
                foreach ($this->objective_ids as $obj_id => $title) {
                    $tmp_cols[strtolower(
                        $title
                    ) . "#~#objtv_" . $obj_id] = array("txt" => $title,
                                                       "default" => true
                    );
                }
            }
            if (sizeof($this->sco_ids)) {
                foreach ($this->sco_ids as $obj_id => $title) {
                    $icon = ilObject::_getIcon($obj_id, "tiny", "sco");
                    $tmp_cols[strtolower(
                        $title
                    ) . "#~#objsco_" . $obj_id] = array("txt" => $title,
                                                        "icon" => $icon,
                                                        "default" => true
                    );
                }
            }
            if (sizeof($this->subitem_ids)) {
                foreach ($this->subitem_ids as $obj_id => $title) {
                    $icon = ilObject::_getIcon(
                        $obj_id,
                        "tiny",
                        ilTrQuery::getSubItemType(
                            $this->obj_id
                        )
                    );
                    $tmp_cols[strtolower(
                        $title
                    ) . "#~#objsub_" . $obj_id] = array("txt" => $title,
                                                        "icon" => $icon,
                                                        "default" => true
                    );
                }
            }

            // alex, 5 Nov 2011: Do not sort SCORM items or "chapters"
            if (!sizeof($this->sco_ids) && !sizeof($this->subitem_ids)) {
                ksort($tmp_cols);
            }
            foreach ($tmp_cols as $id => $def) {
                $id = explode('#~#', $id);
                $columns[$id[1]] = $def;
            }
            unset($tmp_cols);

            if ($parent) {
                $columns["obj_" . $this->obj_id] = $parent;
            }
        }

        unset($user_cols[0]["status"]);
        unset($user_cols[0]["login"]);
        foreach ($user_cols[0] as $col_id => $col_def) {
            if (!isset($columns[$col_id])) {
                // these are all additional fields, no default
                $col_def["default"] = false;
                $columns[$col_id] = $col_def;
            }
        }

        return $columns;
    }

    public function getItems(
        array $a_user_fields,
        array $a_privary_fields = null
    ): array {

        // #17081
        if ($this->restore_filter) {
            $name = $this->restore_filter_values["name"];
            $this->setFilterValue($this->filters[0], $name);
            $this->filter["name"] = $name;
        }

        $collection = ilTrQuery::getObjectIds(
            $this->obj_id,
            $this->ref_id,
            true
        );
        if ($collection["object_ids"]) {
            // we need these for the timing warnings
            $this->ref_ids = $collection["ref_ids"];

            // only if object is [part of] course/group
            $check_agreement = false;
            if ($this->in_course) {
                // privacy (if course agreement is activated)
                $privacy = ilPrivacySettings::getInstance();
                if ($privacy->courseConfirmationRequired()) {
                    $check_agreement = $this->in_course;
                }
            } elseif ($this->in_group) {
                // privacy (if group agreement is activated)
                $privacy = ilPrivacySettings::getInstance();
                if ($privacy->groupConfirmationRequired()) {
                    $check_agreement = $this->in_group;
                }
            }
            $data = ilTrQuery::getUserObjectMatrix(
                $this->ref_id,
                $collection["object_ids"],
                $this->filter["name"] ?? '',
                $a_user_fields,
                $a_privary_fields,
                $check_agreement
            );
            if ($collection["objectives_parent_id"] && $data["users"]) {
                // sub-items: learning objectives
                $objectives = ilTrQuery::getUserObjectiveMatrix(
                    $collection["objectives_parent_id"],
                    $data["users"]
                );

                $this->objective_ids = array();

                foreach ($objectives as $user_id => $inner_objectives) {
                    if (isset($data["set"][$user_id])) {
                        foreach ($inner_objectives as $objective_id => $status) {
                            $obj_id = "objtv_" . $objective_id;
                            $data["set"][$user_id][$obj_id] = $status;

                            if (!in_array($obj_id, $this->objective_ids)) {
                                $this->objective_ids[$objective_id] = ilCourseObjective::lookupObjectiveTitle(
                                    $objective_id
                                );
                            }
                        }
                    }
                }
            }

            // sub-items: SCOs
            if ($collection["scorm"] && $data["set"]) {
                $this->sco_ids = array();
                foreach (array_keys($data["set"]) as $user_id) {
                    foreach ($collection["scorm"]["scos"] as $sco) {
                        if (!in_array($sco, $this->sco_ids)) {
                            $this->sco_ids[$sco] = $collection["scorm"]["scos_title"][$sco];
                        }

                        // alex, 5 Nov 2011: we got users being in failed and in
                        // completed status, I changed the setting in: first check failed
                        // then check completed since failed should superseed completed
                        // (before completed has been checked before failed)
                        $status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
                        if (in_array(
                            $user_id,
                            $collection["scorm"]["failed"][$sco]
                        )) {
                            $status = ilLPStatus::LP_STATUS_FAILED_NUM;
                        } elseif (in_array(
                            $user_id,
                            $collection["scorm"]["completed"][$sco]
                        )) {
                            $status = ilLPStatus::LP_STATUS_COMPLETED_NUM;
                        } elseif (in_array(
                            $user_id,
                            $collection["scorm"]["in_progress"][$sco]
                        )) {
                            $status = ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
                        }

                        $obj_id = "objsco_" . $sco;
                        $data["set"][$user_id][$obj_id] = $status;
                    }
                }
            }

            // sub-items: generic, e.g. lm chapter
            if ($collection["subitems"] && $data["set"]) {
                foreach (array_keys($data["set"]) as $user_id) {
                    foreach ($collection["subitems"]["items"] as $item_id) {
                        $this->subitem_ids[$item_id] = $collection["subitems"]["item_titles"][$item_id];

                        $status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
                        if (in_array(
                            $user_id,
                            $collection["subitems"]["completed"][$item_id]
                        )) {
                            $status = ilLPStatus::LP_STATUS_COMPLETED_NUM;
                        } elseif (is_array(
                            $collection["subitems"]["in_progress"]
                        ) &&
                            in_array(
                                $user_id,
                                $collection["subitems"]["in_progress"][$item_id]
                            )) {
                            $status = ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
                        }

                        $obj_id = "objsub_" . $item_id;
                        $data["set"][$user_id][$obj_id] = $status;
                    }
                }
            }

            // percentage export
            if ($data["set"]) {
                $this->perc_map = array();
                foreach ($data["set"] as $row_idx => $row) {
                    foreach ($row as $column => $value) {
                        if (substr($column, -5) == "_perc") {
                            $obj_id = explode("_", $column);
                            $obj_id = (int) $obj_id[1];

                            // #18673
                            if (!$this->isPercentageAvailable($obj_id) ||
                                !(int) $value) {
                                unset($data["set"][$row_idx][$column]);
                            } else {
                                $this->perc_map[$obj_id] = true;
                            }
                        }
                    }
                }
            }

            $this->setMaxCount($data["cnt"] ?? 0);
            $this->setData($data["set"] ?? []);
            return $collection["object_ids"] ?? [];
        }
        return [];
    }

    protected function fillRow(array $a_set): void
    {
        if ($this->has_multi) {
            $this->tpl->setVariable("USER_ID", $a_set["usr_id"]);
        }

        foreach ($this->getSelectedColumns() as $c) {
            switch ($c) {
                case (substr($c, 0, 4) == "obj_"):
                    $obj_id = substr($c, 4);

                    // object without read-lp-permission
                    if (in_array($obj_id, $this->privacy_cols) ||
                        ($a_set["privacy_conflict"] ?? false)) {
                        $this->tpl->setCurrentBlock("objects");
                        $this->tpl->setVariable("VAL_STATUS", "&nbsp;");
                        $this->tpl->parseCurrentBlock();
                        continue 2;
                    }

                    $status = isset($a_set[$c])
                        ? (int) $a_set[$c]
                        : ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
                    $percentage = isset($a_set[$c . "_perc"])
                        ? (int) $a_set[$c . "_perc"]
                        : null;

                    if ($status != ilLPStatus::LP_STATUS_COMPLETED_NUM) {
                        $timing = $this->showTimingsWarning(
                            $this->ref_ids[$obj_id],
                            $a_set["usr_id"]
                        );
                        if ($timing) {
                            if ($timing !== true) {
                                $timing = ": " . ilDatePresentation::formatDate(
                                    new ilDate($timing, IL_CAL_UNIX)
                                );
                            } else {
                                $timing = "";
                            }
                            $this->tpl->setCurrentBlock('warning_img');
                            $this->tpl->setVariable(
                                'WARNING_IMG',
                                ilUtil::getImagePath(
                                    'time_warn.svg'
                                )
                            );
                            $this->tpl->setVariable(
                                'WARNING_ALT',
                                $this->lng->txt(
                                    'trac_time_passed'
                                ) . $timing
                            );
                            $this->tpl->parseCurrentBlock();
                        }
                    }

                    $this->tpl->setCurrentBlock("objects");
                    $this->tpl->setVariable(
                        "VAL_STATUS",
                        $this->parseValue(
                            "status",
                            (string) $status,
                            ""
                        )
                    );
                    $this->tpl->setVariable(
                        "VAL_PERCENTAGE",
                        $this->parseValue(
                            "percentage",
                            (string) $percentage,
                            ""
                        )
                    );
                    $this->tpl->parseCurrentBlock();
                    break;

                case (substr($c, 0, 6) == "objtv_"):
                case (substr($c, 0, 7) == "objsco_"):
                case (substr($c, 0, 7) == "objsub_"):
                    $status = isset($a_set[$c])
                        ? (int) $a_set[$c]
                        : ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;

                    $this->tpl->setCurrentBlock("objects");
                    if (!($a_set["privacy_conflict"] ?? false)) {
                        $this->tpl->setVariable(
                            "VAL_STATUS",
                            $this->parseValue(
                                "status",
                                (string) $status,
                                ""
                            )
                        );
                    } else {
                        $this->tpl->setVariable("VAL_STATUS", "&nbsp;");
                    }
                    $this->tpl->parseCurrentBlock();
                    break;

                default:
                    $this->tpl->setCurrentBlock("user_field");
                    if (!($a_set["privacy_conflict"] ?? false)) {
                        $this->tpl->setVariable(
                            "VAL_UF",
                            $this->parseValue(
                                $c,
                                $a_set[$c] ?? "",
                                ""
                            )
                        );
                    } else {
                        $this->tpl->setVariable("VAL_UF", "&nbsp;");
                    }
                    $this->tpl->parseCurrentBlock();
                    break;
            }
        }

        // #7694
        if (!$a_set["active"] || ($a_set["privacy_conflict"] ?? false)) {
            $mess = array();
            if ($a_set["privacy_conflict"]) {
                $mess[] = $this->lng->txt("status_no_permission");
            } elseif (!$a_set["active"]) {
                $mess[] = $this->lng->txt("inactive");
            }
            $this->tpl->setCurrentBlock('inactive_bl');
            $this->tpl->setVariable('TXT_INACTIVE', implode(", ", $mess));
            $this->tpl->parseCurrentBlock();
        }

        $login = !($a_set["privacy_conflict"] ?? false)
            ? $a_set["login"]
            : "&nbsp;";
        $this->tpl->setVariable("VAL_LOGIN", $login);
    }

    protected function fillHeaderExcel(ilExcel $a_excel, int &$a_row): void
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];

        $a_excel->setCell($a_row, 0, $this->lng->txt("login"));

        $labels = $this->getSelectableColumns();
        $cnt = 1;
        foreach ($this->getSelectedColumns() as $c) {
            if (substr($c, 0, 4) == "obj_") {
                $obj_id = substr($c, 4);

                $type = $this->ilObjDataCache->lookupType((int) $obj_id);
                if ($DIC['objDefinition']->isPlugin($type)) {
                    $type_text = ilObjectPlugin::lookupTxtById(
                        $type,
                        'obj_' . $type
                    );
                } else {
                    $type_text = $this->lng->txt($type);
                }

                $a_excel->setCell(
                    $a_row,
                    $cnt,
                    "(" . $type_text . ") " . $labels[$c]["txt"]
                );

                if (is_array($this->perc_map) && $this->perc_map[$obj_id]) {
                    $cnt++;
                    $a_excel->setCell(
                        $a_row,
                        $cnt,
                        $this->lng->txt(
                            "trac_percentage"
                        ) . " (%)"
                    );
                }
            } else {
                $a_excel->setCell($a_row, $cnt, $labels[$c]["txt"]);
            }
            $cnt++;
        }

        $a_excel->setBold(
            "A" . $a_row . ":" . $a_excel->getColumnCoord($cnt) . $a_row
        );
    }

    protected function fillRowExcel(
        ilExcel $a_excel,
        int &$a_row,
        array $a_set
    ): void {
        $a_excel->setCell($a_row, 0, $a_set["login"]);

        $cnt = 1;
        foreach ($this->getSelectedColumns() as $c) {
            switch ($c) {
                case (substr($c, 0, 4) == "obj_"):
                    $obj_id = substr($c, 4);
                    $val = ilLearningProgressBaseGUI::_getStatusText(
                        (int) $a_set[$c]
                    );
                    $a_excel->setCell($a_row, $cnt, $val);

                    if (is_array($this->perc_map) && $this->perc_map[$obj_id]) {
                        $cnt++;
                        $perc = (int) $a_set[$c . "_perc"];
                        $perc = !$perc
                            ? null
                            : $perc . "%";
                        $a_excel->setCell($a_row, $cnt, $perc);
                    }
                    break;

                case (substr($c, 0, 6) == "objtv_"):
                case (substr($c, 0, 7) == "objsco_"):
                case (substr($c, 0, 7) == "objsub_"):
                    $val = ilLearningProgressBaseGUI::_getStatusText(
                        (int) $a_set[$c]
                    );
                    $a_excel->setCell($a_row, $cnt, $val);
                    break;

                /* #14142
                case "last_access":
                case "spent_seconds":
                case "status_changed":
                */
                default:
                    $val = $this->parseValue($c, $a_set[$c], "user");
                    $a_excel->setCell($a_row, $cnt, $val);
                    break;

            }
            $cnt++;
        }
    }

    protected function fillHeaderCSV(ilCSVWriter $a_csv): void
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];

        $a_csv->addColumn($this->lng->txt("login"));

        $labels = $this->getSelectableColumns();
        foreach ($this->getSelectedColumns() as $c) {
            if (substr($c, 0, 4) == "obj_") {
                $obj_id = substr($c, 4);

                $type = $this->ilObjDataCache->lookupType((int) $obj_id);
                if ($DIC['objDefinition']->isPlugin($type)) {
                    $type_text = ilObjectPlugin::lookupTxtById(
                        $type,
                        'obj_' . $type
                    );
                } else {
                    $type_text = $this->lng->txt($type);
                }

                $a_csv->addColumn("(" . $type_text . ") " . $labels[$c]["txt"]);

                if (is_array($this->perc_map) && $this->perc_map[$obj_id]) {
                    $a_csv->addColumn(
                        $this->lng->txt("trac_percentage") . " (%)"
                    );
                }
            } else {
                $a_csv->addColumn($labels[$c]["txt"]);
            }
        }

        $a_csv->addRow();
    }

    protected function fillRowCSV(ilCSVWriter $a_csv, array $a_set): void
    {
        $a_csv->addColumn($a_set["login"]);

        foreach ($this->getSelectedColumns() as $c) {
            switch ($c) {
                case (substr($c, 0, 4) == "obj_"):
                    $obj_id = substr($c, 4);
                    $val = ilLearningProgressBaseGUI::_getStatusText(
                        (int) $a_set[$c]
                    );
                    $a_csv->addColumn($val);

                    if (is_array($this->perc_map) && $this->perc_map[$obj_id]) {
                        $perc = (int) $a_set[$c . "_perc"];
                        if (!$perc) {
                            $perc = null;
                        }
                        $a_csv->addColumn((string) $perc);
                    }
                    break;

                case (substr($c, 0, 6) == "objtv_"):
                case (substr($c, 0, 7) == "objsco_"):
                case (substr($c, 0, 7) == "objsub_"):
                    $val = ilLearningProgressBaseGUI::_getStatusText(
                        (int) $a_set[$c]
                    );
                    $a_csv->addColumn($val);
                    break;

                /* #14142
                case "last_access":
                case "spent_seconds":
                case "status_changed":
                */
                default:
                    $val = $this->parseValue($c, $a_set[$c], "user");
                    $a_csv->addColumn($val);
                    break;

            }
        }

        $a_csv->addRow();
    }
}
