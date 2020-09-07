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
* This class represents a number property in a property form.
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilCombinationInputGUI extends ilSubEnabledFormPropertyGUI implements ilTableFilterItem
{
    /**
     * @var ilLanguage
     */
    protected $lng;


    /**
     * Constructor
     */
    public function __construct($a_title = "", $a_postvar = "")
    {
        parent::__construct($a_title, $a_postvar);
        global $DIC;

        $this->lng = $DIC->language();
    }

    protected $items = array();
    protected $labels;
    protected $comparison;

    const COMPARISON_ASCENDING = 1;
    const COMPARISON_DESCENDING = 2;

    /**
     * Add property item
     *
     * @param	string	$id
     * @param	object	$item
     * @param	string	$label
     */
    public function addCombinationItem($id, $item, $label = "")
    {
        $this->items[$id] = $item;
        if ($label) {
            $this->labels[$id] = $label;
        }
    }

    /**
     * Get property item
     *
     * @param	string	$id
     * @return	object
     */
    public function getCombinationItem($id)
    {
        if (isset($this->items[$id])) {
            return $this->items[$id];
        }
    }

    /**
     * Remove property item
     *
     * @param	string	$id
     */
    public function removeCombinationItem($id)
    {
        if (isset($this->items[$id])) {
            unset($this->items[$id]);
        }
    }

    /**
     * Call item methods
     *
     * @param	string	$method
     * @param	array	$param
     */
    public function __call($method, $param)
    {
        $result = array();
        foreach ($this->items as $id => $obj) {
            if (method_exists($obj, $method)) {
                $result[$id] = call_user_func_array(array($obj, $method), $param);
            }
        }
        return $result;
    }

    /**
    * serialize data
    */
    public function serializeData()
    {
        $result = array();
        foreach ($this->items as $id => $obj) {
            $result[$id] = $obj->serializeData();
        }
        return serialize($result);
    }

    /**
    * unserialize data
    */
    public function unserializeData($a_data)
    {
        $data = unserialize($a_data);

        if ($data) {
            foreach ($this->items as $id => $obj) {
                $obj->unserializeData($data[$id]);
            }
        } else {
            foreach ($this->items as $id => $obj) {
                if (method_exists($obj, "setValue")) {
                    $this->setValue(false);
                }
            }
        }
    }

    /**
     * Set mode for comparison (extended validation)
     *
     * @param	int	$mode
     * @return	bool
     */
    public function setComparisonMode($mode)
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
    }

    /**
    * Set Value.
    *
    * @param	string	$a_value	Value
    */
    public function setValue($a_value)
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

    /**
    * Get Value.
    *
    * @return	string	Value
    */
    public function getValue()
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

    /**
    * Set value by array
    *
    * @param	array	$a_values	value array
    */
    public function setValueByArray($a_values)
    {
        foreach ($this->items as $id => $obj) {
            $obj->setValueByArray($a_values);
        }
    }

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        if (sizeof($this->items)) {
            foreach ($this->items as $id => $obj) {
                if (!$obj->checkInput()) {
                    return false;
                }
            }

            if ($this->comparison_mode) {
                $prev = null;
                foreach ($this->items as $id => $obj) {
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

    /**
    * Insert property html
    *
    * @return	int	Size
    */
    public function insert($a_tpl)
    {
        $html = $this->render();

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $html);
        $a_tpl->parseCurrentBlock();
    }

    /**
    * Render item
    */
    public function render()
    {
        $lng = $this->lng;

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

    /**
    * Get HTML for table filter
    */
    public function getTableFilterHTML()
    {
        $html = $this->render();
        return $html;
    }
}
