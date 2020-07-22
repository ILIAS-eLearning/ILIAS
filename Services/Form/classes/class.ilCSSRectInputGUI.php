<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2007 ILIAS open source, University of Cologne            |
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
* This class represents a text property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilCSSRectInputGUI extends ilSubEnabledFormPropertyGUI
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    protected $top;
    protected $left;
    protected $right;
    protected $bottom;
    protected $size;
    protected $useUnits;
    
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

    public function setValue($valueArray)
    {
        $this->top = $valueArray['top'];
        $this->left = $valueArray['left'];
        $this->right = $valueArray['right'];
        $this->bottom = $valueArray['bottom'];
    }

    /**
    * Set use units.
    *
    * @param	boolean	$a_value	Use units
    */
    public function setUseUnits($a_value)
    {
        $this->useUnits = $a_value;
    }

    /**
    * Get use units
    *
    * @return	boolean use units
    */
    public function useUnits()
    {
        return $this->useUnits;
    }

    /**
    * Set Top.
    *
    * @param	string	$a_value	Top
    */
    public function setTop($a_value)
    {
        $this->top = $a_value;
    }

    /**
    * Get Top.
    *
    * @return	string	Top
    */
    public function getTop()
    {
        return $this->top;
    }

    /**
    * Set Bottom.
    *
    * @param	string	$a_value	Bottom
    */
    public function setBottom($a_value)
    {
        $this->bottom = $a_value;
    }

    /**
    * Get Bottom.
    *
    * @return	string	Bottom
    */
    public function getBottom()
    {
        return $this->bottom;
    }

    /**
    * Set Left.
    *
    * @param	string	$a_value	Left
    */
    public function setLeft($a_value)
    {
        $this->left = $a_value;
    }

    /**
    * Get Left.
    *
    * @return	string	Left
    */
    public function getLeft()
    {
        return $this->left;
    }

    /**
    * Set Right.
    *
    * @param	string	$a_value	Right
    */
    public function setRight($a_value)
    {
        $this->right = $a_value;
    }

    /**
    * Get Right.
    *
    * @return	string	Right
    */
    public function getRight()
    {
        return $this->right;
    }

    /**
    * Set Size.
    *
    * @param	int	$a_size	Size
    */
    public function setSize($a_size)
    {
        $this->size = $a_size;
    }

    /**
    * Set value by array
    *
    * @param	array	$a_values	value array
    */
    public function setValueByArray($a_values)
    {
        $postVar = $this->getPostVar();

        $values = array(
            'top' => $a_values[$postVar . '_top'],
            'bottom' => $a_values[$postVar . '_bottom'],
            'right' => $a_values[$postVar . '_right'],
            'left' => $a_values[$postVar . '_left'],
        );

        $this->setValue($values);
    }

    /**
    * Get Size.
    *
    * @return	int	Size
    */
    public function getSize()
    {
        return $this->size;
    }
    
    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        $lng = $this->lng;
        
        $_POST[$this->getPostVar() . '_top'] = ilUtil::stripSlashes($_POST[$this->getPostVar()]['top']);
        $_POST[$this->getPostVar() . '_right'] = ilUtil::stripSlashes($_POST[$this->getPostVar()]['right']);
        $_POST[$this->getPostVar() . '_bottom'] = ilUtil::stripSlashes($_POST[$this->getPostVar()]['bottom']);
        $_POST[$this->getPostVar() . '_left'] = ilUtil::stripSlashes($_POST[$this->getPostVar()]['left']);

        if (
            $this->getRequired() &&
            (
                (trim($_POST[$this->getPostVar() . '_top'] == ""))
                || (trim($_POST[$this->getPostVar() . '_bottom']) == "")
                || (trim($_POST[$this->getPostVar() . '_left']) == "")
                || (trim($_POST[$this->getPostVar() . '_right']) == "")
            )
        ) {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }

        if ($this->useUnits()) {
            if ((!preg_match('/^(([1-9]+|([1-9]+[0]*[\.,]{0,1}[\d]+))|(0[\.,](0*[1-9]+[\d]*))|0)(cm|mm|in|pt|pc|px|em)$/is', $_POST[$this->getPostVar() . '_top'])) ||
                (!preg_match('/^(([1-9]+|([1-9]+[0]*[\.,]{0,1}[\d]+))|(0[\.,](0*[1-9]+[\d]*))|0)(cm|mm|in|pt|pc|px|em)$/is', $_POST[$this->getPostVar() . '_right'])) ||
                (!preg_match('/^(([1-9]+|([1-9]+[0]*[\.,]{0,1}[\d]+))|(0[\.,](0*[1-9]+[\d]*))|0)(cm|mm|in|pt|pc|px|em)$/is', $_POST[$this->getPostVar() . '_bottom'])) ||
                (!preg_match('/^(([1-9]+|([1-9]+[0]*[\.,]{0,1}[\d]+))|(0[\.,](0*[1-9]+[\d]*))|0)(cm|mm|in|pt|pc|px|em)$/is', $_POST[$this->getPostVar() . '_left']))) {
                $this->setAlert($lng->txt("msg_unit_is_required"));
                return false;
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
        $lng = $this->lng;
        
        if (strlen($this->getTop())) {
            $a_tpl->setCurrentBlock("cssrect_value_top");
            $a_tpl->setVariable("CSSRECT_VALUE", ilUtil::prepareFormOutput($this->getTop()));
            $a_tpl->parseCurrentBlock();
        }
        if (strlen($this->getBottom())) {
            $a_tpl->setCurrentBlock("cssrect_value_bottom");
            $a_tpl->setVariable("CSSRECT_VALUE", ilUtil::prepareFormOutput($this->getBottom()));
            $a_tpl->parseCurrentBlock();
        }
        if (strlen($this->getLeft())) {
            $a_tpl->setCurrentBlock("cssrect_value_left");
            $a_tpl->setVariable("CSSRECT_VALUE", ilUtil::prepareFormOutput($this->getLeft()));
            $a_tpl->parseCurrentBlock();
        }
        if (strlen($this->getRight())) {
            $a_tpl->setCurrentBlock("cssrect_value_right");
            $a_tpl->setVariable("CSSRECT_VALUE", ilUtil::prepareFormOutput($this->getRight()));
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
