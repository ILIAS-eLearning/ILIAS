<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php";

/**
* This class represents a selection list property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilSuggestedSolutionSelectorGUI extends ilSubEnabledFormPropertyGUI
{
    protected $options;
    protected $value;
    protected $addCommand;
    protected $intlink;
    protected $intlinktext;
    
    /**
    * Constructor
    *
    * @param	string	$a_title	Title
    * @param	string	$a_postvar	Post Variable
    */
    public function __construct($a_title = "", $a_postvar = "")
    {
        parent::__construct($a_title, $a_postvar);
        $this->setType("select");
    }

    /**
    * Set Options.
    *
    * @param	array	$a_options	Options. Array ("value" => "option_text")
    */
    public function setOptions($a_options)
    {
        $this->options = $a_options;
    }

    /**
    * Get Options.
    *
    * @return	array	Options. Array ("value" => "option_text")
    */
    public function getOptions()
    {
        return $this->options;
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
    * Set internal link.
    *
    * @param	string	$a_value	Value
    */
    public function setInternalLink($a_value)
    {
        $this->intlink = $a_value;
    }

    /**
    * Get internal linnk
    *
    * @return	string	Internal link
    */
    public function getInternalLink()
    {
        return $this->intlink;
    }
    
    /**
    * Set internal link.text
    *
    * @param	string	$a_value	Internal link text
    */
    public function setInternalLinkText($a_value)
    {
        $this->intlinktext = $a_value;
    }

    /**
    * Get internal link text
    *
    * @return	string	Internal link text
    */
    public function getInternalLinkText()
    {
        return $this->intlinktext;
    }
    
    /**
    * Set add command.
    *
    * @param	string	$a_add_command	add command
    */
    public function setAddCommand($a_add_command)
    {
        $this->addCommand = $a_add_command;
    }

    /**
    * Get add command.
    *
    * @return	string	add command
    */
    public function getAddCommand()
    {
        return ($this->addCommand) ? $this->addCommand : "addInternalLink";
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
        global $DIC;
        $lng = $DIC['lng'];
        
        $_POST[$this->getPostVar()] =
            ilUtil::stripSlashes($_POST[$this->getPostVar()]);
        if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == "") {
            $this->setAlert($lng->txt("msg_input_is_required"));

            return false;
        }
        return $this->checkSubItemsInput();
    }

    /**
    * Insert property html
    *
    * @return	int	Size
    */
    public function insert(&$a_tpl)
    {
        global $DIC;
        $lng = $DIC['lng'];

        $template = new ilTemplate("tpl.prop_suggestedsolutionselector.html", true, true, "Modules/TestQuestionPool");
        
        foreach ($this->getOptions() as $option_value => $option_text) {
            $template->setCurrentBlock("prop_intlink_select_option");
            $template->setVariable("VAL_SELECT_OPTION", $option_value);
            if ($option_value == $this->getValue()) {
                $template->setVariable(
                    "CHK_SEL_OPTION",
                    'selected="selected"'
                );
            }
            $template->setVariable("TXT_SELECT_OPTION", $option_text);
            $template->parseCurrentBlock();
        }
        if ($this->getInternalLink()) {
            $template->setCurrentBlock("delete_internallink");
            $template->setVariable("TEXT_DELETE_INTERNALLINK", $lng->txt("remove_solution"));
            $template->setVariable("POST_VAR", $this->getPostVar());
            $template->parseCurrentBlock();
            $template->setCurrentBlock("internal_link");
            $template->setVariable("HREF_INT_LINK", $this->getInternalLink());
            $template->setVariable("TEXT_INT_LINK", $this->getInternalLinkText());
            $template->parseCurrentBlock();
        }
        $template->setCurrentBlock("prop_internallink_selector");
        $template->setVariable("POST_VAR", $this->getPostVar());
        if ($this->getDisabled()) {
            $template->setVariable(
                "DISABLED",
                " disabled=\"disabled\""
            );
        }
        $template->setVariable("TEXT_ADD_INTERNALLINK", ($this->getInternalLink()) ? $lng->txt("change") : $lng->txt("add"));
        $template->setVariable("CMD_ADD_INTERNALLINK", $this->getAddCommand());
        $template->parseCurrentBlock();
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $template->get());
        $a_tpl->parseCurrentBlock();
    }
}
