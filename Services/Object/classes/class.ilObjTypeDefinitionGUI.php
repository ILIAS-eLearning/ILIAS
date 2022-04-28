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
 * Class ilObjTypeDefinitionGUI
 *
 * handles operation assignment to objects (ONLY FOR TESTING PURPOSES!)
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilObjTypeDefinitionGUI extends ilObjectGUI
{
    public function __construct($data, int $id, bool $call_by_reference)
    {
        $this->type = "typ";
        parent::__construct($data, $id, $call_by_reference);
    }

    /**
     * list operations of object type
     */
    public function viewObject() : void
    {
        //prepare object list
        $this->data = [];
        $this->data["data"] = [];
        $this->data["ctrl"] = [];
        $this->data["cols"] = ["type", "operation", "description", "status"];

        $ops_valid = $this->rbac_review->getOperationsOnType(
            $this->request_wrapper->retrieve("obj_id", $this->refinery->kindlyTo()->int())
        );

        if ($list = ilRbacReview::_getOperationList()) {
            foreach ($list as $val) {
                if (in_array($val["ops_id"], $ops_valid)) {
                    $ops_status = 'enabled';
                } else {
                    $ops_status = 'disabled';
                }

                //visible data part
                $this->data["data"][] = [
                    "type" => "perm",
                    "operation" => $val["operation"],
                    "description" => $val["desc"],
                    "status" => $ops_status,
                    "obj_id" => $val["ops_id"]
                ];
            }
        }

        $this->maxcount = count($this->data["data"]);
        $this->data["data"] = ilArrayUtil::sortArray(
            $this->data["data"],
            $this->request_wrapper->retrieve("sort_by", $this->refinery->kindlyTo()->string()),
            $this->request_wrapper->retrieve("sort_order", $this->refinery->kindlyTo()->string()),
        );

        // now compute control information
        foreach ($this->data["data"] as $key => $val) {
            $this->data["ctrl"][$key] = [
                "obj_id" => $val["obj_id"],
                "type" => $val["type"]
            ];

            unset($this->data["data"][$key]["obj_id"]);
        }

        $this->displayList();
    }

    /**
     * display object list
     */
    public function displayList() : void
    {
        // load template for table
        $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.table.html");
        // load template for table content data
        $this->tpl->addBlockFile("TBL_CONTENT", "tbl_content", "tpl.obj_tbl_rows.html");

        $obj_str = ($this->call_by_reference) ? "" : "&obj_id=" . $this->obj_id;
        $this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=" . $this->ref_id . "$obj_str&cmd=gateway");

        $tbl = new ilTableGUI();

        $tbl->setTitle($this->lng->txt("obj_" . $this->object->getType()) . " '" . $this->object->getTitle() . "'");

        $header_names = [];
        foreach ($this->data["cols"] as $val) {
            $header_names[] = $this->lng->txt($val);
        }

        $tbl->setHeaderNames($header_names);

        $header_params = ["ref_id" => $this->ref_id,"obj_id" => $this->id];
        $tbl->setHeaderVars($this->data["cols"], $header_params);
        $tbl->setOrderColumn(
            $this->request_wrapper->retrieve("sort_by", $this->refinery->kindlyTo()->string())
        );
        $tbl->setOrderDirection(
            $this->request_wrapper->retrieve("sort_order", $this->refinery->kindlyTo()->string())
        );
        $tbl->setLimit();
        $tbl->setOffset(0);
        $tbl->setMaxCount($this->maxcount);
        $tbl->setFooter("tblfooter", $this->lng->txt("previous"), $this->lng->txt("next"));
        $tbl->render();

        if (is_array($this->data["data"][0])) {
            for ($i = 0; $i < count($this->data["data"]); $i++) {
                $data = $this->data["data"][$i];

                $this->tpl->setCurrentBlock("table_cell");
                $this->tpl->setVariable("CELLSTYLE", "tblrow1");
                $this->tpl->parseCurrentBlock();

                foreach ($data as $key => $val) {
                    if ($key == "status") {
                        if ($val == "enabled") {
                            $color = "green";
                        } else {
                            $color = "red";
                        }

                        $val = "<font color=\"" . $color . "\">" . $this->lng->txt($val) . "</font>";
                    }

                    $this->tpl->setCurrentBlock("text");

                    if ($key == "type") {
                        $val = ilUtil::getImageTagByType($val, $this->tpl->tplPath);
                    }

                    $this->tpl->setVariable("TEXT_CONTENT", $val);
                    $this->tpl->parseCurrentBlock();

                    $this->tpl->setCurrentBlock("table_cell");
                    $this->tpl->parseCurrentBlock();
                }

                $this->tpl->setCurrentBlock("tbl_content");
                $this->tpl->setVariable("CSS_ROW", " ");
                $this->tpl->parseCurrentBlock();
            }
        }
    }

    /**
     * save (de-)activation of operations on object
     */
    public function saveObject() : void
    {
        $ref_id = $this->request_wrapper->retrieve("ref_id", $this->refinery->kindlyTo()->int());
        $obj_id = $this->request_wrapper->retrieve("obj_id", $this->refinery->kindlyTo()->int());

        if (!$this->rbac_system->checkAccess('edit_permission', $ref_id)) {
            $this->error->raiseError($this->lng->txt("permission_denied"), $this->error->WARNING);
        }

        $ops_valid = $this->rbac_review->getOperationsOnType($obj_id);

        $ids = $this->post_wrapper->retrieve(
            "id",
            $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->string())
        );
        foreach ($ids as $ops_id => $status) {
            if ($status == 'enabled') {
                if (!in_array($ops_id, $ops_valid)) {
                    $this->rbac_admin->assignOperationToObject($obj_id, $ops_id);
                }
            }

            if ($status == 'disabled') {
                if (in_array($ops_id, $ops_valid)) {
                    $this->rbac_admin->deassignOperationFromObject($obj_id, $ops_id);
                }
            }
        }

        $this->object->update();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("saved_successfully"), true);

        header("Location: adm_object.php?ref_id=" . $ref_id . "&obj_id=" . $obj_id);
        exit();
    }

    /**
     * display edit form
     */
    public function editObject() : void
    {
        $ref_id = $this->request_wrapper->retrieve("ref_id", $this->refinery->kindlyTo()->int());
        if (!$this->rbac_system->checkAccess("edit_permission", $ref_id)) {
            $this->error->raiseError($this->lng->txt("permission_denied"), $this->error->MESSAGE);
        }

        //prepare objectlist
        $this->data = [];
        $this->data["data"] = [];
        $this->data["ctrl"] = [];
        $this->data["cols"] = ["type", "operation", "description", "status"];

        $ops_valid = $this->rbac_review->getOperationsOnType($this->obj_id);

        if ($ops_arr = ilRbacReview::_getOperationList()) {
            $options = ["e" => "enabled","d" => "disabled"];

            foreach ($ops_arr as $ops) {
                if (in_array($ops["ops_id"], $ops_valid)) {
                    $ops_status = 'e';
                } else {
                    $ops_status = 'd';
                }

                $obj = $ops["ops_id"];
                $ops_options = ilLegacyFormElementsUtil::formSelect($ops_status, "id[$obj]", $options);

                $this->data["data"][] = [
                    "type" => "perm",
                    "operation" => $ops["operation"],
                    "description" => $ops["desc"],
                    "status" => $ops_status,
                    "status_html" => $ops_options
                ];
            }
        }

        $this->maxcount = count($this->data["data"]);

        $sort_by = $this->request_wrapper->retrieve("sort_by", $this->refinery->kindlyTo()->string());
        $sort_order = $this->request_wrapper->retrieve("sort_order", $this->refinery->kindlyTo()->string());
        $this->data["data"] = ilArrayUtil::sortArray($this->data["data"], $sort_by, $sort_order);

        // now compute control information
        foreach ($this->data["data"] as $key => $val) {
            $this->data["ctrl"][$key] = [
                "obj_id" => $val["obj_id"],
                "type" => $val["type"]
            ];

            unset($this->data["data"][$key]["obj_id"]);
            $this->data["data"][$key]["status"] = $this->data["data"][$key]["status_html"];
            unset($this->data["data"][$key]["status_html"]);
        }

        $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.table.html");
        $this->tpl->addBlockFile("TBL_CONTENT", "tbl_content", "tpl.obj_tbl_rows.html");

        $obj_str = ($this->call_by_reference) ? "" : "&obj_id=" . $this->obj_id;
        $this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=" . $this->ref_id . "$obj_str&cmd=save");

        $tbl = new ilTableGUI();
        $tbl->setTitle(
            $this->lng->txt("edit_operations") . " " . strtolower($this->lng->txt("of")) . " '" . $this->object->getTitle() . "'"
        );

        $header_names = [];
        foreach ($this->data["cols"] as $val) {
            $header_names[] = $this->lng->txt($val);
        }

        $tbl->setHeaderNames($header_names);

        $header_params = ["ref_id" => $this->ref_id,"obj_id" => $this->id,"cmd" => "edit"];
        $tbl->setHeaderVars($this->data["cols"], $header_params);
        $tbl->setOrderColumn($sort_by);
        $tbl->setOrderDirection($sort_order);
        $tbl->setLimit();
        $tbl->setOffset(0);
        $tbl->setMaxCount($this->maxcount);

        $this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.svg"));
        $this->tpl->setVariable("COLUMN_COUNTS", count($this->data["cols"]));

        $tbl->setFooter("tblfooter", $this->lng->txt("previous"), $this->lng->txt("next"));

        $tbl->render();

        if (is_array($this->data["data"][0])) {
            //table cell
            for ($i = 0; $i < count($this->data["data"]); $i++) {
                $data = $this->data["data"][$i];

                $this->tpl->setCurrentBlock("table_cell");
                $this->tpl->setVariable("CELLSTYLE", "tblrow1");
                $this->tpl->parseCurrentBlock();

                foreach ($data as $key => $val) {
                    $this->tpl->setCurrentBlock("text");

                    if ($key == "type") {
                        $val = ilUtil::getImageTagByType($val, $this->tpl->tplPath);
                    }

                    $this->tpl->setVariable("TEXT_CONTENT", $val);
                    $this->tpl->parseCurrentBlock();

                    $this->tpl->setCurrentBlock("table_cell");
                    $this->tpl->parseCurrentBlock();
                } //foreach

                $this->tpl->setVariable("BTN_VALUE", $this->lng->txt("save"));

                $this->tpl->setCurrentBlock("tbl_content");
                $this->tpl->parseCurrentBlock();
            }
        }
    }
    
    public function executeCommand() : void
    {
        $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        if (!$cmd) {
            $cmd = "view";
        }
        $cmd .= "Object";
        $this->$cmd();
    }

    protected function getTabs() : void
    {
        if ($this->rbac_system->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "view"),
                ["view",""]
            );

            $this->tabs_gui->addTarget(
                "edit_operations",
                $this->ctrl->getLinkTarget($this, "edit"),
                "edit",
            );
        }
    }
}
