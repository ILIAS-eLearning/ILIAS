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
 * @author       Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilTrSummaryTableGUI: ilFormPropertyDispatchGUI
 * @ingroup      Services
 */
class ilTrSummaryTableGUI extends ilLPTableBaseGUI
{
    protected ?ilObjectLP $olp = null;
    protected bool $is_root;
    protected int $ref_id;
    protected ?string $type = null;
    protected int $obj_id;

    protected ilAccessHandler $access;
    protected ilRbacSystem $rbacsystem;

    /**
     * Constructor
     */
    public function __construct(
        ?object $a_parent_obj,
        string $a_parent_cmd,
        int $a_ref_id,
        bool $a_print_mode = false
    ) {
        global $DIC;

        $this->objDefinition = $DIC['objDefinition'];
        $this->rbacsystem = $DIC->rbac()->system();
        $this->access = $DIC->access();
        $this->ref_id = $a_ref_id;
        $this->obj_id = ilObject::_lookupObjId($a_ref_id);
        $this->is_root = ($a_ref_id == ROOT_FOLDER_ID);

        $this->setId("trsmy");

        if (!$this->is_root) {
            // #17084 - are we multi-object or not?
            //  we cannot parse type filter (too complicated)
            $type = ilObject::_lookupType($this->obj_id);
            if (!$this->objDefinition->isContainer($type)) {
                $this->type = $type;
                $this->olp = ilObjectLP::getInstance($this->obj_id);
            }
        }

        parent::__construct($a_parent_obj, $a_parent_cmd);

        if ($a_print_mode) {
            $this->setPrintMode(true);
        }

        $this->parseTitle($this->obj_id, "trac_summary");
        $this->setLimit(9999);
        $this->setShowTemplates(true);
        $this->setExportFormats(array(self::EXPORT_CSV, self::EXPORT_EXCEL));

        $this->addColumn($this->lng->txt("title"), "title");
        $this->setDefaultOrderField("title");

        $labels = $this->getSelectableColumns();
        foreach ($this->getSelectedColumns() as $c) {
            $this->addColumn($labels[$c]["txt"], $c);
        }

        if ($this->rbacsystem->checkAccess('write', $this->ref_id)) {
            $this->addColumn($this->lng->txt("path"));
            $this->addColumn($this->lng->txt("action"));
        }

        // $this->setExternalSorting(true);
        $this->setEnableHeader(true);
        $this->setFormAction(
            $this->ctrl->getFormActionByClass(get_class($this))
        );
        $this->setRowTemplate("tpl.trac_summary_row.html", "Services/Tracking");
        $this->initFilter();

        $this->getItems($a_parent_obj->getObjId(), $a_ref_id);
    }

