<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Learning progress table: One object, rows: users, columns: properties
 * Example: A course, rows: members, columns: name, status, mark, ...
 * PD, Personal Learning Progress -> UserObjectsProps
 * PD, Learning Progress of Users -> UserAggObjectsProps
 * Crs, Learnign Progress of Participants -> ObjectUsersProps
 * Details -> UserObjectsProps
 * More:
 * PropUsersObjects (Grading Overview in Course)
 * @author       Alex Killing <alex.killing@gmx.de>
 * @version      $Id$
 * @ilCtrl_Calls ilTrObjectUsersPropsTableGUI: ilFormPropertyDispatchGUI
 * @ingroup      ServicesTracking
 */
class ilTrObjectUsersPropsTableGUI extends ilLPTableBaseGUI
{
    protected array $user_fields;
    protected int $in_course = 0;
    protected int $in_group = 0;
    protected bool $has_edit = false;
    protected bool $has_collection = false;
    protected bool $has_multi = false;

    protected int $obj_id;
    protected int $ref_id;
    protected string $type;

    protected ilTree $tree;
    protected ilRbacSystem $rbacsystem;
    protected ilObjectDataCache $ilObjDataCache;
    protected ilObjectDefinition $objDefinition;

    /**
     * Constructor
     */
    public function __construct(
        ?object $a_parent_obj,
        string $a_parent_cmd,
        int $a_obj_id,
        int $a_ref_id,
        bool $a_print_view = false
    ) {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->ilObjDataCache = $DIC['ilObjDataCache'];
        $this->objDefinition = $DIC['objDefinition'];

        $this->setId("troup");
        $this->obj_id = $a_obj_id;
        $this->ref_id = $a_ref_id;
        $this->type = ilObject::_lookupType($a_obj_id);

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
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->parseTitle($a_obj_id, "trac_participants");

        if ($a_print_view) {
            $this->setPrintMode(true);
        }

        if (!$this->getPrintMode()) {
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
            $this->addColumn("", "", 1);
            $this->has_multi = true;
        }

        $labels = $this->getSelectableColumns();
        $first = false;
        foreach ($this->getSelectedColumns() as $c) {
            $first = $c;

            // list cannot be sorted by udf fields (separate query)
            // because of pagination only core fields can be sorted
            $sort_id = (substr($c, 0, 4) == "udf_") ? "" : $c;

            $this->addColumn($labels[$c]["txt"], $sort_id);
        }

        if (!$this->getPrintMode()) {
            $this->addColumn($this->lng->txt("actions"), "");
        }
        $this->setSelectAllCheckbox('uid');
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->setEnableHeader(true);
        $this->setFormAction(
            $this->ctrl->getFormActionByClass(get_class($this))
        );
        $this->setRowTemplate(
            "tpl.object_users_props_row.html",
            "Services/Tracking"
        );
        $this->setEnableTitle(true);
        $this->setShowTemplates(true);
        $this->setExportFormats(array(self::EXPORT_CSV, self::EXPORT_EXCEL));

        if ($first) {
            $this->setDefaultOrderField($first);
            $this->setDefaultOrderDirection("asc");
        }

        $this->initFilter();

        $this->getItems();

        // #13807
        $this->has_edit = ilLearningProgressAccess::checkPermission(
            'edit_learning_progress',
            $this->ref_id
        );
    }

    public function getSelectableColumns(): array
    {
        if ($this->selectable_columns) {
            return $this->selectable_columns;
        }

        $cols = $this->getSelectableUserColumns(
            $this->in_course,
            $this->in_group
        );
        $this->user_fields = $cols[1];
        $this->selectable_columns = $cols[0];

        return $this->selectable_columns;
    }

    /**
     * Get user items
     */
    public function getItems(): void
    {
        $this->determineOffsetAndOrder();
        $additional_fields = $this->getSelectedColumns();

        // only if object is [part of] course/group
        $check_agreement = null;
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

        $tr_data = ilTrQuery::getUserDataForObject(
            $this->ref_id,
            ilUtil::stripSlashes($this->getOrderField()),
            ilUtil::stripSlashes($this->getOrderDirection()),
            ilUtil::stripSlashes($this->getOffset()),
            ilUtil::stripSlashes($this->getLimit()),
            $this->getCurrentFilter(),
            $additional_fields,
            $check_agreement,
            $this->user_fields
        );

        if (count($tr_data["set"]) == 0 && $this->getOffset() > 0) {
            $this->resetOffset();
            $tr_data = ilTrQuery::getUserDataForObject(
                $this->ref_id,
                ilUtil::stripSlashes($this->getOrderField()),
                ilUtil::stripSlashes($this->getOrderDirection()),
                ilUtil::stripSlashes($this->getOffset()),
                ilUtil::stripSlashes($this->getLimit()),
                $this->getCurrentFilter(),
                $additional_fields,
                $check_agreement,
                $this->user_fields
            );
        }

        $this->setMaxCount($tr_data["cnt"]);
        $this->setData($tr_data["set"]);
    }

