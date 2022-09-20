<?php

declare(strict_types=1);

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
 * This class represents a password property in a property form.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPasswordInputGUI extends ilSubEnabledFormPropertyGUI
{
    protected bool $skip_syntax_check = false;
    protected string $value = "";
    protected int $size = 20;
    protected string $validateauthpost = "";
    protected bool $requiredonauth = false;
    protected int $maxlength = 0;
    protected bool $use_strip_slashes = true;
    /**
     * @var bool Flag whether the html autocomplete attribute should be set to "off" or not
     */
    protected bool $autocomplete_disabled = true;
    protected string $retypevalue = "";
    protected bool $retype = false;

    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
        $this->setRetype(true);
        $this->setSkipSyntaxCheck(false);
    }

    public function setValue(
        string $a_value
    ): void {
        $this->value = $a_value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setRetype(bool $a_val): void
    {
        $this->retype = $a_val;
    }

    public function getRetype(): bool
    {
        return $this->retype;
    }

    public function setRetypeValue(string $a_retypevalue): void
    {
        $this->retypevalue = $a_retypevalue;
    }

    public function getRetypeValue(): string
    {
        return $this->retypevalue;
    }

    public function setMaxLength(int $a_maxlength): void
    {
        $this->maxlength = $a_maxlength;
    }

    public function getMaxLength(): int
    {
        return $this->maxlength;
    }

    public function setSize(int $a_size): void
    {
        $this->size = $a_size;
    }

    public function setValueByArray(array $a_values): void
    {
        $this->setValue($a_values[$this->getPostVar()] ?? "");
        $this->setRetypeValue($a_values[$this->getPostVar() . "_retype"] ?? "");
    }

    public function getSize(): int
    {
        return $this->size;
    }

    // Set Validate required status against authentication POST var.
    public function setValidateAuthPost(string $a_validateauthpost): void
    {
        $this->validateauthpost = $a_validateauthpost;
    }

    public function getValidateAuthPost(): string
    {
        return $this->validateauthpost;
    }

    // Set input required, if authentication mode allows password setting.
    public function setRequiredOnAuth(bool $a_requiredonauth): void
    {
        $this->requiredonauth = $a_requiredonauth;
    }

    public function getRequiredOnAuth(): bool
    {
        return $this->requiredonauth;
    }

    public function setSkipSyntaxCheck(bool $a_val): void
    {
        $this->skip_syntax_check = $a_val;
    }

    public function getSkipSyntaxCheck(): bool
    {
        return $this->skip_syntax_check;
    }

    public function setDisableHtmlAutoComplete(bool $a_value): void
    {
        $this->autocomplete_disabled = $a_value;
    }

    public function isHtmlAutoCompleteDisabled(): bool
    {
        return $this->autocomplete_disabled;
    }

    /**
     * En/disable use of stripslashes. e.g on login screen.
     * Otherwise passwords containing "<" are stripped and therefor authentication
     * fails against external authentication services.
     */
    public function setUseStripSlashes(bool $a_stat): void
    {
        $this->use_strip_slashes = $a_stat;
    }

    public function getUseStripSlashes(): bool
    {
        return $this->use_strip_slashes;
    }

    public function checkInput(): bool
    {
        $lng = $this->lng;

        $pass_value = $this->getInput();
        $retype_value = ($this->getUseStripSlashes())
            ? $this->str($this->getPostVar() . "_retype")
            : $this->raw($this->getPostVar() . "_retype");

        if ($this->getRequired() && trim($pass_value) == "") {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }
        if ($this->getValidateAuthPost() != "") {
            $auth = ilAuthUtils::_getAuthMode($this->str($this->getValidateAuthPost()));

            // check, if password is required dependent on auth mode
            if ($this->getRequiredOnAuth() && ilAuthUtils::_allowPasswordModificationByAuthMode($auth)
                && trim($pass_value) == "") {
                $this->setAlert($lng->txt("form_password_required_for_auth"));
                return false;
            }

            // check, if password is allowed to be set for given auth mode
            if (trim($pass_value) != "" &&
                !ilAuthUtils::_allowPasswordModificationByAuthMode($auth)) {
                $this->setAlert($lng->txt("form_password_not_allowed_for_auth"));
                return false;
            }
        }
        if ($this->getRetype() &&
            ($pass_value != $retype_value)) {
            $this->setAlert($lng->txt("passwd_not_match"));
            return false;
        }
        if (!$this->getSkipSyntaxCheck() &&
            !ilSecuritySettingsChecker::isPassword($pass_value, $custom_error) &&
            $pass_value != "") {
            if ($custom_error != '') {
                $this->setAlert($custom_error);
            } else {
                $this->setAlert($lng->txt("passwd_invalid"));
            }
            return false;
        }

        return $this->checkSubItemsInput();
    }

    public function getInput(): string
    {
        if ($this->getUseStripSlashes()) {
            return $this->str($this->getPostVar());
        }
        return $this->raw($this->getPostVar());
    }

    public function render(): string
    {
        $lng = $this->lng;

        $ptpl = new ilTemplate("tpl.prop_password.html", true, true, "Services/Form");

        if ($this->getRetype()) {
            $ptpl->setCurrentBlock("retype");
            $ptpl->setVariable("RSIZE", $this->getSize());
            $ptpl->setVariable("RID", $this->getFieldId());
            if ($this->getMaxLength() > 0) {
                $ptpl->setCurrentBlock("rmaxlength");
                $ptpl->setVariable("RMAXLENGTH", $this->getMaxLength());
                $ptpl->parseCurrentBlock();
            }
            $ptpl->setVariable("RPOST_VAR", $this->getPostVar());

            if ($this->isHtmlAutoCompleteDisabled()) {
                $ptpl->setVariable("RAUTOCOMPLETE", "autocomplete=\"off\"");
            }

            // this is creating an "auto entry" in the setup, if the retype is missing
            /*$retype_value = ($this->getRetypeValue() != "")
                ? $this->getRetypeValue()
                : $this->getValue();*/
            $retype_value = $this->getRetypeValue();
            $ptpl->setVariable("PROPERTY_RETYPE_VALUE", ilLegacyFormElementsUtil::prepareFormOutput($retype_value));
            if ($this->getDisabled()) {
                $ptpl->setVariable(
                    "RDISABLED",
                    " disabled=\"disabled\""
                );
            }
            $ptpl->setVariable("TXT_RETYPE", $lng->txt("form_retype_password"));
            $ptpl->parseCurrentBlock();
        }

        if (strlen($this->getValue())) {
            $ptpl->setCurrentBlock("prop_password_propval");
            $ptpl->setVariable("PROPERTY_VALUE", ilLegacyFormElementsUtil::prepareFormOutput($this->getValue()));
            $ptpl->parseCurrentBlock();
        }
        $ptpl->setVariable("POST_VAR", $this->getPostVar());
        $ptpl->setVariable("ID", $this->getFieldId());
        $ptpl->setVariable("SIZE", $this->getSize());
        if ($this->getMaxLength() > 0) {
            $ptpl->setCurrentBlock("maxlength");
            $ptpl->setVariable("MAXLENGTH", $this->getMaxLength());
            $ptpl->parseCurrentBlock();
        }
        if ($this->getDisabled()) {
            $ptpl->setVariable(
                "DISABLED",
                " disabled=\"disabled\""
            );
        }
        if ($this->isHtmlAutoCompleteDisabled()) {
            $ptpl->setVariable("AUTOCOMPLETE", "autocomplete=\"off\"");
        }
        if ($this->getRequired()) {
            $ptpl->setVariable("REQUIRED", "required=\"required\"");
        }
        return $ptpl->get();
    }

    public function insert(ilTemplate $a_tpl): void
    {
        $html = $this->render();

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $html);
        $a_tpl->parseCurrentBlock();
    }
}