    public function getSelectableColumns(): array
    {
        $lng_map = array("user_total" => "users",
                         "first_access_min" => "trac_first_access",
                         "last_access_max" => "trac_last_access",
                         "mark" => "trac_mark",
                         "status" => "trac_status",
                         'status_changed_max' => 'trac_status_changed',
                         "spent_seconds_avg" => "trac_spent_seconds",
                         "percentage_avg" => "trac_percentage",
                         "read_count_sum" => "trac_read_count",
                         "read_count_avg" => "trac_read_count",
                         "read_count_spent_seconds_avg" => "trac_read_count_spent_seconds"
        );

        $all = array("user_total");
        $default = array();

        // show only if extended data was activated in lp settings
        $tracking = new ilObjUserTracking();
        if ($tracking->hasExtendedData(
            ilObjUserTracking::EXTENDED_DATA_READ_COUNT
        )) {
            $all[] = "read_count_sum";
            $all[] = "read_count_avg";
            $default[] = "read_count_sum";
        }
        if ($tracking->hasExtendedData(
            ilObjUserTracking::EXTENDED_DATA_SPENT_SECONDS
        )) {
            if ($this->is_root || !$this->type || ilObjectLP::supportsSpentSeconds(
                $this->type
            )) {
                $all[] = "spent_seconds_avg";
                $default[] = "spent_seconds_avg";
            }
        }
        if ($tracking->hasExtendedData(
            ilObjUserTracking::EXTENDED_DATA_READ_COUNT
        ) &&
            $tracking->hasExtendedData(
                ilObjUserTracking::EXTENDED_DATA_SPENT_SECONDS
            )) {
            if ($this->is_root || !$this->type || ilObjectLP::supportsSpentSeconds(
                $this->type
            )) {
                $all[] = "read_count_spent_seconds_avg";
                // $default[] = "read_count_spent_seconds_avg";
            }
        }

        if ($this->is_root || !$this->type || $this->isPercentageAvailable(
            $this->obj_id
        )) {
            $all[] = "percentage_avg";
        }

        if ($this->is_root || !$this->olp || $this->olp->isActive()) {
            $all[] = "status";
            $all[] = 'status_changed_max';
        }

        if ($this->is_root || !$this->type || ilObjectLP::supportsMark(
            $this->type
        )) {
            $all[] = "mark";
        }

        $privacy = array("gender", "city", "country", "sel_country");
        foreach ($privacy as $field) {
            if ($this->setting->get("usr_settings_course_export_" . $field)) {
                $all[] = $field;
            }
        }

        $all[] = "language";

        $default[] = "percentage_avg";
        $default[] = "status";
        $default[] = "mark";

        if ($tracking->hasExtendedData(
            ilObjUserTracking::EXTENDED_DATA_LAST_ACCESS
        )) {
            $all[] = "first_access_min";
            $all[] = "last_access_max";
        }

        $all[] = "create_date_min";
        $all[] = "create_date_max";

        $columns = array();
        foreach ($all as $column) {
            $l = $column;

            $prefix = false;
            if (substr($l, -3) == "avg") {
                $prefix = "&#216; ";
            } elseif (substr($l, -3) == "sum" || $l == "user_total") {
                $prefix = "&#8721; ";
            }

            if (isset($lng_map[$l])) {
                $l = $lng_map[$l];
            }

            $txt = $prefix . $this->lng->txt($l);

            if (in_array(
                $column,
                array("read_count_avg",
                               "spent_seconds_avg",
                               "percentage_avg"
            )
            )) {
                $txt .= " / " . $this->lng->txt("user");
            }

            $columns[$column] = array(
                "txt" => $txt,
                "default" => (in_array($column, $default) ? true : false)
            );
        }
        return $columns;
    }

