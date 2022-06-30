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

/**
 * Build table list for objects of given user
 * @author       Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version      $Id$
 * @ilCtrl_Calls ilTrUserObjectsPropsTableGUI: ilFormPropertyDispatchGUI
 * @ingroup      ServicesTracking
 */
class ilTrUserObjectsPropsTableGUI extends ilLPTableBaseGUI
{
    protected string $type;
    protected int $ref_id;
    protected int $obj_id;
    protected int $user_id;

    protected ilAccessHandler $access;
    protected ilRbacSystem $rbacsystem;

    /**
     * Constructor
     */
    public function __construct(
        ?object $a_parent_obj,
        string $a_parent_cmd,
        int $a_user_id,
        int $a_obj_id,
        int $a_ref_id,
        bool $a_print_view = false
    ) {
        global $DIC;

        $this->setId("truop");
        $this->user_id = $a_user_id;
        $this->obj_id = $a_obj_id;
        $this->type = ilObject::_lookupType($this->obj_id);
        $this->ref_id = $a_ref_id;

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setLimit(9999);

        $this->parseTitle($this->obj_id, "details", $this->user_id);

        if ($a_print_view) {
            $this->setPrintMode(true);
        }

        $this->addColumn($this->lng->txt("title"), "title");

        foreach ($this->getSelectedColumns() as $c) {
            $l = $c;
            if (in_array(
                $l,
                array("last_access",
                      "first_access",
                      "read_count",
                      "spent_seconds",
                      "mark",
                      "status",
                      "percentage"
                )
            )) {
                $l = "trac_" . $l;
            }
            if ($l == "u_comment") {
                $l = "trac_comment";
            }
            $this->addColumn($this->lng->txt($l), $c);
        }

        if (!$this->getPrintMode()) {
            $this->addColumn($this->lng->txt("actions"), "");
        }

        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->setEnableHeader(true);
        $this->setFormAction(
            $this->ctrl->getFormActionByClass(get_class($this))
        );
        $this->setRowTemplate(
            "tpl.user_objects_props_row.html",
            "Services/Tracking"
        );
        $this->setEnableTitle(true);
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");
        $this->setShowTemplates(true);
        $this->setExportFormats(array(self::EXPORT_CSV, self::EXPORT_EXCEL));
        $this->initFilter();
        $this->getItems();
    }

    public function getSelectableColumns() : array
    {
        // default fields
        $cols = array();

        $tracking = new ilObjUserTracking();
        if ($tracking->hasExtendedData(
            ilObjUserTracking::EXTENDED_DATA_LAST_ACCESS
        )) {
            $cols["first_access"] = array(
                "txt" => $this->lng->txt("trac_first_access"),
                "default" => true
            );
            $cols["last_access"] = array(
                "txt" => $this->lng->txt("trac_last_access"),
                "default" => true
            );
        }
        if ($tracking->hasExtendedData(
            ilObjUserTracking::EXTENDED_DATA_READ_COUNT
        )) {
            $cols["read_count"] = array(
                "txt" => $this->lng->txt("trac_read_count"),
                "default" => true
            );
        }
        if ($tracking->hasExtendedData(
            ilObjUserTracking::EXTENDED_DATA_SPENT_SECONDS
        )) {
            $cols["spent_seconds"] = array(
                "txt" => $this->lng->txt("trac_spent_seconds"),
                "default" => true
            );
        }

        // #15334 - parent object does not matter, sub-objects may have percentage
        $cols["percentage"] = array(
            "txt" => $this->lng->txt("trac_percentage"),
            "default" => true
        );

        $cols["status"] = array(
            "txt" => $this->lng->txt("trac_status"),
            "default" => true
        );
        $cols["mark"] = array(
            "txt" => $this->lng->txt("trac_mark"),
            "default" => true
        );
        $cols["u_comment"] = array(
            "txt" => $this->lng->txt("trac_comment"),
            "default" => false
        );

        return $cols;
    }

