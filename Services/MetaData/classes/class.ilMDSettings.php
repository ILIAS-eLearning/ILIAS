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
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ilCtrl_Calls
* @ingroup ServicesMetaData
*/
class ilMDSettings
{
    public static $instance = null;

    private $settings;
    private $copyright_selection_active = false;

    /**
     * Constructor
     *
     * @access private
     *
     */
    private function __construct()
    {
        $this->read();
    }
    
    /**
     * get instance
     *
     * @access public
     * @static
     *
     * @param
     */
    public static function _getInstance()
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new ilMDSettings();
    }
    
    /**
     * is copyright selection active
     *
     * @access public
     *
     */
    public function isCopyrightSelectionActive()
    {
        return $this->copyright_selection_active ? true : false;
    }
    
    /**
     * enable copyright selection
     *
     * @access public
     * @param bool status
     *
     */
    public function activateCopyrightSelection($a_status)
    {
        $this->copyright_selection_active = $a_status;
    }
    
    /**
    * Set delimiter (used in quick editing screen)
    *
    * @param	string delimiter
    */
    public function setDelimiter($a_val)
    {
        $this->delimiter = $a_val;
    }
    
    /**
    * Get delimiter
    *
    * @return	string delimiter
    */
    public function getDelimiter()
    {
        if (trim($this->delimiter) == "") {
            return ",";
        }
        return $this->delimiter;
    }
    
    /**
     * save
     *
     * @access public
     *
     */
    public function save()
    {
        $this->settings->set('copyright_selection_active', (int) $this->isCopyrightSelectionActive());
        $this->settings->set('delimiter', $this->getDelimiter());
    }
    
    /**
     * read
     *
     * @access private
     *
     */
    private function read()
    {
        $this->settings = new ilSetting('md_settings');
        
        $this->copyright_selection_active = $this->settings->get('copyright_selection_active', 0);
        $this->delimiter = $this->settings->get('delimiter', ",");
    }
}
