<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Form/classes/class.ilFormPropertyGUI.php");
include_once 'Services/UIComponent/Toolbar/interfaces/interface.ilToolbarItem.php';

/**
* This class represents a text property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilAlphabetInputGUI extends ilFormPropertyGUI implements ilToolbarItem
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    protected $letters;
    protected $parent_object;
    protected $parent_cmd;
    protected $highlight;
    protected $highlight_letter;

    /**
     * @var bool
     */
    protected $fix_db_umlauts = false;

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
        $this->ctrl = $DIC->ctrl();
        parent::__construct($a_title, $a_postvar);
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
    * Set value by array
    *
    * @param	array	$a_values	value array
    */
    public function setValueByArray($a_values)
    {
        $this->setValue($a_values[$this->getPostVar()]);
    }

    /**
     * Set letters available
     *
     * @param	array	letters
     */
    public function setLetters($a_val)
    {
        $this->letters = $a_val;
    }
    
    /**
     * Get letters available
     *
     * @return	array	letters
     */
    public function getLetters()
    {
        return $this->letters;
    }

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        $lng = $this->lng;
        
        $_POST[$this->getPostVar()] = ilUtil::stripSlashes($_POST[$this->getPostVar()]);
        if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == "") {
            $this->setAlert($lng->txt("msg_input_is_required"));

            return false;
        }
        
        return true;
    }
    
    /**
     * Set fix db umlauts
     *
     * @param bool $a_val fix db umlauts
     */
    public function setFixDBUmlauts($a_val)
    {
        $this->fix_db_umlauts = $a_val;
    }
    
    /**
     * Get fix db umlauts
     *
     * @return bool fix db umlauts
     */
    public function getFixDBUmlauts()
    {
        return $this->fix_db_umlauts;
    }
    
    /**
     * Fix db umlauts
     *
     * @param
     * @return
     */
    public function fixDBUmlauts($l)
    {
        if ($this->fix_db_umlauts && !ilUtil::dbSupportsDisctinctUmlauts()) {
            $l = str_replace(array("Ä", "Ö", "Ü", "ä", "ö", "ü"), array("A", "O", "U", "a", "o", "u"), $l);
        }
        return $l;
    }
    
    
    /**
    * Render item
    */
    protected function render($a_mode = "")
    {
        die("only implemented for toolbar usage");
    }
    
    /**
    * Insert property html
    *
    * @return	int	Size
    */
    //	function insert($a_tpl)
    //	{
    //		$a_tpl->setCurrentBlock("prop_generic");
    //		$a_tpl->setVariable("PROP_GENERIC", "zz");
    //		$a_tpl->parseCurrentBlock();
    //	}
    
    /**
     * Set parent cmd
     *
     * @param	object	parent object
     * @param	string	parent command
     */
    public function setParentCommand($a_obj, $a_cmd)
    {
        $this->parent_object = $a_obj;
        $this->parent_cmd = $a_cmd;
    }
    
    /**
     * Set highlighted
     *
     * @param
     * @return
     */
    public function setHighlighted($a_high_letter)
    {
        $this->highlight = true;
        $this->highlight_letter = $a_high_letter;
    }
    
    /**
    * Get HTML for toolbar
    */
    public function getToolbarHTML()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $lng->loadLanguageModule("form");

        $tpl = new ilTemplate("tpl.prop_alphabet.html", true, true, "Services/Form");
        foreach ((array) $this->getLetters() as $l) {
            $l = $this->fixDBUmlauts($l);
            $tpl->setCurrentBlock("letter");
            $tpl->setVariable("TXT_LET", $l);
            $ilCtrl->setParameter($this->parent_object, "letter", rawurlencode($l));
            $tpl->setVariable("TXT_LET", $l);
            $tpl->setVariable("HREF_LET", $ilCtrl->getLinkTarget($this->parent_object, $this->parent_cmd));
            if ($this->highlight && $this->highlight_letter !== null && $this->highlight_letter == $l) {
                $tpl->setVariable("CLASS", ' class="ilHighlighted" ');
            }
            $tpl->parseCurrentBlock();
        }
        $ilCtrl->setParameter($this->parent_object, "letter", "");
        $tpl->setVariable("TXT_ALL", $lng->txt("form_alphabet_all"));
        $tpl->setVariable("HREF_ALL", $ilCtrl->getLinkTarget($this->parent_object, $this->parent_cmd));
        if ($this->highlight && $this->highlight_letter === null) {
            $tpl->setVariable("CLASSA", ' class="ilHighlighted" ');
        }
        return $tpl->get();
    }
}