    public function getItems()
    {
        $this->determineOffsetAndOrder();
        $additional_fields = $this->getSelectedColumns();

        $tr_data = ilTrQuery::getObjectsDataForUser(
            $this->user_id,
            $this->obj_id,
            $this->ref_id,
            ilUtil::stripSlashes($this->getOrderField()),
            ilUtil::stripSlashes($this->getOrderDirection()),
            ilUtil::stripSlashes($this->getOffset()),
            ilUtil::stripSlashes($this->getLimit()),
            $this->filter,
            $additional_fields,
            $this->filter["view_mode"]
        );

        if (count($tr_data["set"]) == 0 && $this->getOffset() > 0) {
            $this->resetOffset();
            $tr_data = ilTrQuery::getObjectsDataForUser(
                $this->user_id,
                $this->obj_id,
                $this->ref_id,
                ilUtil::stripSlashes($this->getOrderField()),
                ilUtil::stripSlashes($this->getOrderDirection()),
                ilUtil::stripSlashes($this->getOffset()),
                ilUtil::stripSlashes($this->getLimit()),
                $this->filter,
                $additional_fields,
                $this->filter["view_mode"]
            );
        }

        // #13807
        foreach ($tr_data["set"] as $idx => $row) {
            if ($row["ref_id"] &&
                !ilLearningProgressAccess::checkPermission(
                    'read_learning_progress',
                    $row['ref_id']
                )) {
                foreach (array_keys($row) as $col_id) {
                    if (!in_array(
                        $col_id,
                        array("type",
                                       "obj_id",
                                       "ref_id",
                                       "title",
                                       "sort_title"
                    )
                    )) {
                        $tr_data["set"][$idx][$col_id] = null;
                    }
                }
                $tr_data["set"][$idx]["privacy_conflict"] = true;
            }
        }

        $this->setMaxCount($tr_data["cnt"]);

        if ($this->getOrderField() == "title") {
            // sort alphabetically, move parent object to 1st position
            $set = array();
            $parent = false;
            foreach ($tr_data["set"] as $idx => $row) {
                if ($row['obj_id'] == $this->obj_id) {
                    $parent = $row;
                } elseif (isset($row["sort_title"])) {
                    $set[strtolower($row["sort_title"]) . "__" . $idx] = $row;
                } else {
                    $set[strtolower($row["title"]) . "__" . $idx] = $row;
                }
            }
            unset($tr_data["set"]);
            if ($this->getOrderDirection() == "asc") {
                ksort($set);
            } else {
                krsort($set);
            }
            $set = array_values($set);
            if ($parent) {
                array_unshift($set, $parent);
            }

            $this->setData($set);
        } else {
            $this->setData($tr_data["set"]);
        }
    }

    public function initFilter() : void
    {
        // for scorm and objectives this filter does not make sense / is not implemented
        $olp = ilObjectLP::getInstance($this->obj_id);
        $collection = $olp->getCollectionInstance();
        if ($collection instanceof ilLPCollectionOfRepositoryObjects) {

            // show collection only/all
            $ti = new ilRadioGroupInputGUI(
                $this->lng->txt("trac_view_mode"),
                "view_mode"
            );
            $ti->addOption(
                new ilRadioOption($this->lng->txt("trac_view_mode_all"), "")
            );
            $ti->addOption(
                new ilRadioOption(
                    $this->lng->txt("trac_view_mode_collection"),
                    "coll"
                )
            );
            $this->addFilterItem($ti);
            $ti->readFromSession();
            $this->filter["view_mode"] = $ti->getValue();
        }
    }

