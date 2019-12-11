<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Form/classes/class.ilFormPropertyGUI.php");

/**
 * This class represents a typical learning time property in a property form.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup	ServicesMetaData
 */
class ilTypicalLearningTimeInputGUI extends ilFormPropertyGUI
{
    protected $value;
    protected $valid = true;
    
    /**
     * Constructor
     *
     * @param	string	$a_title	Title
     * @param	string	$a_postvar	Post Variable
     */
    public function __construct($a_title = "", $a_postvar = "")
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        $this->lng = $lng;
        $this->lng->loadLanguageModule("meta");
        
        parent::__construct($a_title, $a_postvar);
        $this->setType("typical_learntime");
        $this->setValue(array(0,0,0,0,0));
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
     * Set by LOM duration
     *
     * @param	string	$a_value	Value
     */
    public function setValueByLOMDuration($a_value)
    {
        $this->lom_duration = $a_value;
        $this->valid = true;
        
        include_once 'Services/MetaData/classes/class.ilMDUtils.php';
        $tlt = ilMDUtils::_LOMDurationToArray($a_value);
        
        if (!$tlt) {
            $this->setValue(array(0,0,0,0,0));
            if ($a_value != "") {
                $this->valid = false;
            }
        } else {
            $this->setValue($tlt);
        }
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
     * Check input, strip slashes etc. set alert, if input is not ok.
     *
     * @return	boolean		Input ok, true/false
     */
    public function checkInput()
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        $_POST[$this->getPostVar()][0] = (int) ilUtil::stripSlashes($_POST[$this->getPostVar()][0]);
        $_POST[$this->getPostVar()][1] = (int) ilUtil::stripSlashes($_POST[$this->getPostVar()][1]);
        $_POST[$this->getPostVar()][2] = (int) ilUtil::stripSlashes($_POST[$this->getPostVar()][2]);
        $_POST[$this->getPostVar()][3] = (int) ilUtil::stripSlashes($_POST[$this->getPostVar()][3]);
        if (isset($_POST[$this->getPostVar()][4])) {
            $_POST[$this->getPostVar()][4] = (int) ilUtil::stripSlashes($_POST[$this->getPostVar()][4]);
        }
        
        // check required
        $v = $_POST[$this->getPostVar()];
        if ($this->getRequired() && $v[0] == 0 && $v[1] == 0 &&
            $v[2] == 0 && $v[3] == 0 && (int) $v[4] == 0) {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }

        return true;
    }

    public function __buildMonthsSelect($sel_month)
    {
        for ($i = 0;$i <= 24;$i++) {
            $options[$i] = sprintf('%02d', $i);
        }
        return ilUtil::formSelect($sel_month, $this->getPostVar() . '[mo]', $options, false, true);
    }


    public function __buildDaysSelect($sel_day)
    {
        for ($i = 0;$i <= 31;$i++) {
            $options[$i] = sprintf('%02d', $i);
        }
        return ilUtil::formSelect($sel_day, $this->getPostVar() . '[d]', $options, false, true);
    }

    /**
     * Insert property html
     */
    public function insert(&$a_tpl)
    {
        $ttpl = new ilTemplate("tpl.prop_typical_learning_time.html", true, true, "Services/MetaData");
        $val = $this->getValue();
        
        $ttpl->setVariable("TXT_MONTH", $this->lng->txt('md_months'));
        $ttpl->setVariable("SEL_MONTHS", $this->__buildMonthsSelect($val[0]));
        $ttpl->setVariable("SEL_DAYS", $this->__buildDaysSelect($val[1]));
        
        $ttpl->setVariable("TXT_DAYS", $this->lng->txt('md_days'));
        $ttpl->setVariable("TXT_TIME", $this->lng->txt('md_time'));

        $ttpl->setVariable("SEL_TLT", ilUtil::makeTimeSelect(
            $this->getPostVar(),
            $val[4] ? false : true,
            $val[2],
            $val[3],
            $val[4],
            false
        ));
        $ttpl->setVariable("TLT_HINT", $tlt[4] ? '(hh:mm:ss)' : '(hh:mm)');

        if (!$this->valid) {
            $ttpl->setCurrentBlock("tlt_not_valid");
            $ttpl->setVariable("TXT_CURRENT_VAL", $this->lng->txt('meta_current_value'));
            $ttpl->setVariable("TLT", $this->lom_duration);
            $ttpl->setVariable("INFO_TLT_NOT_VALID", $this->lng->txt('meta_info_tlt_not_valid'));
            $ttpl->parseCurrentBlock();
        }
        
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $ttpl->get());
        $a_tpl->parseCurrentBlock();
    }
}
