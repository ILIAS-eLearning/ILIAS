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
 * This class represents a checkbox property in a property form.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilCheckboxInputGUI extends ilSubEnabledFormPropertyGUI implements ilToolbarItem, ilTableFilterItem
{
    protected string $value = "1";
    protected bool $checked = false;
    protected string $optiontitle = "";
    protected string $additional_attributes = '';
    
    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
        $this->setType("checkbox");
    }

    public function setValue(string $a_value) : void
    {
        $this->value = $a_value;
    }

    public function getValue() : string
    {
        return $this->value;
    }

    public function setChecked(bool $a_checked) : void
    {
        $this->checked = $a_checked;
    }

    public function getChecked() : bool
    {
        return $this->checked;
    }

    public function setOptionTitle(string $a_optiontitle) : void
    {
        $this->optiontitle = $a_optiontitle;
    }

    public function getOptionTitle() : string
    {
        return $this->optiontitle;
    }

    public function setValueByArray(array $a_values) : void
    {
        $checked = $a_values[$this->getPostVar()] ?? false;
        $this->setChecked((bool) $checked);
        foreach ($this->getSubItems() as $item) {
            $item->setValueByArray($a_values);
        }
    }

    public function setAdditionalAttributes(string $a_attrs) : void
    {
        $this->additional_attributes = $a_attrs;
    }

    public function getAdditionalAttributes() : string
    {
        return $this->additional_attributes;
    }

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    * @return    bool        Input ok, true/false
    */
    public function checkInput() : bool
    {
        $ok = $this->checkSubItemsInput();

        // only not ok, if checkbox not checked
        if ($this->getInput() == "") {
            $ok = true;
        }

        return $ok;
    }

    public function getInput() : string
    {
        return $this->str($this->getPostVar());
    }

    public function hideSubForm() : bool
    {
        return !$this->getChecked();
    }

    public function render($a_mode = '') : string
    {
        $tpl = new ilTemplate("tpl.prop_checkbox.html", true, true, "Services/Form");
        
        $tpl->setVariable("POST_VAR", $this->getPostVar());
        $tpl->setVariable("ID", $this->getFieldId());
        $tpl->setVariable("PROPERTY_VALUE", $this->getValue());
        $tpl->setVariable("OPTION_TITLE", $this->getOptionTitle());
        if (strlen($this->getAdditionalAttributes())) {
            $tpl->setVariable('PROP_CHECK_ATTRS', $this->getAdditionalAttributes());
        }
        if ($this->getChecked()) {
            $tpl->setVariable(
                "PROPERTY_CHECKED",
                'checked="checked"'
            );
        }
        if ($this->getDisabled()) {
            $tpl->setVariable(
                "DISABLED",
                'disabled="disabled"'
            );
        }
        
        if ($a_mode == "toolbar") {
            // block-inline hack, see: http://blog.mozilla.com/webdev/2009/02/20/cross-browser-inline-block/
            // -moz-inline-stack for FF2
            // zoom 1; *display:inline for IE6 & 7
            $tpl->setVariable("STYLE_PAR", 'display: -moz-inline-stack; display:inline-block; zoom: 1; *display:inline;');
        }

        $tpl->setVariable("ARIA_LABEL", ilLegacyFormElementsUtil::prepareFormOutput($this->getTitle()));

        return $tpl->get();
    }

    public function insert(ilTemplate $a_tpl) : void
    {
        $html = $this->render();

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $html);
        $a_tpl->parseCurrentBlock();
    }

    public function getTableFilterHTML() : string
    {
        $html = $this->render();
        return $html;
    }

    public function serializeData() : string
    {
        return serialize($this->getChecked());
    }
    
    public function unserializeData(string $a_data) : void
    {
        $data = unserialize($a_data);

        if ($data) {
            $this->setValue((string) $data);
            $this->setChecked(true);
        }
    }
    
    public function getToolbarHTML() : string
    {
        $html = $this->render('toolbar');
        return $html;
    }
}
