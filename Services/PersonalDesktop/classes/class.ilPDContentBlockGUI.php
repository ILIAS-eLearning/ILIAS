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

include_once("Services/Block/classes/class.ilBlockGUI.php");

/**
* BlockGUI class for (centered) Content on Personal Desktop
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilPDContentBlockGUI extends ilBlockGUI
{
    public static $block_type = "pdcontent";
    
    /**
    * Constructor
    */
    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();

        parent::__construct();
        
        $this->setEnableNumInfo(false);
        $this->setLimit(99999);
        $this->setBigMode(true);
        $this->allow_moving = false;
    }

    /**
     * @inheritdoc
     */
    public function getBlockType() : string
    {
        return self::$block_type;
    }

    /**
    * Set Current Item Number.
    *
    * @param	int	$a_currentitemnumber	Current Item Number
    */
    public function setCurrentItemNumber($a_currentitemnumber)
    {
        $this->currentitemnumber = $a_currentitemnumber;
    }

    /**
    * Get Current Item Number.
    *
    * @return	int	Current Item Number
    */
    public function getCurrentItemNumber()
    {
        return $this->currentitemnumber;
    }

    /**
     * @inheritdoc
     */
    protected function isRepositoryObject() : bool
    {
        return false;
    }

    public function getHTML()
    {
        return parent::getHTML();
    }
    
    public function getContent()
    {
        return $this->content;
    }
    
    public function setContent($a_content)
    {
        $this->content = $a_content;
    }
    
    /**
    * Fill data section
    */
    public function fillDataSection()
    {
        $this->tpl->setVariable("BLOCK_ROW", $this->getContent());
    }

    /**
    * block footer
    */
    public function fillFooter()
    {
        //$this->fillFooterLinks();
        $lng = $this->lng;

        if (is_array($this->data)) {
            $this->max_count = count($this->data);
        }
                
        // table footer numinfo
        if ($this->getEnableNumInfo()) {
            $numinfo = "(" . $this->getCurrentItemNumber() . " " .
                strtolower($lng->txt("of")) . " " . $this->max_count . ")";
    
            if ($this->max_count > 0) {
                $this->tpl->setVariable("NUMINFO", $numinfo);
            }
            $this->fillFooterLinks(true, $numinfo);
        }
    }
    
    public function fillPreviousNext()
    {
    }
}
