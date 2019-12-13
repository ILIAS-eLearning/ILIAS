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
* This class represents a hidden form property in a property form.
*
* @author Roland Küstermann (rkuestermann@mps.de)
* @version $Id$
* @ingroup	ServicesForm
*/
class ilHiddenInputGUI extends ilFormPropertyGUI implements ilToolbarItem
{
    protected $value;
    
    /**
    * Constructor
    *
    * @param	string	$a_title	Title
    * @param	string	$a_postvar	Post Variable
    */
    public function __construct($a_postvar)
    {
        parent::__construct("", $a_postvar);
        $this->setType("hidden");
    }

    /**
    * Set Value.
    *
    * @param	string	$a_value	Value
    */
    public function setValue($a_value)
    {
        $this->value = $a_value;
    }

    /**
    * Get Value.
    *
    * @return	string	Value
    */
    public function getValue()
    {
        return $this->value;
    }
    
    
    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        return true;		// please overwrite
    }
    
    /**
    * Set value by array
    *
    * @param	array	$a_values	value array
    */
    public function setValueByArray($a_values)
    {
        $this->setValue($a_values[$this->getPostVar()]);
    }
    
    /**
    * Insert property html
    *
    * @return	int	Size
    */
    public function insert($a_tpl)
    {
        $a_tpl->setCurrentBlock("hidden");
        $a_tpl->setVariable('PROP_INPUT_TYPE', 'hidden');
        $a_tpl->setVariable("POST_VAR", $this->getPostVar());
        $a_tpl->setVariable("ID", $this->getFieldId());
        $a_tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($this->getValue()));
        $a_tpl->parseCurrentBlock();
    }

    /**
     * Get HTML for toolbar
     */
    public function getToolbarHTML()
    {
        return "<input type=\"hidden\"" .
            " name=\"" . $this->getPostVar() . "\"" .
            " value=\"" . ilUtil::prepareFormOutput($this->getValue()) . "\"" .
            " id=\"" . $this->getFieldId() . "\" />";
    }
}
