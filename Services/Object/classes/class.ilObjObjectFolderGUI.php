<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/


/**
* Class ilObjObjectFolderGUI
*
* @author Stefan Meyer <meyer@leifos.com>
* $Id$
*
* @ilCtrl_Calls ilObjObjectFolderGUI: ilPermissionGUI
*
* @extends ilObjectGUI
*/

require_once "./Services/Object/classes/class.ilObjectGUI.php";

class ilObjObjectFolderGUI extends ilObjectGUI
{
    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
    * Constructor
    *
    * @param	array	basic object data
    * @param	integer	ref_id or obj_id (depends on referenced-flag)
    * @param	boolean	true: treat id as ref_id; false: treat id as obj_id
    * @access	public
    */
    public function __construct($a_data, $a_id, $a_call_by_reference)
    {
        global $DIC;

        $this->rbacsystem = $DIC->rbac()->system();
        $this->error = $DIC["ilErr"];
        $this->type = "objf";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
    }

    /**
    * list childs of current object
    *
    * @access	public
    */
    public function viewObject()
    {
        $rbacsystem = $this->rbacsystem;
        $ilErr = $this->error;

        if (!$rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $ilErr->raiseError($this->lng->txt("permission_denied"), $ilErr->MESSAGE);
        }

        //prepare objectlist
        $this->data = array();
        $this->data["data"] = array();
        $this->data["ctrl"] = array();

        $this->data["cols"] = array("type","title","last_change");

        $this->maxcount = count($this->data["data"]);

        // now compute control information
        foreach ($this->data["data"] as $key => $val) {
            $this->data["ctrl"][$key] = array(
                                            "ref_id" => $this->id,
                                            "obj_id" => $val["obj_id"],
                                            "type" => $val["type"],
                                            );

            unset($this->data["data"][$key]["obj_id"]);
            $this->data["data"][$key]["last_change"] = ilDatePresentation::formatDate(new ilDateTime($this->data["data"][$key]["last_change"], IL_CAL_DATETIME));
        }

        $this->displayList();
    }

    /**
    * display object list
    *
    * @access	public
    */
    public function displayList()
    {
        include_once "./Services/Table/classes/class.ilTableGUI.php";
        return;

        // load template for table
        $this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");

        // load template for table content data
        $this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.obj_tbl_rows.html");

        $num = 0;

        $obj_str = ($this->call_by_reference) ? "" : "&obj_id=" . $this->obj_id;
        $this->tpl->setVariable(
            "FORMACTION",
            $this->ctrl->getFormAction($this)
        );

        // create table
        $tbl = new ilTableGUI();

        // title & header columns
        $tbl->setTitle($this->object->getTitle());

        foreach ($this->data["cols"] as $val) {
            $header_names[] = $this->lng->txt($val);
        }

        $tbl->setHeaderNames($header_names);

        //$header_params = array("ref_id" => $this->ref_id);
        $header_params = $this->ctrl->getParameterArray($this, "view");
        $tbl->setHeaderVars($this->data["cols"], $header_params);
        $tbl->setColumnWidth(array("15","75%","25%"));

        // control
        $tbl->setOrderColumn($_GET["sort_by"]);
        $tbl->setOrderDirection($_GET["sort_order"]);
        $tbl->setLimit($_GET["limit"]);
        $tbl->setOffset($_GET["offset"]);
        $tbl->setMaxCount($this->maxcount);

        // footer
        $tbl->setFooter("tblfooter", $this->lng->txt("previous"), $this->lng->txt("next"));
        //$tbl->disable("footer");
        
        // render table
        $tbl->render();

        if (is_array($this->data["data"][0])) {
            //table cell
            for ($i = 0; $i < count($this->data["data"]); $i++) {
                $data = $this->data["data"][$i];
                $ctrl = $this->data["ctrl"][$i];

                // color changing
                $css_row = ilUtil::switchColor($i + 1, "tblrow1", "tblrow2");

                $this->tpl->setCurrentBlock("table_cell");
                $this->tpl->setVariable("CELLSTYLE", "tblrow1");
                $this->tpl->parseCurrentBlock();

                foreach ($data as $key => $val) {
                    //build link
                    /*

                    $n = 0;

                    foreach ($ctrl as $key2 => $val2)
                    {
                        $link .= $key2."=".$val2;

                        if ($n < count($ctrl)-1)
                        {
                            $link .= "&";
                            $n++;
                        }
                    }

                    if ($key == "title")
                    {
                        $name_field = explode("#separator#",$val);
                    }

                    if ($key == "title" || $key == "type")
                    {
                        $this->tpl->setCurrentBlock("begin_link");
                        $this->tpl->setVariable("LINK_TARGET", $link);

                        $this->tpl->parseCurrentBlock();
                        $this->tpl->touchBlock("end_link");
                    }

                    $this->tpl->setCurrentBlock("text");

                    if ($key == "type")
                    {
                        $val = ilUtil::getImageTagByType($val,$this->tpl->tplPath);
                    }

                    if ($key == "title")
                    {
                        $this->tpl->setVariable("TEXT_CONTENT", $name_field[0]);

                        $this->tpl->setCurrentBlock("subtitle");
                        $this->tpl->setVariable("DESC", $name_field[1]);
                        $this->tpl->parseCurrentBlock();
                    }
                    else
                    {
                        $this->tpl->setVariable("TEXT_CONTENT", $val);
                    }

                    $this->tpl->parseCurrentBlock();

                    $this->tpl->setCurrentBlock("table_cell");
                    $this->tpl->parseCurrentBlock();
                    */
                } //foreach

                $this->tpl->setCurrentBlock("tbl_content");
                $this->tpl->setVariable("CSS_ROW", $css_row);
                $this->tpl->parseCurrentBlock();
            } //for
        } //if is_array
        else {
            $this->tpl->setCurrentBlock("notfound");
            $this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
            $this->tpl->setVariable("NUM_COLS", $num);
            $this->tpl->parseCurrentBlock();
        }
    }
    
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $ret = &$this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if (!$cmd) {
                    $cmd = "view";
                }
                $cmd .= "Object";
                $this->$cmd();

                break;
        }
        return true;
    }
    
    /**
    * get tabs
    * @access	public
    * @param	object	tabs gui object
    */
    public function getTabs()
    {
        $rbacsystem = $this->rbacsystem;

        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "view"),
                array("view",""),
                "",
                ""
            );

            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"),
                array("perm","info","owner"),
                'ilpermissiongui'
            );
        }
    }
} // END class.ilObjObjectFolderGUI
