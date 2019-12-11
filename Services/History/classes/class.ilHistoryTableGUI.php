<?php
include_once('./Services/Table/classes/class.ilTable2GUI.php');
include_once('./Services/History/classes/class.ilHistory.php');
include_once('./Services/User/classes/class.ilUserUtil.php');
/**
 * Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE
 * Date: 24.10.14
 * Time: 10:35
 */
/**
* Lists History entrys in chronological order
*
* @author Fabian Wolf <wolf@leifos.com>
* @version $Id$
*
* @ingroup ModuleHistory
*/
class ilHistoryTableGUI extends ilTable2GUI
{
    protected $obj_id;
    protected $obj_type;
    protected $ref_id;
    protected $ilCtrl;

    protected $comment_visibility = false;

    
    public function __construct($a_parent_obj, $a_parent_cmd, $a_obj_id, $a_obj_type = null)
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setObjId($a_obj_id);
        $this->setObjType($a_obj_type);
        $this->ilCtrl = $ilCtrl;
    }
    
    /**
    * Get data and put it into an array
    */
    public function getDataFromDb()
    {
        $entries = ilHistory::_getEntriesForObject($this->getObjId(), $this->getObjType());
        $this->setData($entries);
    }

    /**
     * init table
     */
    public function initTable()
    {
        $this->setRowTemplate("tpl.history_row.html", "Services/History");
        $this->setFormAction($this->ilCtrl->getFormAction($this->getParentObject()));

        $this->setTitle($this->lng->txt("history"));
        $this->addColumn($this->lng->txt("user"), "", "25%");
        $this->addColumn($this->lng->txt("date"), "", "25%");
        $this->addColumn($this->lng->txt("action"), "", "50%");

        $this->getDataFromDb();
    }
    
    /**
    * Fill a single data row.
    */
    protected function fillRow($a_set)
    {
        $this->tpl->setVariable("TXT_USER", ilUserUtil::getNamePresentation($a_set["user_id"], false, false));
        $this->tpl->setVariable(
            'TXT_DATE',
            ilDatePresentation::formatDate(new ilDateTime($a_set["date"], IL_CAL_DATETIME))
        );
        $this->tpl->setVariable("TXT_ACTION", $this->createInfoText($a_set));

        if ($this->getObjType() == "lm") {
            $obj_arr = explode(":", $a_set["obj_type"]);
            switch ($obj_arr[1]) {
                case "st":
                    $img_type = "st";
                    $class = "ilstructureobjectgui";
                    $cmd = "view";
                    break;

                case "pg":
                    $img_type = "pg";
                    $class = "illmpageobjectgui";
                    $cmd = "edit";
                    break;

                default:
                    $img_type = $obj_arr[0];
                    $class = "";
                    $cmd = "view";
                    break;
            }

            $this->tpl->setCurrentBlock("item_icon");
            $this->tpl->setVariable("SRC_ICON", ilUtil::getImagePath("icon_" . $img_type . ".svg"));
            $this->tpl->parseCurrentBlock();

            if ($class != "") {
                $this->tpl->setCurrentBlock("item_link");
                $this->ilCtrl->setParameterByClass($class, "obj_id", $a_set["obj_id"]);
                $this->tpl->setVariable(
                    "HREF_LINK",
                    $this->ilCtrl->getLinkTargetByClass($class, $cmd)
                );
                $this->tpl->setVariable("TXT_LINK", $a_set["title"]);
                $this->tpl->parseCurrentBlock();
            } else {
                $this->tpl->setCurrentBlock("item_title");
                $this->tpl->setVariable(
                    "TXT_TITLE",
                    ilObject::_lookupTitle($a_set["obj_id"])
                );
                $this->tpl->parseCurrentBlock();
            }
        }

        if ($this->isCommentVisible() && $a_set["user_comment"] != "") {
            $this->tpl->setCurrentBlock("user_comment");
            $this->tpl->setVariable("TXT_COMMENT", $this->lng->txt("comment"));
            $this->tpl->setVariable("TXT_USER_COMMENT", $a_set["user_comment"]);
            $this->tpl->parseCurrentBlock();
        }
    }

    /**
     * format info parameters into info text
     * @param $a_set
     * @return mixed|string
     */
    protected function createInfoText($a_set)
    {
        $info_params = explode(",", $a_set["info_params"]);

        switch ($this->getObjType()) {
            case "lm":
                $info_text = $this->lng->txt("hist_" . str_replace(":", "_", $a_set["obj_type"]) .
            "_" . $a_set["action"]);
                break;
            default:
                $info_text = $this->lng->txt("hist_" . str_replace(":", "_", $this->getObjType()) .
                    "_" . $a_set["action"]);
                break;
        }

        $i=1;
        foreach ($info_params as $info_param) {
            $info_text = str_replace("%" . $i, $info_param, $info_text);
            $i++;
        }

        return $info_text;
    }

    /**
     * set comments visible
     *
     * @param $a_visible
     */
    public function setCommentVisibility($a_visible)
    {
        $this->comment_visibility = (bool) $a_visible;
    }

    /**
     * comments visible?
     * @return bool
     */
    public function isCommentVisible()
    {
        return $this->comment_visibility;
    }

    /**
     * set object id
     * @param $a_obj_id
     */
    public function setObjId($a_obj_id)
    {
        $this->obj_id = $a_obj_id;
    }

    /**
     * get object id
     * @return mixed
     */
    public function getObjId()
    {
        return $this->obj_id;
    }

    /**
     * set object type (not required)
     * @param $a_obj_type
     */
    public function setObjType($a_obj_type)
    {
        $this->obj_type = $a_obj_type;
    }

    /**
     * get object type (if not set, it will be set via object id)
     * @return mixed
     */
    public function getObjType()
    {
        if (!$this->obj_type) {
            $this->setObjType(ilObject::_lookupType($this->getObjId()));
        }
        return $this->obj_type;
    }
}
