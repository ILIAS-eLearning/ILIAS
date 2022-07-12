<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * This class represents a email property in a property form.
 * @author Alexander Killing <killing@leifos.de>
 */
class ilEMailInputGUI extends ilFormPropertyGUI
{
    protected string $value = "";
    protected int  $size = 30;
    protected int $max_length = 80;
    protected bool $allowRFC822 = false;
    protected bool $retype = false;
    protected string $retypevalue = '';

    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
        $this->setRetype(false);
    }

    public function setValue(string $a_value) : void
    {
        $this->value = $a_value;
    }

    public function getValue() : string
    {
        return $this->value;
    }

    public function setValueByArray(array $a_values) : void
    {
        $this->setValue($a_values[$this->getPostVar()] ?? "");
        $this->setRetypeValue($a_values[$this->getPostVar() . '_retype'] ?? "");
    }
    
    /**
     * Allow extended email address format
     *
     * "example@example.com" vs "example <example@example.com>"
     */
    public function allowRFC822(bool $a_value) : void
    {
        $this->allowRFC822 = $a_value;
    }

    // get string parameter kindly
    protected function sanitize($key) : string
    {
        $t = $this->refinery->kindlyTo()->string();
        return ilUtil::stripSlashes(
            (string) ($this->getRequestParam($key, $t) ?? ""),
            !$this->allowRFC822
        );
    }

    public function checkInput() : bool
    {
        $lng = $this->lng;

        if ($this->getRequired() && trim($this->str($this->getPostVar())) == "") {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }
        if ($this->getRetype() &&
            ($this->sanitize($this->getPostVar()) != $this->sanitize($this->getPostVar() . '_retype'))) {
            $this->setAlert($lng->txt('email_not_match'));
            return false;
        }
        if (!ilUtil::is_email($this->sanitize($this->getPostVar())) &&
            trim($this->sanitize($this->getPostVar())) != ""
        ) {
            $this->setAlert($lng->txt("email_not_valid"));
            return false;
        }
        return true;
    }

    public function getInput() : string
    {
        return trim($this->sanitize($this->getPostVar()));
    }

    public function insert(ilTemplate $a_tpl) : void
    {
        $lng = $this->lng;

        $ptpl = new ilTemplate('tpl.prop_email.html', true, true, 'Services/Form');

        if ($this->getRetype()) {
            $ptpl->setCurrentBlock('retype_email');
            $ptpl->setVariable('RSIZE', $this->getSize());
            $ptpl->setVariable('RID', $this->getFieldId());
            $ptpl->setVariable('RMAXLENGTH', $this->getMaxLength());
            $ptpl->setVariable('RPOST_VAR', $this->getPostVar());

            $retype_value = $this->getRetypeValue();
            $ptpl->setVariable('PROPERTY_RETYPE_VALUE', ilLegacyFormElementsUtil::prepareFormOutput($retype_value));
            if ($this->getDisabled()) {
                $ptpl->setVariable('RDISABLED', ' disabled="disabled"');
            }
            $ptpl->setVariable('TXT_RETYPE', $lng->txt('form_retype_email'));
            $ptpl->parseCurrentBlock();
        }

        $ptpl->setVariable('POST_VAR', $this->getPostVar());
        $ptpl->setVariable('ID', $this->getFieldId());
        $ptpl->setVariable('PROPERTY_VALUE', ilLegacyFormElementsUtil::prepareFormOutput($this->getValue()));
        $ptpl->setVariable('SIZE', $this->getSize());
        $ptpl->setVariable('MAXLENGTH', $this->getMaxLength());
        if ($this->getDisabled()) {
            $ptpl->setVariable('DISABLED', ' disabled="disabled"');
            $ptpl->setVariable('HIDDEN_INPUT', $this->getHiddenTag($this->getPostVar(), $this->getValue()));
        }
        
        if ($this->getRequired()) {
            $ptpl->setVariable("REQUIRED", "required=\"required\"");
        }

        $a_tpl->setCurrentBlock('prop_generic');
        $a_tpl->setVariable('PROP_GENERIC', $ptpl->get());
        $a_tpl->parseCurrentBlock();
    }

    public function setRetype(bool $a_val) : void
    {
        $this->retype = $a_val;
    }

    public function getRetype() : bool
    {
        return $this->retype;
    }

    public function setRetypeValue(string $a_retypevalue) : void
    {
        $this->retypevalue = $a_retypevalue;
    }

    public function getRetypeValue() : string
    {
        return $this->retypevalue;
    }

    public function setSize(int $size) : void
    {
        $this->size = $size;
    }

    public function getSize() : int
    {
        return $this->size;
    }

    public function setMaxLength(int $max_length) : void
    {
        $this->max_length = $max_length;
    }

    public function getMaxLength() : int
    {
        return $this->max_length;
    }
}