    protected function fillRow(array $a_set) : void
    {
        global $DIC;
        $icons = ilLPStatusIcons::getInstance(ilLPStatusIcons::ICON_VARIANT_LONG);

        if (!$this->isPercentageAvailable($a_set["obj_id"])) {
            $a_set["percentage"] = null;
        }

        foreach ($this->getSelectedColumns() as $c) {
            if (!(bool) ($a_set["privacy_conflict"] ?? null)) {
                $val = (trim($a_set[$c]) == "")
                    ? " "
                    : $a_set[$c];

                if ($a_set[$c] != "" || $c == "status") {
                    switch ($c) {
                        case "first_access":
                            $val = ilDatePresentation::formatDate(
                                new ilDateTime(
                                    $a_set[$c],
                                    IL_CAL_DATETIME
                                )
                            );
                            break;

                        case "last_access":
                            $val = ilDatePresentation::formatDate(
                                new ilDateTime($a_set[$c], IL_CAL_UNIX)
                            );
                            break;

                        case "status":
                            $val = $icons->renderIconForStatus($a_set[$c]);

                            if ($a_set["ref_id"] &&
                                $a_set["type"] != "lobj" &&
                                $a_set["type"] != "sco" &&
                                $a_set["type"] != "st" &&
                                $a_set["type"] != "mob") {
                                $timing = $this->showTimingsWarning(
                                    $a_set["ref_id"],
                                    $this->user_id
                                );
                                if ($timing) {
                                    if ($timing !== true) {
                                        $timing = ": " . ilDatePresentation::formatDate(
                                            new ilDate(
                                                $timing,
                                                IL_CAL_UNIX
                                            )
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
                            break;

                        case "spent_seconds":
                            if (!ilObjectLP::supportsSpentSeconds(
                                $a_set["type"]
                            )) {
                                $val = "-";
                            } else {
                                $val = ilDatePresentation::secondsToString(
                                    $a_set[$c],
                                    ($a_set[$c] < 3600 ? true : false)
                                ); // #14858
                            }
                            break;

                        case "percentage":
                            $val = $a_set[$c] . "%";
                            break;

                    }
                }
                if ($c == "mark" &&
                    !ilObjectLP::supportsMark($this->type)) {
                    $val = "-";
                }
                if ($c == "spent_seconds" &&
                    !ilObjectLP::supportsSpentSeconds($this->type)) {
                    $val = "-";
                }
                if ($c == "percentage" &&
                    !$this->isPercentageAvailable($a_set["obj_id"])) {
                    $val = "-";
                }
            } else {
                $val = "&nbsp;";
            }

            $this->tpl->setCurrentBlock("user_field");
            $this->tpl->setVariable("VAL_UF", $val);
            $this->tpl->parseCurrentBlock();
        }

        if ($a_set["privacy_conflict"] ?? null) {
            $this->tpl->setCurrentBlock("permission_bl");
            $this->tpl->setVariable(
                "TXT_NO_PERMISSION",
                $this->lng->txt("status_no_permission")
            );
            $this->tpl->parseCurrentBlock();
        }

        if ($a_set["title"] == "") {
            $a_set["title"] = "--" . $this->lng->txt("none") . "--";
        }

        $this->tpl->setVariable(
            "ICON",
            ilObject::_getIcon(0, "tiny", $a_set["type"])
        );
        $this->tpl->setVariable("ICON_ALT", $this->lng->txt($a_set["type"]));

        if (in_array(
            $a_set['type'],
            array('fold', 'grp')
        ) && $a_set['obj_id'] != $this->obj_id) {
            if ($a_set['type'] == 'fold') {
                $object_gui = 'ilobjfoldergui';
            } else {
                $object_gui = 'ilobjgroupgui';
            }
            $this->tpl->setCurrentBlock('title_linked');

            $base_class = '';
            if ($this->http->wrapper()->query()->has('baseClass')) {
                $base_class = $this->http->wrapper()->query()->retrieve(
                    'baseClass',
                    $this->refinery->kindlyTo()->string()
                );
            }
            // link structure gets too complicated
            if ($base_class != "ilDashboardGUI" && $base_class != "ilAdministrationGUI") {
                $old = $this->ctrl->getParameterArrayByClass(
                    'illplistofobjectsgui'
                );
                $this->ctrl->setParameterByClass(
                    'illplistofobjectsgui',
                    'ref_id',
                    $a_set["ref_id"]
                );
                $this->ctrl->setParameterByClass(
                    'illplistofobjectsgui',
                    'details_id',
                    $a_set["ref_id"]
                );
                $this->ctrl->setParameterByClass(
                    'illplistofobjectsgui',
                    'user_id',
                    $this->user_id
                );
                $url = $this->ctrl->getLinkTargetByClass(
                    array('ilrepositorygui',
                          $object_gui,
                          'illearningprogressgui',
                          'illplistofobjectsgui'
                    ),
                    'userdetails'
                );
                $this->ctrl->setParameterByClass(
                    'illplistofobjectsgui',
                    'ref_id',
                    $old["ref_id"] ?? null
                );
                $this->ctrl->setParameterByClass(
                    'illplistofobjectsgui',
                    'details_id',
                    $old["details_id"]
                );
                $this->ctrl->setParameterByClass(
                    'illplistofobjectsgui',
                    'user_id',
                    $old["user_id"]
                );
            } else {
                $url = "#";
            }

            $this->tpl->setVariable("URL_TITLE", $url);
            $this->tpl->setVariable("VAL_TITLE", $a_set["title"]);
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setCurrentBlock('title_plain');
            $this->tpl->setVariable("VAL_TITLE", $a_set["title"]);
            $this->tpl->parseCurrentBlock();
        }

        // #16453 / #17163
        if ($a_set['ref_id']) {
            $path = new ilPathGUI();
            $path = $path->getPath($this->ref_id, $a_set['ref_id']);
            if ($path) {
                $this->tpl->setVariable(
                    'COLL_PATH',
                    $this->lng->txt('path') . ': ' . $path
                );
            }
        }

        // #13807 / #17069
        if ($a_set["ref_id"] &&
            ilLearningProgressAccess::checkPermission(
                'edit_learning_progress',
                $a_set['ref_id']
            )) {
            if (!in_array(
                $a_set["type"],
                array("sco", "lobj")
            ) && !$this->getPrintMode()) {
                $this->tpl->setCurrentBlock("item_command");
                $this->ctrl->setParameterByClass(
                    "illplistofobjectsgui",
                    "userdetails_id",
                    $a_set["ref_id"]
                );
                $this->tpl->setVariable(
                    "HREF_COMMAND",
                    $this->ctrl->getLinkTargetByClass(
                        "illplistofobjectsgui",
                        'edituser'
                    )
                );
                $this->tpl->setVariable("TXT_COMMAND", $this->lng->txt('edit'));
                $this->ctrl->setParameterByClass(
                    "illplistofobjectsgui",
                    "userdetails_id",
                    ""
                );
                $this->tpl->parseCurrentBlock();
            }
        }
    }

    protected function fillHeaderExcel(ilExcel $a_excel, int &$a_row) : void
    {
        $a_excel->setCell($a_row, 0, $this->lng->txt("type"));
        $a_excel->setCell($a_row, 1, $this->lng->txt("title"));

        $labels = $this->getSelectableColumns();
        $cnt = 2;
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
    ) : void {
        $a_excel->setCell($a_row, 0, $this->lng->txt($a_set["type"]));
        $a_excel->setCell($a_row, 1, $a_set["title"]);

        $cnt = 2;
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

    protected function fillHeaderCSV(ilCSVWriter $a_csv) : void
    {
        $a_csv->addColumn($this->lng->txt("type"));
        $a_csv->addColumn($this->lng->txt("title"));

        $labels = $this->getSelectableColumns();
        foreach ($this->getSelectedColumns() as $c) {
            $a_csv->addColumn($labels[$c]["txt"]);
        }

        $a_csv->addRow();
    }

    protected function fillRowCSV(ilCSVWriter $a_csv, array $a_set) : void
    {
        $a_csv->addColumn($this->lng->txt($a_set["type"]));
        $a_csv->addColumn($a_set["title"]);

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
