<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Form/classes/class.ilFormPropertyGUI.php';

/**
 * Input for adv meta data column sorting in glossaries.
 * Please note, that data us already an array, we do not use the MultipleValues
 * interface here.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup	ModulesGlossary
 */
class ilGloAdvColSortInputGUI extends ilFormPropertyGUI
{
    
    /**
    * Constructor
    *
    * @param
    */
    public function __construct($a_title = "", $a_id = "")
    {
        global $DIC;

        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_id);
        $this->setType("glo_adv_col_sort");
    }
    
    /**
     * Set Value.
     *
     * @param	string	$a_value	Value
     */
    public function setValue($a_value)
    {
        $this->value = $a_value;
    }

    /**
     * Get Value.
     *
     * @return	string	Value
     */
    public function getValue()
    {
        return $this->value;
    }

    
    /**
     * Input should always be valid, since we sort only
     *
     * @return boolean
     */
    public function checkInput()
    {
        if (is_array($_POST[$this->getPostVar()])) {
            foreach ($_POST[$this->getPostVar()] as $k => $v) {
                $_POST[$this->getPostVar()][$k]["id"] = ilUtil::stripSlashes($_POST[$this->getPostVar()][$k]["id"]);
                $_POST[$this->getPostVar()][$k]["text"] = ilUtil::stripSlashes($_POST[$this->getPostVar()][$k]["text"]);
            }
        } else {
            $_POST[$this->getPostVar()] = array();
        }

        return true;
    }

    /**
     * render
     */
    public function render()
    {
        $lng = $this->lng;
        
        $tpl = new ilTemplate("tpl.adv_col_sort_input.html", true, true, "Modules/Glossary");
        include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
        if (is_array($this->getValue())) {
            foreach ($this->getValue() as $k => $v) {
                $tpl->setCurrentBlock("item");
                $tpl->setVariable("TEXT", $v["text"]);
                $tpl->setVariable("ID", $this->getFieldId() . "~" . $k);
                $tpl->setVariable("DOWN", ilGlyphGUI::get(ilGlyphGUI::DOWN));
                $tpl->setVariable("TXT_DOWN", $lng->txt("down"));
                $tpl->setVariable("UP", ilGlyphGUI::get(ilGlyphGUI::UP));
                $tpl->setVariable("TXT_UP", $lng->txt("up"));
                $tpl->setVariable('NAME', $this->getPostVar() . "[" . $k . "][id]");
                $tpl->setVariable('TNAME', $this->getPostVar() . "[" . $k . "][text]");
                $tpl->setVariable('VAL', ilUtil::prepareFormOutput($v["id"]));
                $tpl->setVariable('TVAL', ilUtil::prepareFormOutput($v["text"]));
                $tpl->parseCurrentBlock();
            }
        }
        
        return $tpl->get();
    }
    
    /**
    * Insert property html
    *
    */
    public function insert(&$a_tpl)
    {
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $this->render());
        $a_tpl->parseCurrentBlock();
    }
    
    /**
    * Set value by array
    *
    * @param	array	$a_values	value array
    */
    public function setValueByArray($a_values)
    {
        if ($this->getPostVar() && isset($a_values[$this->getPostVar()])) {
            $this->setValue($a_values[$this->getPostVar()]);
        }
    }
    
    /**
    * Get HTML for table filter
    */
    public function getTableFilterHTML()
    {
        $html = $this->render();
        return $html;
    }
}