    public function initFilter(): void
    {
        foreach ($this->getSelectableColumns() as $column => $meta) {
            // no udf!
            switch ($column) {
                case "firstname":
                case "lastname":
                case "mark":
                case "u_comment":
                case "institution":
                case "department":
                case "title":
                case "street":
                case "zipcode":
                case "city":
                case "country":
                case "email":
                case "matriculation":
                case "login":
                    if ($column != "mark" ||
                        ilObjectLP::supportsMark($this->type)) {
                        $item = $this->addFilterItemByMetaType(
                            $column,
                            ilTable2GUI::FILTER_TEXT,
                            true,
                            $meta["txt"]
                        );
                        $this->filter[$column] = $item->getValue();
                    }
                    break;

                case "first_access":
                case "last_access":
                case "create_date":
                case 'status_changed':
                    $item = $this->addFilterItemByMetaType(
                        $column,
                        ilTable2GUI::FILTER_DATETIME_RANGE,
                        true,
                        $meta["txt"]
                    );
                    $this->filter[$column] = $item->getDate();
                    break;

                case "birthday":
                    $item = $this->addFilterItemByMetaType(
                        $column,
                        ilTable2GUI::FILTER_DATE_RANGE,
                        true,
                        $meta["txt"]
                    );
                    $this->filter[$column] = $item->getDate();
                    break;

                case "read_count":
                case "percentage":
                    $item = $this->addFilterItemByMetaType(
                        $column,
                        ilTable2GUI::FILTER_NUMBER_RANGE,
                        true,
                        $meta["txt"]
                    );
                    $this->filter[$column] = $item->getValue();
                    break;

                case "gender":
                    $item = $this->addFilterItemByMetaType(
                        "gender",
                        ilTable2GUI::FILTER_SELECT,
                        true,
                        $meta["txt"]
                    );
                    $item->setOptions(
                        array(
                            "" => $this->lng->txt("trac_all"),
                            "n" => $this->lng->txt("gender_n"),
                            "m" => $this->lng->txt("gender_m"),
                            "f" => $this->lng->txt("gender_f"),
                        )
                    );
                    $this->filter["gender"] = $item->getValue();
                    break;

                case "sel_country":
                    $item = $this->addFilterItemByMetaType(
                        "sel_country",
                        ilTable2GUI::FILTER_SELECT,
                        true,
                        $meta["txt"]
                    );

                    $options = array();
                    foreach (ilCountry::getCountryCodes() as $c) {
                        $options[$c] = $this->lng->txt("meta_c_" . $c);
                    }
                    asort($options);
                    $item->setOptions(
                        array("" => $this->lng->txt("trac_all")) + $options
                    );

                    $this->filter["sel_country"] = $item->getValue();
                    break;

                case "status":
                    $item = $this->addFilterItemByMetaType(
                        "status",
                        ilTable2GUI::FILTER_SELECT,
                        true,
                        $meta["txt"]
                    );
                    $item->setOptions(
                        array("" => $this->lng->txt("trac_all"),
                              ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM + 1 => $this->lng->txt(
                                  ilLPStatus::LP_STATUS_NOT_ATTEMPTED
                              ),
                              ilLPStatus::LP_STATUS_IN_PROGRESS_NUM + 1 => $this->lng->txt(
                                  ilLPStatus::LP_STATUS_IN_PROGRESS
                              ),
                              ilLPStatus::LP_STATUS_COMPLETED_NUM + 1 => $this->lng->txt(
                                  ilLPStatus::LP_STATUS_COMPLETED
                              ),
                              ilLPStatus::LP_STATUS_FAILED_NUM + 1 => $this->lng->txt(
                                  ilLPStatus::LP_STATUS_FAILED
                              )
                        )
                    );
                    $this->filter["status"] = $item->getValue();
                    if (is_numeric($this->filter["status"])) {
                        $this->filter["status"]--;
                    }
                    break;

                case "language":
                    $item = $this->addFilterItemByMetaType(
                        "language",
                        ilTable2GUI::FILTER_LANGUAGE,
                        true
                    );
                    $this->filter["language"] = $item->getValue();
                    break;

                case "spent_seconds":
                    if (ilObjectLP::supportsSpentSeconds($this->type)) {
                        $item = $this->addFilterItemByMetaType(
                            "spent_seconds",
                            ilTable2GUI::FILTER_DURATION_RANGE,
                            true,
                            $meta["txt"]
                        );
                        $this->filter["spent_seconds"]["from"] = $item->getCombinationItem(
                            "from"
                        )->getValueInSeconds();
                        $this->filter["spent_seconds"]["to"] = $item->getCombinationItem(
                            "to"
                        )->getValueInSeconds();
                    }
                    break;
            }
        }
    }

