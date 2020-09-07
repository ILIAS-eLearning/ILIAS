<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2007 ILIAS open source, University of Cologne            |
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

include_once("./Services/Table/interfaces/interface.ilTableFilterItem.php");
include_once("./Services/Form/classes/class.ilFormPropertyGUI.php");

/**
* This class represents a repository selector in a property form.
*
* The implementation is kind of beta. It looses all other inputs, if the
* selector link is used.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup	ServicesForm
* @ilCtrl_IsCalledBy ilRepositorySelectorInputGUI: ilFormPropertyDispatchGUI
*/
class ilRepositorySelectorInputGUI extends ilFormPropertyGUI implements ilTableFilterItem
{
    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilObjectDataCache
     */
    protected $obj_data_cache;

    protected $options;
    protected $value;
    protected $container_types = array("root", "cat", "grp", "fold", "crs");
    
    /**
    * Constructor
    *
    * @param	string	$a_title	Title
    * @param	string	$a_postvar	Post Variable
    */
    public function __construct($a_title = "", $a_postvar = "")
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->tree = $DIC->repositoryTree();
        $this->user = $DIC->user();
        $this->obj_data_cache = $DIC["ilObjDataCache"];
        $lng = $DIC->language();
        
        parent::__construct($a_title, $a_postvar);
        $this->setClickableTypes($this->container_types);
        $this->setHeaderMessage($lng->txt('search_area_info'));
        $this->setType("rep_select");
        $this->setSelectText($lng->txt("select"));
    }

    /**
    * Set Value.
    *
    * @param	int 		ref id of selected repository item
    */
    public function setValue($a_value)
    {
        $this->value = $a_value;
    }

    /**
    * Get Value.
    *
    * @return	int 		ref id of selected repository item
    */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
    * Set value by array
    *
    * @param	array	$a_values	value array
    */
    public function setValueByArray($a_values)
    {
        $this->setValue($a_values[$this->getPostVar()]);
    }

    /**
    * Set select link text
    *
    * @param	string	select link text
    */
    public function setSelectText($a_val)
    {
        $this->select_text = $a_val;
    }
    
    /**
    * Get select link text
    *
    * @return	string	select link text
    */
    public function getSelectText()
    {
        return $this->select_text;
    }
    
    /**
    * Set header message
    *
    * @param	string		header message
    */
    public function setHeaderMessage($a_val)
    {
        $this->hm = $a_val;
    }
    
    /**
    * Get header message
    *
    * @return	string		header message
    */
    public function getHeaderMessage()
    {
        return $this->hm;
    }
    
    /**
    * Set clickable types
    *
    * @param	array	 clickable types
    */
    public function setClickableTypes($a_types)
    {
        $this->clickable_types = $a_types;
    }
    
    /**
    * Get  clickable types
    *
    * @return	array	 clickable types
    */
    public function getClickableTypes()
    {
        return $this->clickable_types;
    }
    
    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        $lng = $this->lng;
        
        $_POST[$this->getPostVar()] =
            ilUtil::stripSlashes($_POST[$this->getPostVar()]);

        if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == "") {
            $this->setAlert($lng->txt("msg_input_is_required"));

            return false;
        }
        return true;
    }

    /**
    * Select Repository Item
    */
    public function showRepositorySelection()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $tree = $this->tree;
        $ilUser = $this->user;
        
        include_once 'Services/Repository/classes/class.ilRepositorySelectorExplorerGUI.php';
        $ilCtrl->setParameter($this, "postvar", $this->getPostVar());

        ilUtil::sendInfo($this->getHeaderMessage());

        $exp = new ilRepositorySelectorExplorerGUI(
            $this,
            "showRepositorySelection",
            $this,
            "selectRepositoryItem",
            "root_id"
        );
        $exp->setTypeWhiteList($this->getVisibleTypes());
        $exp->setClickableTypes($this->getClickableTypes());

        if ($this->getValue()) {
            $exp->setPathOpen($this->getValue());
            $exp->setHighlightedNode($this->getHighlightedNode());
        }

        if ($exp->handleCommand()) {
            return;
        }
        // build html-output
        $tpl->setContent($exp->getHTML());
    }
    
    /**
    * Select repository item
    */
    public function selectRepositoryItem()
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        $anchor = $ilUser->prefs["screen_reader_optimization"]
            ? $this->getFieldId() . "_anchor"
            : "";

        $this->setValue($_GET["root_id"]);
        $this->writeToSession();

        $ilCtrl->returnToParent($this, $anchor);
    }
    
    /**
    * Reset
    */
    public function reset()
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        $anchor = $ilUser->prefs["screen_reader_optimization"]
            ? $this->getFieldId() . "_anchor"
            : "";

        $this->setValue("");
        $this->writeToSession();

        $ilCtrl->returnToParent($this, $anchor);
    }
    
    /**
    * Render item
    */
    public function render($a_mode = "property_form")
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilObjDataCache = $this->obj_data_cache;
        $tree = $this->tree;
        
        $tpl = new ilTemplate("tpl.prop_rep_select.html", true, true, "Services/Form");

        $tpl->setVariable("POST_VAR", $this->getPostVar());
        $tpl->setVariable("ID", $this->getFieldId());
        $tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($this->getValue()));
        $tpl->setVariable("TXT_SELECT", $this->getSelectText());
        $tpl->setVariable("TXT_RESET", $lng->txt("reset"));
        switch ($a_mode) {
            case "property_form":
                $parent_gui = "ilpropertyformgui";
                break;
                
            case "table_filter":
                $parent_gui = get_class($this->getParent());
                break;
        }

        $ilCtrl->setParameterByClass(
            "ilrepositoryselectorinputgui",
            "postvar",
            $this->getPostVar()
        );
        $tpl->setVariable(
            "HREF_SELECT",
            $ilCtrl->getLinkTargetByClass(
                array($parent_gui, "ilformpropertydispatchgui", "ilrepositoryselectorinputgui"),
                "showRepositorySelection"
            )
        );
        $tpl->setVariable(
            "HREF_RESET",
            $ilCtrl->getLinkTargetByClass(
                array($parent_gui, "ilformpropertydispatchgui", "ilrepositoryselectorinputgui"),
                "reset"
            )
        );

        if ($this->getValue() > 0 && $this->getValue() != ROOT_FOLDER_ID) {
            $tpl->setVariable(
                "TXT_ITEM",
                $ilObjDataCache->lookupTitle($ilObjDataCache->lookupObjId($this->getValue()))
            );
        } else {
            $nd = $tree->getNodeData(ROOT_FOLDER_ID);
            $title = $nd["title"];
            if ($title == "ILIAS") {
                $title = $lng->txt("repository");
            }
            if (in_array($nd["type"], $this->getClickableTypes())) {
                $tpl->setVariable("TXT_ITEM", $title);
            }
        }
        return $tpl->get();
    }
    
    /**
    * Insert property html
    *
    * @return	int	Size
    */
    public function insert($a_tpl)
    {
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $this->render());
        $a_tpl->parseCurrentBlock();
    }

    /**
    * Get HTML for table filter
    */
    public function getTableFilterHTML()
    {
        $html = $this->render("table_filter");
        return $html;
    }

    /**
     * Returns the highlighted object
     *
     * @return int ref_id (node)
     */
    protected function getHighlightedNode()
    {
        $tree = $this->tree;

        if (!in_array(ilObject::_lookupType($this->getValue(), true), $this->getVisibleTypes())) {
            return $tree->getParentId($this->getValue());
        }

        return $this->getValue();
    }

    /**
     * returns all visible types like container and clickable types
     *
     * @return array
     */
    protected function getVisibleTypes()
    {
        return array_merge((array) $this->container_types, (array) $this->getClickableTypes());
    }
}
