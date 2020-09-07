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

require_once('./Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php');
/**
 * This class represents a custom property in a property form.
 *
 * @author     Alex Killing <alex.killing@gmx.de>
 * @version    $Id$
 * @ingroup    ServicesForm
 *
 * @deprecated Deprecated since 4.4, inherit directly from InputGUI instead
 */
class ilCustomInputGUI extends ilSubEnabledFormPropertyGUI
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    protected $html;
    
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
        $this->setType("custom");
    }
    
    /**
    * Set Html.
    *
    * @param	string	$a_html	Html
    */
    public function setHtml($a_html)
    {
        $this->html = $a_html;
    }

    /**
    * Get Html.
    *
    * @return	string	Html
    */
    public function getHtml()
    {
        return $this->html;
    }

    /**
    * Set value by array
    *
    * @param	object	$a_item		Item
    */
    public function setValueByArray($a_values)
    {
        foreach ($this->getSubItems() as $item) {
            $item->setValueByArray($a_values);
        }
    }

    /**
    * Insert property html
    *
    */
    public function insert($a_tpl)
    {
        $a_tpl->setCurrentBlock("prop_custom");
        $a_tpl->setVariable("CUSTOM_CONTENT", $this->getHtml());
        $a_tpl->parseCurrentBlock();
    }
    
    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        $lng = $this->lng;
        
        if ($this->getPostVar()) {
            $_POST[$this->getPostVar()] = ilUtil::stripSlashes($_POST[$this->getPostVar()]);
            if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == "") {
                $this->setAlert($lng->txt("msg_input_is_required"));
                return false;
            }
        }
        return $this->checkSubItemsInput();
    }
}