    protected function fillRow(array $a_set): void
    {
        if ($this->has_multi) {
            $this->tpl->setVariable("USER_ID", $a_set["usr_id"]);
        }

        foreach ($this->getSelectedColumns() as $c) {
            if (!(bool) ($a_set["privacy_conflict"] ?? null)) {
                if ($c == 'status' && $a_set[$c] != ilLPStatus::LP_STATUS_COMPLETED_NUM) {
                    $timing = $this->showTimingsWarning(
                        $this->ref_id,
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

                // #7694
                if ($c == 'login' && !$a_set["active"]) {
                    $this->tpl->setCurrentBlock('inactive_bl');
                    $this->tpl->setVariable(
                        'TXT_INACTIVE',
                        $this->lng->txt("inactive")
                    );
                    $this->tpl->parseCurrentBlock();
                }

                $val = $this->parseValue($c, $a_set[$c], $this->type);
            } else {
                if ($c == 'login') {
                    $this->tpl->setCurrentBlock('inactive_bl');
                    $this->tpl->setVariable(
                        'TXT_INACTIVE',
                        $this->lng->txt(
                            "status_no_permission"
                        )
                    );
                    $this->tpl->parseCurrentBlock();
                }

                $val = "&nbsp;";
            }

            $this->tpl->setCurrentBlock("user_field");
            $this->tpl->setVariable("VAL_UF", $val);
            $this->tpl->parseCurrentBlock();
        }

        $this->ctrl->setParameterByClass(
            "illplistofobjectsgui",
            "user_id",
            $a_set["usr_id"]
        );

        if (!$this->getPrintMode() && !(bool) ($a_set["privacy_conflict"] ?? null)) {
            // details for containers and collections
            if ($this->has_collection ||
                $this->objDefinition->isContainer($this->type)) {
                $this->tpl->setCurrentBlock("item_command");
                $this->tpl->setVariable(
                    "HREF_COMMAND",
                    $this->ctrl->getLinkTargetByClass(
                        "illplistofobjectsgui",
                        "userdetails"
                    )
                );
                $this->tpl->setVariable(
                    "TXT_COMMAND",
                    $this->lng->txt('details')
                );
                $this->tpl->parseCurrentBlock();
            }

            if ($this->has_edit) {
                $this->tpl->setCurrentBlock("item_command");
                $this->tpl->setVariable(
                    "HREF_COMMAND",
                    $this->ctrl->getLinkTargetByClass(
                        "illplistofobjectsgui",
                        "edituser"
                    )
                );
                $this->tpl->setVariable("TXT_COMMAND", $this->lng->txt('edit'));
                $this->tpl->parseCurrentBlock();
            }
        }

        $this->ctrl->setParameterByClass("illplistofobjectsgui", 'user_id', '');
    }

    protected function fillHeaderExcel(ilExcel $a_excel, int &$a_row): void
    {
        $labels = $this->getSelectableColumns();
        $cnt = 0;
        foreach ($this->getSelectedColumns() as $c) {
            $a_excel->setCell($a_row, $cnt++, $labels[$c]["txt"]);
        }

        $a_excel->setBold(
            "A" . $a_row . ":" . $a_excel->getColumnCoord($cnt - 1) . $a_row
        );
    }

    protected function fillRowExcel(
        ilExcel $a_excel,
        int &$a_row,
        array $a_set
    ): void {
        $cnt = 0;
        foreach ($this->getSelectedColumns() as $c) {
            if ($c != 'status') {
                $val = $this->parseValue($c, $a_set[$c], $this->type);
            } else {
                $val = ilLearningProgressBaseGUI::_getStatusText(
                    (int) $a_set[$c]
                );
            }
            $a_excel->setCell($a_row, $cnt++, $val);
        }
    }

    protected function fillHeaderCSV(ilCSVWriter $a_csv): void
    {
        $labels = $this->getSelectableColumns();
        foreach ($this->getSelectedColumns() as $c) {
            $a_csv->addColumn($labels[$c]["txt"]);
        }

        $a_csv->addRow();
    }

    protected function fillRowCSV(ilCSVWriter $a_csv, array $a_set): void
    {
        foreach ($this->getSelectedColumns() as $c) {
            if ($c != 'status') {
                $val = $this->parseValue($c, $a_set[$c], $this->type);
            } else {
                $val = ilLearningProgressBaseGUI::_getStatusText(
                    (int) $a_set[$c]
                );
            }
            $a_csv->addColumn($val);
        }

        $a_csv->addRow();
    }
}
