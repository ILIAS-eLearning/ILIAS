<?php declare(strict_types=1);

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * This class represents a typical learning time property in a property form.
 * @author     Alex Killing <alex.killing@gmx.de>
 * @version    $Id$
 * @ingroup    ServicesMetaData
 */
class ilTypicalLearningTimeInputGUI extends ilFormPropertyGUI
{
    protected array $value;
    protected bool $valid = true;
    protected string $lom_duration = '';

    public function __construct(string $a_title = "", string $a_postvar = "")
    {
        parent::__construct($a_title, $a_postvar);

        $this->lng->loadLanguageModule("meta");
        $this->setType("typical_learntime");
        $this->setValue(array(0, 0, 0, 0, 0));
    }

    public function setValue(array $a_value) : void
    {
        $this->value = $a_value;
    }

    public function setValueByLOMDuration(string $a_value) : void
    {
        $this->lom_duration = $a_value;
        $this->valid        = true;

        $tlt = ilMDUtils::_LOMDurationToArray($a_value);

        if (!$tlt) {
            $this->setValue(array(0, 0, 0, 0, 0));
            if ($a_value != "") {
                $this->valid = false;
            }
        } else {
            $this->setValue($tlt);
        }
    }

    /**
     * @return int[]
     */
    public function getValue() : array
    {
        return $this->value;
    }

    public function setValueByArray(array $a_values) : void
    {
        $this->setValue($a_values[$this->getPostVar()]);
    }

    public function checkInput() : bool
    {

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
            $this->setAlert($this->lng->txt("msg_input_is_required"));
            return false;
        }

        return true;
    }

    public function __buildMonthsSelect(string $sel_month) : string
    {
        $options = [];
        for ($i = 0; $i <= 24; $i++) {
            $options[$i] = sprintf('%02d', $i);
        }
        return ilLegacyFormElementsUtil::formSelect($sel_month, $this->getPostVar() . '[mo]', $options, false, true);
    }

    public function __buildDaysSelect(string $sel_day) : string
    {
        $options = [];
        for ($i = 0; $i <= 31; $i++) {
            $options[$i] = sprintf('%02d', $i);
        }
        return ilLegacyFormElementsUtil::formSelect($sel_day, $this->getPostVar() . '[d]', $options, false, true);
    }

    public function insert(ilTemplate $a_tpl) : void
    {
        $ttpl = new ilTemplate("tpl.prop_typical_learning_time.html", true, true, "Services/MetaData");
        $val  = $this->getValue();

        $ttpl->setVariable("TXT_MONTH", $this->lng->txt('md_months'));
        $ttpl->setVariable("SEL_MONTHS", $this->__buildMonthsSelect((string) $val[0]));
        $ttpl->setVariable("SEL_DAYS", $this->__buildDaysSelect((string) $val[1]));

        $ttpl->setVariable("TXT_DAYS", $this->lng->txt('md_days'));
        $ttpl->setVariable("TXT_TIME", $this->lng->txt('md_time'));

        $ttpl->setVariable("SEL_TLT",
            ilLegacyFormElementsUtil::makeTimeSelect(
                $this->getPostVar(),
                $val[4] ? false : true,
                $val[2],
                $val[3],
                $val[4],
                false
            )
        );
        $ttpl->setVariable("TLT_HINT", $val[4] ? '(hh:mm:ss)' : '(hh:mm)');

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
