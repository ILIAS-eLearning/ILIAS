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
 * This class represents a user login property in a property form.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserLoginInputGUI extends ilFormPropertyGUI
{
    protected string $value = "";
    protected int $size = 40;
    protected int $max_length = 80;
    protected int $checkunused = 0;
    /**
     * @var bool Flag whether the html autocomplete attribute should be set to "off" or not
     */
    protected bool $autocomplete_disabled = false;
    
    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
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
        $this->setValue($a_values[$this->getPostVar()]);
    }

    public function setCurrentUserId(int $a_user_id) : void
    {
        $this->checkunused = $a_user_id;
    }

    public function getCurrentUserId() : int
    {
        return $this->checkunused;
    }

    public function setDisableHtmlAutoComplete(bool $a_value) : void
    {
        $this->autocomplete_disabled = $a_value;
    }

    public function isHtmlAutoCompleteDisabled() : bool
    {
        return $this->autocomplete_disabled;
    }
    
    public function checkInput() : bool
    {
        $lng = $this->lng;

        $value = $this->getInput();
        if ($this->getRequired() && $value == "") {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }
        if (!ilUtil::isLogin($value)) {
            $this->setAlert($lng->txt("login_invalid"));
            return false;
        }
        
        if (ilObjUser::_loginExists($value, $this->getCurrentUserId())) {
            $this->setAlert($lng->txt("login_exists"));
            return false;
        }
        
        return true;
    }

    public function getInput() : string
    {
        return trim($this->str($this->getPostVar()));
    }

    public function insert(ilTemplate $a_tpl) : void
    {
        $a_tpl->setCurrentBlock("prop_login");
        $a_tpl->setVariable("POST_VAR", $this->getPostVar());
        $a_tpl->setVariable("ID", $this->getFieldId());
        $a_tpl->setVariable("PROPERTY_VALUE", ilLegacyFormElementsUtil::prepareFormOutput($this->getValue()));
        $a_tpl->setVariable("SIZE", $this->size);
        $a_tpl->setVariable("MAXLENGTH", $this->max_length);
        if ($this->getDisabled()) {
            $a_tpl->setVariable(
                "DISABLED",
                " disabled=\"disabled\""
            );
        }
        if ($this->isHtmlAutoCompleteDisabled()) {
            $a_tpl->setVariable("AUTOCOMPLETE", "autocomplete=\"off\"");
        }
        if ($this->getRequired()) {
            $a_tpl->setVariable("REQUIRED", "required=\"required\"");
        }
        $a_tpl->parseCurrentBlock();
    }
}
