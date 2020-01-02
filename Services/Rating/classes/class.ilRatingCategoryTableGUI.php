<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * List rating categories
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesRating
 */
class ilRatingCategoryTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * Constructor
     * @param	object	$a_parent_obj
     * @param	string	$a_parent_cmd
     * @param	int		$a_parent_id
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_parent_id)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $this->setId("rtgcat");

        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->addColumn($this->lng->txt("position"));
        $this->addColumn($this->lng->txt("title"), "title");
        $this->addColumn($this->lng->txt("description"), "description");
        $this->addColumn($this->lng->txt("actions"));

        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate("tpl.rating_category_row.html", "Services/Rating");
        
        $this->addCommandButton("updateorder", $lng->txt("rating_update_positions"));
        
        $this->getItems($a_parent_id);
    }

    /**
     * Build item rows for given object and filter(s)
     *
     * @param	int	$a_parent_obj_id
     */
    public function getItems($a_parent_obj_id)
    {
        include_once "Services/Rating/classes/class.ilRatingCategory.php";
        $data = ilRatingCategory::getAllForObject($a_parent_obj_id);
        
        $this->setMaxCount(sizeof($data));
        $this->setData($data);
    }

    /**
     * Fill table row
     * @param	array	$a_set
     */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->tpl->setVariable("VAL_ID", $a_set["id"]);
        $this->tpl->setVariable("VAL_POS", $a_set["pos"]);
        $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
        $this->tpl->setVariable("TXT_DESCRIPTION", nl2br($a_set["description"]));
        
        $ilCtrl->setParameter($this->parent_obj, "cat_id", $a_set["id"]);
        
        $items = array();
        $items["edit"] = array($lng->txt("edit"), $ilCtrl->getLinkTarget($this->parent_obj, "edit"));
        $items["delete"] = array($lng->txt("delete"), $ilCtrl->getLinkTarget($this->parent_obj, "confirmDelete"));
        
        $this->tpl->setCurrentBlock("actions");
        foreach ($items as $item) {
            $this->tpl->setVariable("ACTION_CAPTION", $item[0]);
            $this->tpl->setVariable("ACTION_LINK", $item[1]);
            $this->tpl->parseCurrentBlock();
        }
    }
}
