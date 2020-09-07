<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* Abstract base class for all ical items (Component, Parameter and Value)
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesCalendar
*/

abstract class ilICalItem
{
    protected $name = '';
    protected $value = '';
    protected $items = array();
    
    /**
     * Constructor
     *
     * @access public
     * @param string name
     *
     */
    public function __construct($a_name, $a_value = '')
    {
        $this->name = $a_name;
        $this->value = $a_value;
    }
    
    /**
     * set value
     *
     * @access public
     * @param string value
     *
     */
    public function setValue($a_value)
    {
        $this->value = $a_value;
    }
    
    /**
     * get value
     *
     * @access public
     *
     */
    public function getValue()
    {
        return trim($this->value);
    }
    
    /**
     * get items
     *
     * @access public
     *
     */
    public function getItems()
    {
        return $this->items ? $this->items : array();
    }
    
    /**
     * get name
     *
     * @access public
     *
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Get items by name
     *
     * @access public
     * @param string name
     *
     */
    public function getItemsByName($a_name, $a_recursive = true)
    {
        return array();
    }
    
    /**
     * Add item
     *
     * @access public
     *
     * @param ilICalItem
     */
    public function addItem($a_item)
    {
        $this->items[] = $a_item;
    }
}
