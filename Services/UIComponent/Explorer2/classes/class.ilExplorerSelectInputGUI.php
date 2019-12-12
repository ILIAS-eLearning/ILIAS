<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/interfaces/interface.ilTableFilterItem.php");
include_once("./Services/Form/classes/class.ilFormPropertyGUI.php");

/**
 * Select explorer tree nodes input GUI
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup	ServicesForm
 */
abstract class ilExplorerSelectInputGUI extends ilFormPropertyGUI implements ilTableFilterItem
{
    /**
     * Constructor
     *
     * @param	string	$a_title	Title
     * @param	string	$a_postvar	Post Variable
     */
    public function __construct($a_title, $a_postvar, $a_explorer_gui, $a_multi = false)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $lng = $DIC->language();
        
        $this->multi_nodes = $a_multi;
        $this->explorer_gui = $a_explorer_gui;
        
        parent::__construct($a_title, $a_postvar);
        $this->setType("exp_select");
    }

    /**
     * Get explorer handle command function
     *
     * @param
     * @return
     */
    public function getExplHandleCmd()
    {
        return "handleExplorerCommand";
    }
    
    /**
     * Handle explorer command
     */
    public function handleExplorerCommand()
    {
        $val = $this->getValue();
        if (is_array($val)) {
            foreach ($val as $v) {
                $this->explorer_gui->setNodeOpen($v);
                $this->explorer_gui->setNodeSelected($v);
            }
        } elseif ($val != "") {
            $this->explorer_gui->setNodeOpen($val);
            $this->explorer_gui->setNodeSelected($val);
        }
        $this->explorer_gui->handleCommand();
    }
    
    /**
     * Get title for node id (needs to be overwritten, if explorer is not a tree eplorer
     *
     * @param
     * @return
     */
    abstract public function getTitleForNodeId($a_id);
    
    /**
     * Set Value.
     *
     * @param mixed tax node id or array of node ids (multi mode)
     */
    public function setValue($a_value)
    {
        if ($this->multi_nodes && !is_array($a_value)) {
            if ($a_value !== false) {
                $this->value = array($a_value);
            } else {
                $this->value = array();
            }
        } else {
            $this->value = $a_value;
        }
    }

    /**
     * Get Value.
     *
     * @return mixed tax node id or array of node ids (multi mode)
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
     * Check input, strip slashes etc. set alert, if input is not ok.
     *
     * @return	boolean		Input ok, true/false
     */
    public function checkInput()
    {
        $lng = $this->lng;
        
        // sanitize
        if ($this->multi_nodes) {
            if (!is_array($_POST[$this->getPostVar()])) {
                $_POST[$this->getPostVar()] = array();
            }
            
            foreach ($_POST[$this->getPostVar()] as $k => $v) {
                $_POST[$this->getPostVar()][$k] = ilUtil::stripSlashes($v);
            }
        } else {
            $_POST[$this->getPostVar()] = ilUtil::stripSlashes($_POST[$this->getPostVar()]);
        }
        
        // check required
        if ($this->getRequired()) {
            if ((!$this->multi_nodes && trim($_POST[$this->getPostVar()]) == "") ||
                ($this->multi_nodes && count($_POST[$this->getPostVar()]) == 0)) {
                $this->setAlert($lng->txt("msg_input_is_required"));
                return false;
            }
        }
        return true;
    }

    
    /**
     * Render item
     */
    public function render($a_mode = "property_form")
    {
        $lng = $this->lng;
        
        include_once("./Services/YUI/classes/class.ilYuiUtil.php");
        ilYuiUtil::initPanel();
        $GLOBALS["tpl"]->addJavascript("./Services/UIComponent/Explorer2/js/Explorer2.js");
        
        $tpl = new ilTemplate("tpl.prop_expl_select.html", true, true, "Services/UIComponent/Explorer2");

        if ($a_mode != "property_form") {
            $tpl->touchBlock("tiny_presentation");
        }

        // set values
        $val = $this->getValue();
        if (is_array($val)) {
            $val_txt = $sep = "";
            foreach ($val as $v) {
                $tpl->setCurrentBlock("node_hid");
                $tpl->setVariable("HID_NAME", $this->getPostVar() . "[]");
                $tpl->setVariable("HID_VAL", $v);
                $tpl->parseCurrentBlock();
                $val_txt.= $sep . $this->getTitleForNodeId($v);
                $sep = ", ";
                $this->explorer_gui->setNodeOpen($v);
                $this->explorer_gui->setNodeSelected($v);
            }
            $tpl->setVariable("VAL_TXT", $val_txt);
        } elseif ($val != "") {
            $tpl->setCurrentBlock("node_hid");
            $tpl->setVariable("HID_NAME", $this->getPostVar());
            $tpl->setVariable("HID_VAL", $val);
            $tpl->parseCurrentBlock();
            $tpl->setVariable("VAL_TXT", $this->getTitleForNodeId($val));
            $this->explorer_gui->setNodeOpen($val);
            $this->explorer_gui->setNodeSelected($val);
        }

        $tpl->setVariable("POST_VAR", $this->getPostVar());
        $tpl->setVariable("ID", $this->getFieldId());
        //		$tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($this->getValue()));
        
        //added disabled
        if (!$this->disabled) {
            $tpl->setCurrentBlock("txt_select");
            $tpl->setVariable("TXT_SELECT", $lng->txt("select"));
            $tpl->setVariable("ID_TXT_SELECT", $this->getFieldId());
            $tpl->parseCurrentBlock();

            $tpl->setCurrentBlock("txt_reset");
            $tpl->setVariable("TXT_RESET", $lng->txt("reset"));
            $tpl->setVariable("ID_TXT_RESET", $this->getFieldId());
            $tpl->parseCurrentBlock();
        }
        
        $tpl->setVariable("EXPL", $this->explorer_gui->getHTML());
        
        $top_tb = new ilToolbarGUI();
        
        include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
        
        $button = ilLinkButton::getInstance();
        $button->setCaption("select");
        $button->addCSSClass("ilExplSelectInputButS");
        $button->setOmitPreventDoubleSubmission(true);
        $top_tb->addButtonInstance($button);
        
        $button = ilLinkButton::getInstance();
        $button->setCaption("cancel");
        $button->addCSSClass("ilExplSelectInputButC");
        $button->setOmitPreventDoubleSubmission(true);
        $top_tb->addButtonInstance($button);
        
        // :TODO: we should probably clone the buttons properly
        $tpl->setVariable("TOP_TB", $top_tb->getHTML());
        $tpl->setVariable("BOT_TB", $top_tb->getHTML());

        //$tpl->setVariable("HREF_SELECT",
        //	$ilCtrl->getLinkTargetByClass(array($parent_gui, "ilformpropertydispatchgui", "ilrepositoryselectorinputgui"),
        //	"showRepositorySelection"));

        /*if ($this->getValue() > 0 && $this->getValue() != ROOT_FOLDER_ID)
        {
            $tpl->setVariable("TXT_ITEM",
                $ilObjDataCache->lookupTitle($ilObjDataCache->lookupObjId($this->getValue())));
        }
        else
        {
            $nd = $tree->getNodeData(ROOT_FOLDER_ID);
            $title = $nd["title"];
            if ($title == "ILIAS")
            {
                $title = $lng->txt("repository");
            }
            if (in_array($nd["type"], $this->getClickableTypes()))
            {
                $tpl->setVariable("TXT_ITEM", $title);
            }
        }*/
        
        return $tpl->get();
    }
    
    /**
     * Insert property html
     * @param ilTemplate
     */
    public function insert(&$a_tpl)
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
}
