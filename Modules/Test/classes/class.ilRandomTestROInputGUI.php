<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* This class represents a random test input property in a property form.
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilRandomTestROInputGUI extends ilSubEnabledFormPropertyGUI
{
    protected $values = array();
    
    /**
    * Constructor
    *
    * @param	string	$a_title	Title
    * @param	string	$a_postvar	Post Variable
    */
    public function __construct($a_title = "", $a_postvar = "")
    {
        parent::__construct($a_title, $a_postvar);
        $this->setRequired(true);
    }

    /**
    * Set Value.
    *
    * @param	string	$a_value	Value
    */
    public function setValue($a_value)
    {
    }
    
    public function setValueByArray($a_values)
    {
    }

    /**
    * Set Values
    *
    * @param	array	$a_value	Value
    */
    public function setValues($a_values)
    {
        $this->values = $a_values;
    }

    /**
    * Get Values
    *
    * @return	array	Values
    */
    public function getValues()
    {
        return $this->values;
    }

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
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
        
        $tpl = new ilTemplate("tpl.prop_randomtestroinput.html", true, true, "Modules/Test");
        $i = 0;
        foreach ($this->values as $value) {
            if ($value['num_of_q'] > 0) {
                $tpl->setCurrentBlock("num_of_q");
                $tpl->setVariable("NUM_OF_Q", $value['num_of_q']);
                $tpl->setVariable("TEXT_FROM", $lng->txt('questions_from'));
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock("row");
            $class = ($i % 2 == 0) ? "even" : "odd";
            if ($i == 0) {
                $class .= " first";
            }
            if ($i == count($this->values) - 1) {
                $class .= " last";
            }
            $tpl->setVariable("ROW_CLASS", $class);
            $tpl->setVariable("QPL_VALUE", ilUtil::prepareFormOutput($value['title']));
            $tpl->setVariable("COUNT_VALUE", "(" . $value['count'] . " " . $lng->txt('assQuestions') . ")");
            $tpl->parseCurrentBlock();
            $i++;
        }
        $tpl->setVariable("ELEMENT_ID", $this->getPostVar());

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();
    }
}