    public function initFilter(): void
    {
        if ($this->is_root) {
            parent::initBaseFilter(true, false);
            return;
        }

        // show only if extended data was activated in lp settings
        $tracking = new ilObjUserTracking();

        $item = $this->addFilterItemByMetaType(
            "user_total",
            ilTable2GUI::FILTER_NUMBER_RANGE,
            true,
            "&#8721; " . $this->lng->txt("users")
        );
        $this->filter["user_total"] = $item->getValue();

        if ($tracking->hasExtendedData(
            ilObjUserTracking::EXTENDED_DATA_READ_COUNT
        )) {
            $item = $this->addFilterItemByMetaType(
                "read_count",
                ilTable2GUI::FILTER_NUMBER_RANGE,
                true,
                "&#8721; " . $this->lng->txt("trac_read_count")
            );
            $this->filter["read_count"] = $item->getValue();
        }

        if ($tracking->hasExtendedData(
            ilObjUserTracking::EXTENDED_DATA_SPENT_SECONDS
        )) {
            if ($this->is_root || !$this->type || ilObjectLP::supportsSpentSeconds(
                $this->type
            )) {
                $item = $this->addFilterItemByMetaType(
                    "spent_seconds",
                    ilTable2GUI::FILTER_DURATION_RANGE,
                    true,
                    "&#216; " . $this->lng->txt(
                        "trac_spent_seconds"
                    ) . " / " . $this->lng->txt("user")
                );
                $this->filter["spent_seconds"]["from"] = $item->getCombinationItem(
                    "from"
                )->getValueInSeconds();
                $this->filter["spent_seconds"]["to"] = $item->getCombinationItem(
                    "to"
                )->getValueInSeconds();
            }
        }

        if ($this->is_root || !$this->type || $this->isPercentageAvailable(
            $this->obj_id
        )) {
            $item = $this->addFilterItemByMetaType(
                "percentage",
                ilTable2GUI::FILTER_NUMBER_RANGE,
                true,
                "&#216; " . $this->lng->txt(
                    "trac_percentage"
                ) . " / " . $this->lng->txt("user")
            );
            $this->filter["percentage"] = $item->getValue();
        }

        if ($this->is_root || !$this->olp || $this->olp->isActive()) {
            $item = $this->addFilterItemByMetaType(
                "status",
                ilTable2GUI::FILTER_SELECT,
                true
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
            if ($this->filter["status"]) {
                $this->filter["status"]--;
            }

            $item = $this->addFilterItemByMetaType(
                "trac_status_changed",
                ilTable2GUI::FILTER_DATE_RANGE,
                true
            );
            $this->filter["status_changed"] = $item->getDate();
        }

        if ($this->is_root || !$this->type || ilObjectLP::supportsMark(
            $this->type
        )) {
            $item = $this->addFilterItemByMetaType(
                "mark",
                ilTable2GUI::FILTER_TEXT,
                true,
                $this->lng->txt("trac_mark")
            );
            $this->filter["mark"] = $item->getValue();
        }

        if ($this->setting->get("usr_settings_course_export_gender")) {
            $item = $this->addFilterItemByMetaType(
                "gender",
                ilTable2GUI::FILTER_SELECT,
                true
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
        }

        if ($this->setting->get("usr_settings_course_export_city")) {
            $item = $this->addFilterItemByMetaType(
                "city",
                ilTable2GUI::FILTER_TEXT,
                true
            );
            $this->filter["city"] = $item->getValue();
        }

        if ($this->setting->get("usr_settings_course_export_country")) {
            $item = $this->addFilterItemByMetaType(
                "country",
                ilTable2GUI::FILTER_TEXT,
                true
            );
            $this->filter["country"] = $item->getValue();
        }

        if ($this->setting->get("usr_settings_course_export_sel_country")) {
            $item = $this->addFilterItemByMetaType(
                "sel_country",
                ilTable2GUI::FILTER_SELECT,
                true
            );
            $item->setOptions(
                array("" => $this->lng->txt(
                    "trac_all"
                )
                ) + $this->getSelCountryCodes()
            );
            $this->filter["sel_country"] = $item->getValue();
        }

        $item = $this->addFilterItemByMetaType(
            "language",
            ilTable2GUI::FILTER_LANGUAGE,
            true
        );
        $this->filter["language"] = $item->getValue();

        if ($tracking->hasExtendedData(
            ilObjUserTracking::EXTENDED_DATA_LAST_ACCESS
        )) {
            $item = $this->addFilterItemByMetaType(
                "trac_first_access",
                ilTable2GUI::FILTER_DATETIME_RANGE,
                true
            );
            $this->filter["first_access"] = $item->getDate();

            $item = $this->addFilterItemByMetaType(
                "trac_last_access",
                ilTable2GUI::FILTER_DATETIME_RANGE,
                true
            );
            $this->filter["last_access"] = $item->getDate();
        }

        $item = $this->addFilterItemByMetaType(
            "registration_filter",
            ilTable2GUI::FILTER_DATE_RANGE,
            true
        );
        $this->filter["registration"] = $item->getDate();
    }

    public function getSelCountryCodes(): array
    {
        $options = array();
        foreach (ilCountry::getCountryCodes() as $c) {
            $options[$c] = $this->lng->txt("meta_c_" . $c);
        }
        asort($options);
        return $options;
    }

    /**
     * Build summary item rows for given object and filter(s
     */
    public function getItems(int $a_object_id, int $a_ref_id): void
    {
        // show only selected subobjects for lp mode
        $preselected_obj_ids = $filter = null;

        $olp = ilObjectLP::getInstance(ilObject::_lookupObjId($a_ref_id));
        if (
            $olp->getCurrentMode(
            ) == ilLPObjSettings::LP_MODE_COLLECTION_MANUAL ||
            $olp->getCurrentMode() == ilLPObjSettings::LP_MODE_COLLECTION ||
            $olp->getCurrentMode() == ilLPObjSettings::LP_MODE_MANUAL_BY_TUTOR
        ) {
            $collection = $olp->getCollectionInstance();
            $preselected_obj_ids[$a_object_id][] = $a_ref_id;
            foreach ($collection->getItems() as $item => $item_info) {
                $tmp_lp = ilObjectLP::getInstance(
                    ilObject::_lookupObjId($item_info)
                );
                if ($tmp_lp->isActive()) {
                    $preselected_obj_ids[ilObject::_lookupObjId(
                        $item_info
                    )][] = $item_info;
                }
            }
            $filter = $this->getCurrentFilter();
        } elseif ($this->is_root) {
            // using search to get all relevant objects
            // #8498/#8499: restrict to objects with at least "read_learning_progress" access
            $preselected_obj_ids = $this->searchObjects(
                $this->getCurrentFilter(true),
                "read_learning_progress"
            );
        } else {
            // using summary filters
            $filter = $this->getCurrentFilter();
        }

        $data = ilTrQuery::getObjectsSummaryForObject(
            $a_object_id,
            $a_ref_id,
            ilUtil::stripSlashes($this->getOrderField()),
            ilUtil::stripSlashes($this->getOrderDirection()),
            ilUtil::stripSlashes($this->getOffset()),
            ilUtil::stripSlashes($this->getLimit()),
            $filter,
            $this->getSelectedColumns(),
            $preselected_obj_ids
        );

        // build status to image map
        $valid_status = array(ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM,
                              ilLPStatus::LP_STATUS_IN_PROGRESS_NUM,
                              ilLPStatus::LP_STATUS_COMPLETED_NUM,
                              ilLPStatus::LP_STATUS_FAILED_NUM
        );
        $status_map = array();
        $status_icons = ilLPStatusIcons::getInstance(ilLPStatusIcons::ICON_VARIANT_SHORT);
        foreach ($valid_status as $status) {
            $status_map[$status] = $status_icons->renderIconForStatus($status);
        }

        // language map
        $this->lng->loadLanguageModule("meta");
        $languages = array();
        foreach ($this->lng->getInstalledLanguages() as $lang_key) {
            $languages[$lang_key] = $this->lng->txt("meta_l_" . $lang_key);
        }

        $rows = array();
        foreach ($data["set"] as $idx => $result) {
            // sessions have no title
            if ($result["title"] == "" && $result["type"] == "sess") {
                $sess = new ilObjSession($result["obj_id"], false);
                $data["set"][$idx]["title"] = $sess->getFirstAppointment(
                )->appointmentToString();
            }

            $data["set"][$idx]["offline"] = ilLearningProgressBaseGUI::isObjectOffline(
                $result["obj_id"],
                $result["type"]
            );

            // #13807
            if ($result["ref_ids"]) {
                $valid = false;
                foreach ($result["ref_ids"] as $check_ref_id) {
                    if (ilLearningProgressAccess::checkPermission(
                        'read_learning_progress',
                        $check_ref_id
                    )) {
                        $valid = true;
                        break;
                    }
                }
                if (!$valid) {
                    foreach (array_keys($data["set"][$idx]) as $col_id) {
                        if (!in_array(
                            $col_id,
                            array("type",
                                           "title",
                                           "obj_id",
                                           "ref_id",
                                           "offline"
                        )
                        )) {
                            $data["set"][$idx][$col_id] = null;
                        }
                    }
                    $data["set"][$idx]["privacy_conflict"] = true;
                    continue;
                }
            }

            // percentages
            $users_no = $result["user_total"];
            $data["set"][$idx]["country"] = $this->getItemsPercentages(
                $result["country"],
                $users_no
            );
            $data["set"][$idx]["gender"] = $this->getItemsPercentages(
                $result["gender"],
                $users_no,
                array(
                "n" => $this->lng->txt("gender_n"),
                "m" => $this->lng->txt("gender_m"),
                "f" => $this->lng->txt("gender_f"),
            )
            );
            $data["set"][$idx]["city"] = $this->getItemsPercentages(
                $result["city"],
                $users_no
            );
            $data["set"][$idx]["sel_country"] = $this->getItemsPercentages(
                $result["sel_country"],
                $users_no,
                $this->getSelCountryCodes()
            );
            $data["set"][$idx]["mark"] = $this->getItemsPercentages(
                $result["mark"],
                $users_no
            );
            $data["set"][$idx]["language"] = $this->getItemsPercentages(
                $result["language"],
                $users_no,
                $languages
            );

            // if we encounter any invalid status codes, e.g. null, map them to not attempted instead
            foreach ($result["status"] as $status_code => $status_counter) {
                // null is cast to ""
                if ($status_code === "" || !in_array(
                    $status_code,
                    $valid_status
                )) {
                    $result['status'][ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM] =
                        $result['status'][ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM] ?? 0 + $status_counter;
                    unset($result["status"][$status_code]);
                }
            }
            $data["set"][$idx]["status"] = $this->getItemsPercentagesStatus(
                $result["status"],
                $users_no,
                $status_map
            );

            if (!$this->isPercentageAvailable($result["obj_id"])) {
                $data["set"][$idx]["percentage_avg"] = null;
            }
        }

        $this->setMaxCount($data["cnt"]);
        $this->setData($data["set"]);
    }

    /**
     * Render data as needed for summary list (based on grouped values)
     */
    protected function getItemsPercentages(
        $data = null,
        int $overall = 0,
        array $value_map = null,
        $limit = 3
    ): array {
        if (!$overall) {
            return [];
        }

        $result = [];

        if ($data) {
            if (is_array($data) && count($data) < $limit) {
                $limit = count($data);
            }
            if (is_array($data) && (count($data) == $limit + 1)) {
                ++$limit;
            }
            $counter = $others_counter = 0;
            $others_sum = $overall;
            $all_sum = 0;
            foreach ($data as $id => $count) {
                $counter++;
                $all_sum += $count;
                if ($counter <= $limit) {
                    $caption = $id;

                    if ($value_map && isset($value_map[$id])) {
                        $caption = $value_map[$id];
                    }
                    if ($caption == "") {
                        $caption = $this->lng->txt("none");
                    }
                    if (
                        $counter == $limit &&
                        $all_sum < $overall
                    ) {
                        ++$others_counter;
                        continue;
                    }
                    $perc = round($count / $overall * 100);
                    $result[] = array(
                        "caption" => $caption,
                        "absolute" => $count,
                        // ." ".($count > 1 ? $lng->txt("users") : $lng->txt("user")),
                        "percentage" => $perc
                    );
                    $others_sum -= $count;
                } else {
                    $others_counter++;
                }
            }

            if ($others_counter) {
                $perc = round($others_sum / $overall * 100);
                $result[] = array(
                    "caption" => $others_counter . "  " . $this->lng->txt(
                        "trac_others"
                    ),
                    "absolute" => $others_sum,
                    // ." ".($others_sum > 1 ? $lng->txt("users") : $lng->txt("user")),
                    "percentage" => $perc
                );
            }
        }

        return $result;
    }

    /**
     * Render status data as needed for summary list (based on grouped values)
     */
    protected function getItemsPercentagesStatus(
        $data = null,
        int $overall = 0,
        array $value_map = null
    ): array {
        $result = array();
        foreach ($value_map as $id => $caption) {
            $count = 0;
            if (isset($data[$id])) {
                $count = $data[$id];
            }
            $perc = round($count / $overall * 100);

            $result[] = array(
                "caption" => $caption,
                "absolute" => $count,
                "percentage" => $perc
            );
        }

        return $result;
    }

    protected function parseValue(
        string $id,
        ?string $value,
        string $type
    ): string {
        // get rid of aggregation
        $pos = strrpos($id, "_");
        if ($pos !== false) {
            $function = strtoupper(substr($id, $pos + 1));
            if (in_array(
                $function,
                array("MIN", "MAX", "SUM", "AVG", "COUNT")
            )) {
                $id = substr($id, 0, $pos);
            }
        }

        if (trim($value) == "") {
            if ($id == "title") {
                return "--" . $this->lng->txt("none") . "--";
            }
            return "";
        }
        switch ($id) {
            case 'status_changed':
            case "first_access":
            case "create_date":
                $value = ilDatePresentation::formatDate(
                    new ilDateTime($value, IL_CAL_DATETIME)
                );
                break;

            case "last_access":
                $value = ilDatePresentation::formatDate(
                    new ilDateTime($value, IL_CAL_UNIX)
                );
                break;

            case "spent_seconds":
            case "read_count_spent_seconds":
                if (!ilObjectLP::supportsSpentSeconds($type)) {
                    $value = "-";
                } else {
                    $value = ilDatePresentation::secondsToString(
                        (int) $value,
                        $value < 3600
                    ); // #14858
                }
                break;

            case "percentage":
                if (false /* $this->isPercentageAvailable() */) {
                    $value = "-";
                } else {
                    $value = $value . "%";
                }
                break;

            case "mark":
                if (!ilObjectLP::supportsMark($type)) {
                    $value = "-";
                }
                break;
        }

        return $value;
    }

    /**
     * Fill table row
     */
    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable(
            "ICON",
            ilObject::_getIcon(
                (int) $a_set["obj_id"],
                "tiny",
                $a_set["type"]
            )
        );
        $this->tpl->setVariable("ICON_ALT", $this->lng->txt($a_set["type"]));
        $this->tpl->setVariable("TITLE", $a_set["title"]);

        if ($a_set["offline"] || ($a_set["privacy_conflict"] ?? null)) {
            $mess = array();
            if ($a_set["offline"]) {
                $mess[] = $this->lng->txt("offline");
            }
            if ($a_set["privacy_conflict"] ?? null) {
                $mess[] = $this->lng->txt("status_no_permission");
            }
            $this->tpl->setCurrentBlock("status_bl");
            $this->tpl->setVariable("TEXT_STATUS", implode(", ", $mess));
            $this->tpl->parseCurrentBlock();
        }

        foreach ($this->getSelectedColumns() as $c) {
            switch ($c) {
                case "country":
                case "gender":
                case "city":
                case "language":
                case "status":
                case "mark":
                case "sel_country":
                    $this->renderPercentages($c, $a_set[$c]);
                    break;

                case "percentage_avg":
                    if ((int) $a_set[$c] === 0 || !$this->isPercentageAvailable(
                        $a_set["obj_id"]
                    )) {
                        $this->tpl->setVariable(strtoupper($c), "");
                        break;
                    }

                // no break
                default:
                    $value = $this->parseValue($c, $a_set[$c], $a_set["type"]);
                    $this->tpl->setVariable(strtoupper($c), $value);
                    break;
            }
        }

        if ($this->is_root) {
            $path = $this->buildPath($a_set["ref_ids"]);
            if ($path) {
                $this->tpl->setCurrentBlock("item_path");
                foreach ($path as $ref_id => $path_item) {
                    $this->tpl->setVariable("PATH_ITEM", $path_item);

                    if (!$this->anonymized) {
                        $this->ctrl->setParameterByClass(
                            $this->ctrl->getCmdClass(),
                            'details_id',
                            $ref_id
                        );
                        $this->tpl->setVariable(
                            "URL_DETAILS",
                            $this->ctrl->getLinkTargetByClass(
                                $this->ctrl->getCmdClass(),
                                'details'
                            )
                        );
                        $this->ctrl->setParameterByClass(
                            $this->ctrl->getCmdClass(),
                            'details_id',
                            ''
                        );
                        $this->tpl->setVariable(
                            "TXT_DETAILS",
                            $this->lng->txt(
                                'trac_participants'
                            )
                        );
                    } else {
                        $this->tpl->setVariable(
                            "URL_DETAILS",
                            ilLink::_getLink(
                                $ref_id,
                                $a_set["type"]
                            )
                        );
                        $this->tpl->setVariable(
                            "TXT_DETAILS",
                            $this->lng->txt('view')
                        );
                    }

                    $this->tpl->parseCurrentBlock();
                }
            }

            $this->tpl->setCurrentBlock("item_command");
            $this->ctrl->setParameterByClass(
                get_class($this),
                'hide',
                $a_set["obj_id"]
            );
            $this->tpl->setVariable(
                "HREF_COMMAND",
                $this->ctrl->getLinkTargetByClass(
                    get_class($this),
                    'hide'
                )
            );
            $this->tpl->setVariable(
                "TXT_COMMAND",
                $this->lng->txt('trac_hide')
            );
            $this->tpl->parseCurrentBlock();

            $this->tpl->touchBlock("path_action");
        } elseif ($a_set["ref_ids"]) { // #18446
            // #16453
            $path = new ilPathGUI();
            $path = $path->getPath(
                $this->ref_id,
                (int) array_pop($a_set["ref_ids"])
            );
            if ($path) {
                $this->tpl->setVariable(
                    'COLL_PATH',
                    $this->lng->txt('path') . ': ' . $path
                );
            }
        }
    }

    protected function renderPercentages(string $id, array $data): void
    {
        if ($data) {
            foreach ($data as $item) {
                $this->tpl->setCurrentBlock($id . "_row");
                $this->tpl->setVariable("CAPTION", $item["caption"]);
                $this->tpl->setVariable("ABSOLUTE", $item["absolute"]);
                $this->tpl->setVariable("PERCENTAGE", $item["percentage"]);
                $this->tpl->parseCurrentBlock();
            }
        } else {
            $this->tpl->touchBlock($id);
        }
    }

    protected function isArrayColumn(string $a_name): bool
    {
        if (in_array(
            $a_name,
            array("country",
                           "gender",
                           "city",
                           "language",
                           "status",
                           "mark",
                           'sel_country'
        )
        )) {
            return true;
        }
        return false;
    }

    public function numericOrdering(string $a_field): bool
    {
        $pos = strrpos($a_field, "_");
        if ($pos !== false) {
            $function = strtoupper(substr($a_field, $pos + 1));
            if (in_array(
                $function,
                array("MIN", "MAX", "SUM", "AVG", "COUNT", "TOTAL")
            )) {
                return true;
            }
        }
        return false;
    }

    protected function fillHeaderExcel(ilExcel $a_excel, int &$a_row): void
    {
        $a_excel->setCell($a_row, 0, $this->lng->txt("title"));

        $labels = $this->getSelectableColumns();
        $cnt = 1;
        foreach ($this->getSelectedColumns() as $c) {
            $label = $labels[$c]["txt"];
            $label = str_replace(
                "&#216;",
                $this->lng->txt("trac_average"),
                $label
            );
            $label = str_replace(
                "&#8721;",
                $this->lng->txt("trac_sum"),
                $label
            );

            if (!$this->isArrayColumn($c)) {
                $a_excel->setCell($a_row, $cnt, $label);
                $cnt++;
            } else {
                if ($c != "status") {
                    $a_excel->setCell($a_row, $cnt, $label . " #1");
                    $a_excel->setCell($a_row, ++$cnt, $label . " #1");
                    $a_excel->setCell($a_row, ++$cnt, $label . " #1 %");
                    $a_excel->setCell($a_row, ++$cnt, $label . " #2");
                    $a_excel->setCell($a_row, ++$cnt, $label . " #2");
                    $a_excel->setCell($a_row, ++$cnt, $label . " #2 %");
                    $a_excel->setCell($a_row, ++$cnt, $label . " #3");
                    $a_excel->setCell($a_row, ++$cnt, $label . " #3");
                    $a_excel->setCell($a_row, ++$cnt, $label . " #3 %");
                    $a_excel->setCell(
                        $a_row,
                        ++$cnt,
                        $label . " " . $this->lng->txt(
                            "trac_others"
                        )
                    );
                    $a_excel->setCell(
                        $a_row,
                        ++$cnt,
                        $label . " " . $this->lng->txt(
                            "trac_others"
                        )
                    );
                    $a_excel->setCell(
                        $a_row,
                        ++$cnt,
                        $label . " " . $this->lng->txt(
                            "trac_others"
                        ) . " %"
                    );
                } else {
                    // build status to image map
                    $valid_status = array(ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM,
                                          ilLPStatus::LP_STATUS_IN_PROGRESS_NUM,
                                          ilLPStatus::LP_STATUS_COMPLETED_NUM,
                                          ilLPStatus::LP_STATUS_FAILED_NUM
                    );
                    $cnt--;
                    foreach ($valid_status as $status) {
                        $text = ilLearningProgressBaseGUI::_getStatusText(
                            $status
                        );
                        $a_excel->setCell($a_row, ++$cnt, $text);
                        $a_excel->setCell($a_row, ++$cnt, $text . " %");
                    }
                }
                $cnt++;
            }
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
        $a_excel->setCell($a_row, 0, $a_set["title"]);

        $cnt = 1;
        foreach ($this->getSelectedColumns() as $c) {
            if (!$this->isArrayColumn($c)) {
                $val = $this->parseValue($c, $a_set[$c], $a_set["type"]);
                $a_excel->setCell($a_row, $cnt, $val);
                $cnt++;
            } else {
                foreach ((array) $a_set[$c] as $idx => $value) {
                    if ($c == "status") {
                        $a_excel->setCell(
                            $a_row,
                            $cnt,
                            (int) $value["absolute"]
                        );
                        $a_excel->setCell(
                            $a_row,
                            ++$cnt,
                            $value["percentage"] . "%"
                        );
                    } else {
                        $a_excel->setCell($a_row, $cnt, $value["caption"]);
                        $a_excel->setCell(
                            $a_row,
                            ++$cnt,
                            (int) $value["absolute"]
                        );
                        $a_excel->setCell(
                            $a_row,
                            ++$cnt,
                            $value["percentage"] . "%"
                        );
                    }
                    $cnt++;
                }
                if (sizeof($a_set[$c]) < 4 && $c != "status") {
                    for ($loop = 4; $loop > sizeof($a_set[$c]); $loop--) {
                        $a_excel->setCell($a_row, $cnt, "");
                        $a_excel->setCell($a_row, ++$cnt, "");
                        $a_excel->setCell($a_row, ++$cnt, "");
                        $cnt++;
                    }
                }
            }
        }
    }

    protected function fillHeaderCSV(ilCSVWriter $a_csv): void
    {
        $a_csv->addColumn($this->lng->txt("title"));

        $labels = $this->getSelectableColumns();
        foreach ($this->getSelectedColumns() as $c) {
            $label = $labels[$c]["txt"];
            $label = str_replace(
                "&#216;",
                $this->lng->txt("trac_average"),
                $label
            );
            $label = str_replace(
                "&#8721;",
                $this->lng->txt("trac_sum"),
                $label
            );

            if (!$this->isArrayColumn($c)) {
                $a_csv->addColumn($label);
            } else {
                if ($c != "status") {
                    $a_csv->addColumn($label . " #1");
                    $a_csv->addColumn($label . " #1");
                    $a_csv->addColumn($label . " #1 %");
                    $a_csv->addColumn($label . " #2");
                    $a_csv->addColumn($label . " #2");
                    $a_csv->addColumn($label . " #2 %");
                    $a_csv->addColumn($label . " #3");
                    $a_csv->addColumn($label . " #3");
                    $a_csv->addColumn($label . " #3 %");
                    $a_csv->addColumn(
                        $label . " " . $this->lng->txt("trac_others")
                    );
                    $a_csv->addColumn(
                        $label . " " . $this->lng->txt("trac_others")
                    );
                    $a_csv->addColumn(
                        $label . " " . $this->lng->txt("trac_others") . " %"
                    );
                } else {
                    // build status to image map
                    $valid_status = array(ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM,
                                          ilLPStatus::LP_STATUS_IN_PROGRESS_NUM,
                                          ilLPStatus::LP_STATUS_COMPLETED_NUM,
                                          ilLPStatus::LP_STATUS_FAILED_NUM
                    );
                    foreach ($valid_status as $status) {
                        $text = ilLearningProgressBaseGUI::_getStatusText(
                            $status
                        );
                        $a_csv->addColumn($text);
                        $a_csv->addColumn($text . " %");
                    }
                }
            }
        }

        $a_csv->addRow();
    }

    protected function fillRowCSV(ilCSVWriter $a_csv, array $a_set): void
    {
        $a_csv->addColumn($a_set["title"]);

        foreach ($this->getSelectedColumns() as $c) {
            if (!$this->isArrayColumn($c)) {
                $val = $this->parseValue($c, $a_set[$c], $a_set["type"]);
                $a_csv->addColumn($val);
            } else {
                foreach ((array) $a_set[$c] as $idx => $value) {
                    if ($c != "status") {
                        $a_csv->addColumn($value["caption"]);
                    }
                    $a_csv->addColumn((string) $value["absolute"]);
                    $a_csv->addColumn($value["percentage"]);
                }
                if (sizeof($a_set[$c]) < 4 && $c != "status") {
                    for ($loop = 4; $loop > sizeof($a_set[$c]); $loop--) {
                        $a_csv->addColumn("");
                        $a_csv->addColumn("");
                        $a_csv->addColumn("");
                    }
                }
            }
        }

        $a_csv->addRow();
    }
}
