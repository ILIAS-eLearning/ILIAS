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

include_once("./Services/Block/classes/class.ilBlockGUI.php");

/**
* BlockGUI class for dummy block. Only used for moving, if block is not visible
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_IsCalledBy ilDummyBlockGUI: ilColumnGUI
* @ingroup ServicesBlock
*/
class ilDummyBlockGUI extends ilBlockGUI
{
    /**
     * @var ilSetting
     */
    protected $settings;

    public static $block_type = "";
    
    /**
    * Constructor
    */
    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->access = $DIC->access();
        $this->settings = $DIC->settings();

        parent::__construct();
        
        $this->setLimit(5);
        $this->allow_moving = true;
    }

    /**
     * @inheritdoc
     */
    public function getBlockType() : string
    {
        return self::$block_type;
    }

    /**
    * Set block type
    *
    * @return	string	Block type.
    */
    public static function setBlockType($a_type)
    {
        self::$block_type = $a_type;
    }

    /**
     * @inheritdoc
     */
    protected function isRepositoryObject() : bool
    {
        return false;
    }

    
    /**
    * Get Screen Mode for current command.
    */
    public static function getScreenMode()
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        
        return IL_SCREEN_SIDE;
    }

    /**
    * Do most of the initialisation.
    */
    public function setBlock($a_block)
    {
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;

        $next_class = $ilCtrl->getNextClass();
        $cmd = $ilCtrl->getCmd("getHTML");

        switch ($next_class) {
            default:
                return $this->$cmd();
        }
    }

    /**
    * Fill data section
    */
    public function fillDataSection()
    {
        $lng = $this->lng;

        $this->setDataSection($lng->txt("invisible_block_mess"));
    }

    /**
    * Get block HTML code.
    */
    public function getHTML()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilUser = $this->user;
        $ilAccess = $this->access;
        $ilSetting = $this->settings;
        
        return parent::getHTML();
    }
}
