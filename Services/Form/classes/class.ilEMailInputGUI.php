<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
 * This class represents a email property in a property form.
 * @author     Alex Killing <alex.killing@gmx.de>
 * @version    $Id$
 * @ingroup    ServicesForm
 */
class ilEMailInputGUI extends ilFormPropertyGUI
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    protected $value;
    protected $size = 30;
    protected $max_length = 80;
    protected $allowRFC822 = false; // [bool]

    /**
     * @var bool
     */
    protected $retype = false;

    /**
     * @var string
     */
    protected $retypevalue = '';

    /**
     * Constructor
     * @param    string $a_title      Title
     * @param    string $a_postvar    Post Variable
     */
    public function __construct($a_title = "", $a_postvar = "")
    {
        global $DIC;

        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
        $this->setRetype(false);
    }

    /**
     * Set Value.
     * @param    string $a_value    Value
     */
    public function setValue($a_value)
    {
        $this->value = $a_value;
    }

    /**
     * Get Value.
     * @return    string    Value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set value by array
     * @param    array $a_values    value array
     */
    public function setValueByArray($a_values)
    {
        $this->setValue($a_values[$this->getPostVar()]);
        $this->setRetypeValue($a_values[$this->getPostVar() . '_retype']);
    }
    
    /**
     * Allow extended email address format
     *
     * "example@example.com" vs "example <example@example.com>"
     *
     * @param bool $a_value
     */
    public function allowRFC822($a_value)
    {
        $this->allowRFC822 = (bool) $a_value;
    }

    /**
     * Check input, strip slashes etc. set alert, if input is not ok.
     * @return    boolean        Input ok, true/false
     */
    public function checkInput()
    {
        $lng = $this->lng;
        
        $_POST[$this->getPostVar()] = ilUtil::stripSlashes($_POST[$this->getPostVar()], !(bool) $this->allowRFC822);
        $_POST[$this->getPostVar() . '_retype'] = ilUtil::stripSlashes($_POST[$this->getPostVar() . '_retype'], !(bool) $this->allowRFC822);
        if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == "") {
            $this->setAlert($lng->txt("msg_input_is_required"));

            return false;
        }
        if ($this->getRetype() && ($_POST[$this->getPostVar()] != $_POST[$this->getPostVar() . '_retype'])) {
            $this->setAlert($lng->txt('email_not_match'));

            return false;
        }
        if (!ilUtil::is_email($_POST[$this->getPostVar()]) &&
            trim($_POST[$this->getPostVar()]) != ""
        ) {
            $this->setAlert($lng->txt("email_not_valid"));

            return false;
        }


        return true;
    }

    /**
     * @param ilTemplate $a_tpl
     */
    public function insert(ilTemplate $a_tpl)
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
            $ptpl->setVariable('PROPERTY_RETYPE_VALUE', ilUtil::prepareFormOutput($retype_value));
            if ($this->getDisabled()) {
                $ptpl->setVariable('RDISABLED', ' disabled="disabled"');
            }
            $ptpl->setVariable('TXT_RETYPE', $lng->txt('form_retype_email'));
            $ptpl->parseCurrentBlock();
        }

        $ptpl->setVariable('POST_VAR', $this->getPostVar());
        $ptpl->setVariable('ID', $this->getFieldId());
        $ptpl->setVariable('PROPERTY_VALUE', ilUtil::prepareFormOutput($this->getValue()));
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

    /**
     * @param    boolean $a_val
     */
    public function setRetype($a_val)
    {
        $this->retype = $a_val;
    }

    /**
     * @return    boolean
     */
    public function getRetype()
    {
        return $this->retype;
    }

    /**
     * @param string $a_retypevalue
     */
    public function setRetypeValue($a_retypevalue)
    {
        $this->retypevalue = $a_retypevalue;
    }

    /**
     * @return    string
     */
    public function getRetypeValue()
    {
        return $this->retypevalue;
    }

    /**
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param int $max_length
     */
    public function setMaxLength($max_length)
    {
        $this->max_length = $max_length;
    }

    /**
     * @return int
     */
    public function getMaxLength()
    {
        return $this->max_length;
    }
}
