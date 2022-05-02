<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * This class represents a non editable value in a property form.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilNonEditableValueGUI extends ilSubEnabledFormPropertyGUI implements ilTableFilterItem, ilMultiValuesItem
{
    /**
     * @var string|array
     */
    protected $value = null;
    protected string $section_icon = "";
    protected bool $disable_escaping = false;
    
    public function __construct(
        string $a_title = "",
        string $a_id = "",
        bool $a_disable_escaping = false
    ) {
        parent::__construct($a_title, $a_id);
        $this->setTitle($a_title);
        $this->setType("non_editable_value");
        $this->disable_escaping = $a_disable_escaping;
    }
    
    public function checkInput() : bool
    {
        return $this->checkSubItemsInput();
    }

    /**
     * @return array|string
     */
    public function getInput()
    {
        if ($this->isRequestParamArray($this->getPostVar())) {
            return $this->strArray($this->getPostVar());
        }
        return $this->str($this->getPostVar());
    }

    protected function setType(string $a_type) : void
    {
        $this->type = $a_type;
    }

    public function getType() : string
    {
        return $this->type;
    }
    
    public function setTitle(string $a_title) : void
    {
        $this->title = $a_title;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function setInfo(string $a_info) : void
    {
        $this->info = $a_info;
    }

    public function getInfo() : string
    {
        return $this->info;
    }

    /**
     * @param string|array $a_value
     */
    public function setValue($a_value) : void
    {
        if ($this->getMulti() && is_array($a_value)) {
            $this->setMultiValues($a_value);
            $a_value = array_shift($a_value);
        }
        $this->value = $a_value;
    }

    /**
     * @return string|array
     */
    public function getValue()
    {
        return $this->value;
    }

    public function render() : string
    {
        $postvar = "";

        $tpl = new ilTemplate("tpl.non_editable_value.html", true, true, "Services/Form");
        if ($this->getPostVar() != "") {
            $postvar = $this->getPostVar();
            if ($this->getMulti() && substr($postvar, -2) != "[]") {
                $postvar .= "[]";
            }
            
            $tpl->setCurrentBlock("hidden");
            $tpl->setVariable('NON_EDITABLE_ID', $postvar);
            $tpl->setVariable('MULTI_HIDDEN_ID', $this->getFieldId());
            $tpl->setVariable("HVALUE", ilLegacyFormElementsUtil::prepareFormOutput((string) $this->getValue()));
            $tpl->parseCurrentBlock();
        }
        $value = $this->getValue();
        if (!$this->disable_escaping) {
            $value = ilLegacyFormElementsUtil::prepareFormOutput((string) $value);
        }
        $tpl->setVariable("VALUE", $value);
        if ($this->getFieldId() != "") {
            $tpl->setVariable("ID", ' id="' . $this->getFieldId() . '" ');
        }
        $tpl->parseCurrentBlock();
        
        if ($this->getMulti() && $postvar != "" && !$this->getDisabled()) {
            $tpl->setVariable("MULTI_ICONS", $this->getMultiIconsHTML());
        }

        
        return $tpl->get();
    }
    
    public function insert(ilTemplate $a_tpl) : void
    {
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $this->render());
        $a_tpl->parseCurrentBlock();
    }

    public function setValueByArray(array $a_values) : void
    {
        if ($this->getPostVar() && isset($a_values[$this->getPostVar()])) {
            $this->setValue($a_values[$this->getPostVar()]);
        }
        foreach ($this->getSubItems() as $item) {
            $item->setValueByArray($a_values);
        }
    }

    public function getTableFilterHTML() : string
    {
        $html = $this->render();
        return $html;
    }
}
