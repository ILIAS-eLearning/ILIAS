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
 * This class represents a number property in a property form.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilCombinationInputGUI extends ilSubEnabledFormPropertyGUI implements ilTableFilterItem
{
    public const COMPARISON_ASCENDING = 1;
    public const COMPARISON_DESCENDING = 2;

    protected array $items = array();
    protected array $labels = [];
    protected int $comparison_mode = 1;

    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        parent::__construct($a_title, $a_postvar);
        global $DIC;

        $this->lng = $DIC->language();
    }

    public function addCombinationItem(
        string $id,
        ilFormPropertyGUI $item,
        $label = ""
    ) : void {
        $this->items[$id] = $item;
        if ($label) {
            $this->labels[$id] = $label;
        }
    }

    public function getCombinationItem(string $id) : ?ilFormPropertyGUI
    {
        if (isset($this->items[$id])) {
            return $this->items[$id];
        }
        return null;
    }

    public function removeCombinationItem(string $id) : void
    {
        if (isset($this->items[$id])) {
            unset($this->items[$id]);
        }
    }

    public function __call(
        string $method,
        array $param
    ) : array {
        $result = array();
        foreach ($this->items as $id => $obj) {
            if (method_exists($obj, $method)) {
                $result[$id] = call_user_func_array(array($obj, $method), $param);
            }
        }
        return $result;
    }

    public function serializeData() : string
    {
        $result = array();
        foreach ($this->items as $id => $obj) {
            $result[$id] = $obj->serializeData();
        }
        return serialize($result);
    }

    public function unserializeData(string $a_data) : void
    {
        $data = unserialize($a_data);

        if ($data) {
            foreach ($this->items as $id => $obj) {
                $obj->unserializeData($data[$id]);
            }
        } else {
            foreach ($this->items as $id => $obj) {
                if (method_exists($obj, "setValue")) {
                    $this->setValue(null);
                }
            }
        }
    }

    /**
     * Set mode for comparison (extended validation)
     */
    public function setComparisonMode(int $mode) : bool
    {
        if (in_array($mode, array(self::COMPARISON_ASCENDING, self::COMPARISON_DESCENDING))) {
            foreach ($this->items as $obj) {
                if (!method_exists($obj, "getPostValueForComparison")) {
                    return false;
                }
            }
            $this->comparison_mode = $mode;
            return true;
        }
        return false;
    }

    public function setValue(?array $a_value) : void
    {
        if (is_array($a_value)) {
            foreach ($a_value as $id => $value) {
                if (isset($this->items[$id])) {
                    if (method_exists($this->items[$id], "setValue")) {
                        $this->items[$id]->setValue($value);
                    }
                    // datetime
                    elseif (method_exists($this->items[$id], "setDate")) {
                        $this->items[$id]->setDate($value);
                    }
                }
            }
        } elseif ($a_value === null) {
            foreach ($this->items as $item) {
                if (method_exists($item, "setValue")) {
                    $item->setValue(null);
                }
                // datetime
                elseif (method_exists($item, "setDate")) {
                    $item->setDate();
                }
                // duration
                elseif (method_exists($item, "setMonths")) {
                    $item->setMonths(0);
                    $item->setDays(0);
                    $item->setHours(0);
                    $item->setMinutes(0);
                    $item->setSeconds(0);
                }
            }
        }
    }

    public function getValue() : ?array
    {
        $result = array();
        foreach ($this->items as $id => $obj) {
            if (method_exists($obj, "getValue")) {
                $result[$id] = $obj->getValue();
            }
            // datetime
            elseif (method_exists($obj, "setDate")) {
                $result[$id] = $obj->getDate();
            }
        }
        return $result;
    }

    public function setValueByArray(array $a_values) : void
    {
        foreach ($this->items as $obj) {
            $obj->setValueByArray($a_values);
        }
    }

    /**
     * Check input, strip slashes etc. set alert, if input is not ok.
     */
    public function checkInput() : bool
    {
        if (sizeof($this->items)) {
            foreach ($this->items as $obj) {
                if (!$obj->checkInput()) {
                    return false;
                }
            }

            if ($this->comparison_mode) {
                $prev = null;
                foreach ($this->items as $obj) {
                    $value = $obj->getPostValueForComparison();
                    if ($value != "") {
                        if ($prev !== null) {
                            if ($this->comparison_mode == self::COMPARISON_ASCENDING) {
                                if ($value < $prev) {
                                    return false;
                                }
                            } else {
                                if ($value > $prev) {
                                    return false;
                                }
                            }
                        }
                        $prev = $value;
                    }
                }
            }
        }

        return $this->checkSubItemsInput();
    }

    public function insert(ilTemplate $a_tpl) : void
    {
        $html = $this->render();

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $html);
        $a_tpl->parseCurrentBlock();
    }

    public function render() : string
    {
        $tpl = new ilTemplate("tpl.prop_combination.html", true, true, "Services/Form");

        if (sizeof($this->items)) {
            foreach ($this->items as $id => $obj) {
                // label
                if (isset($this->labels[$id])) {
                    $tpl->setCurrentBlock("prop_combination_label");
                    $tpl->setVariable("LABEL", $this->labels[$id]);
                    $tpl->parseCurrentBlock();
                }

                $tpl->setCurrentBlock("prop_combination");
                $tpl->setVariable("FIELD", $obj->render());
                $tpl->parseCurrentBlock();
            }
        }

        return $tpl->get();
    }

    public function getTableFilterHTML() : string
    {
        $html = $this->render();
        return $html;
    }
}
