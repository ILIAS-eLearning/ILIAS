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
 * This class represents a text property in a property form.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilCSSRectInputGUI extends ilSubEnabledFormPropertyGUI
{
    protected string $top = "";
    protected string $left = "";
    protected string $right = "";
    protected string $bottom = "";
    protected int $size = 0;
    protected bool $useUnits = false;
    
    /**
    * Constructor
    *
    * @param	string	$a_title	Title
    * @param	string	$a_postvar	Post Variable
    */
    public function __construct($a_title = "", $a_postvar = "")
    {
        global $DIC;

        $this->lng = $DIC->language();
        parent::__construct($a_title, $a_postvar);
        $this->size = 6;
        $this->useUnits = true;
    }

    public function setValue(array $valueArray) : void
    {
        $this->top = $valueArray['top'];
        $this->left = $valueArray['left'];
        $this->right = $valueArray['right'];
        $this->bottom = $valueArray['bottom'];
    }

    public function setUseUnits(bool $a_value) : void
    {
        $this->useUnits = $a_value;
    }

    public function useUnits() : bool
    {
        return $this->useUnits;
    }

    public function setTop(string $a_value) : void
    {
        $this->top = $a_value;
    }

    public function getTop() : string
    {
        return $this->top;
    }

    public function setBottom(string $a_value) : void
    {
        $this->bottom = $a_value;
    }

    public function getBottom() : string
    {
        return $this->bottom;
    }

    public function setLeft(string $a_value) : void
    {
        $this->left = $a_value;
    }

    public function getLeft() : string
    {
        return $this->left;
    }

    public function setRight(string $a_value) : void
    {
        $this->right = $a_value;
    }

    public function getRight() : string
    {
        return $this->right;
    }

    public function setSize(int $a_size) : void
    {
        $this->size = $a_size;
    }

    public function setValueByArray(array $a_values) : void
    {
        $postVar = $this->getPostVar();
        
        $positions = ['top', 'left', 'right', 'bottom'];
        $values = [
            'top' => '',
            'bottom' => '',
            'right' => '',
            'left' => '',
        ];
        
        foreach ($positions as $position) {
            if (isset($a_values[$postVar . '_' . $position])) {
                $values[$position] = $a_values[$postVar . '_' . $position];
            } elseif (isset($a_values[$postVar][$position])) {
                $values[$position] = $a_values[$postVar][$position];
            }
        }

        $this->setValue($values);
    }

    public function getSize() : int
    {
        return $this->size;
    }
    
    public function checkInput() : bool
    {
        $lng = $this->lng;

        $val = $this->getInput();
        if (
            $this->getRequired() &&
            (
                ($val[$this->getPostVar() . '_top'] == "")
                || ($val[$this->getPostVar() . '_bottom'] == "")
                || ($val[$this->getPostVar() . '_left'] == "")
                || ($val[$this->getPostVar() . '_right'] == "")
            )
        ) {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }

        if ($this->useUnits()) {
            if ((!preg_match('/^(([1-9]+|([1-9]+[0]*[\.,]{0,1}[\d]+))|(0[\.,](0*[1-9]+[\d]*))|0)(cm|mm|in|pt|pc|px|em)$/is', $val[$this->getPostVar() . '_top'])) ||
                (!preg_match('/^(([1-9]+|([1-9]+[0]*[\.,]{0,1}[\d]+))|(0[\.,](0*[1-9]+[\d]*))|0)(cm|mm|in|pt|pc|px|em)$/is', $val[$this->getPostVar() . '_right'])) ||
                (!preg_match('/^(([1-9]+|([1-9]+[0]*[\.,]{0,1}[\d]+))|(0[\.,](0*[1-9]+[\d]*))|0)(cm|mm|in|pt|pc|px|em)$/is', $val[$this->getPostVar() . '_bottom'])) ||
                (!preg_match('/^(([1-9]+|([1-9]+[0]*[\.,]{0,1}[\d]+))|(0[\.,](0*[1-9]+[\d]*))|0)(cm|mm|in|pt|pc|px|em)$/is', $val[$this->getPostVar() . '_left']))) {
                $this->setAlert($lng->txt("msg_unit_is_required"));
                return false;
            }
        }
        return $this->checkSubItemsInput();
    }

    public function getInput() : array
    {
        $val = $this->strArray($this->getPostVar());
        $ret[$this->getPostVar() . '_top'] = trim($val['top']);
        $ret[$this->getPostVar() . '_right'] = trim($val['right']);
        $ret[$this->getPostVar() . '_bottom'] = trim($val['bottom']);
        $ret[$this->getPostVar() . '_left'] = trim($val['left']);
        return $ret;
    }

    public function insert(ilTemplate $a_tpl) : void
    {
        $lng = $this->lng;
        
        if (strlen($this->getTop())) {
            $a_tpl->setCurrentBlock("cssrect_value_top");
            $a_tpl->setVariable("CSSRECT_VALUE", ilLegacyFormElementsUtil::prepareFormOutput($this->getTop()));
            $a_tpl->parseCurrentBlock();
        }
        if (strlen($this->getBottom())) {
            $a_tpl->setCurrentBlock("cssrect_value_bottom");
            $a_tpl->setVariable("CSSRECT_VALUE", ilLegacyFormElementsUtil::prepareFormOutput($this->getBottom()));
            $a_tpl->parseCurrentBlock();
        }
        if (strlen($this->getLeft())) {
            $a_tpl->setCurrentBlock("cssrect_value_left");
            $a_tpl->setVariable("CSSRECT_VALUE", ilLegacyFormElementsUtil::prepareFormOutput($this->getLeft()));
            $a_tpl->parseCurrentBlock();
        }
        if (strlen($this->getRight())) {
            $a_tpl->setCurrentBlock("cssrect_value_right");
            $a_tpl->setVariable("CSSRECT_VALUE", ilLegacyFormElementsUtil::prepareFormOutput($this->getRight()));
            $a_tpl->parseCurrentBlock();
        }
        $a_tpl->setCurrentBlock("cssrect");
        $a_tpl->setVariable("ID", $this->getFieldId());
        $a_tpl->setVariable("SIZE", $this->getSize());
        $a_tpl->setVariable("POST_VAR", $this->getPostVar());
        $a_tpl->setVariable("TEXT_TOP", $lng->txt("pos_top"));
        $a_tpl->setVariable("TEXT_RIGHT", $lng->txt("pos_right"));
        $a_tpl->setVariable("TEXT_BOTTOM", $lng->txt("pos_bottom"));
        $a_tpl->setVariable("TEXT_LEFT", $lng->txt("pos_left"));
        if ($this->getDisabled()) {
            $a_tpl->setVariable(
                "DISABLED",
                " disabled=\"disabled\""
            );
        }
        $a_tpl->parseCurrentBlock();
    }
}
