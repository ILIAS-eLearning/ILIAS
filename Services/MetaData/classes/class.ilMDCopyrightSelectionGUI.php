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
* @ingroup ServicesMetaData
*/

include_once('Services/MetaData/classes/class.ilMDSettings.php');
include_once('Services/MetaData/classes/class.ilMDRights.php');

class ilMDCopyrightSelectionGUI
{
    const MODE_QUICKEDIT = 1;
    const MODE_EDIT = 2;

    protected $tpl;
    protected $lng;
    protected $settings;

    private $mode;
    private $rbac_id;
    private $obj_id;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct($a_mode, $a_rbac_id, $a_obj_id)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];
        
        $this->tpl = $tpl;
        $this->lng = $lng;
        
        $this->mode = $a_mode;
        $this->rbac_id = $a_rbac_id;
        $this->obj_id = $a_obj_id;
        
        $this->settings = ilMDSettings::_getInstance();
    }
    
    /**
     * parse
     *
     * @access public
     *
     */
    public function fillTemplate()
    {
        include_once('Services/MetaData/classes/class.ilMDCopyrightSelectionEntry.php');
        
        $desc = ilMDRights::_lookupDescription($this->rbac_id, $this->obj_id);
        
        if (!$this->settings->isCopyrightSelectionActive() or
            !count($entries = ilMDCopyrightSelectionEntry::_getEntries())) {
            $this->tpl->setVariable("TXT_COPYRIGHT", $this->lng->txt('meta_copyright'));
            $this->tpl->setVariable(
                'COPYRIGHT_VAL',
                ilUtil::prepareFormOutput($desc)
            );
            return true;
        }
        
        $default_id = ilMDCopyrightSelectionEntry::_extractEntryId($desc);
        
        include_once('Services/MetaData/classes/class.ilMDCopyrightSelectionEntry.php');
        $found = false;
        foreach ($entries as $entry) {
            $this->tpl->setCurrentBlock('copyright_selection');
            
            if ($entry->getEntryId() == $default_id) {
                $found = true;
                $this->tpl->setVariable('COPYRIGHT_CHECKED', 'checked="checked"');
            }
            $this->tpl->setVariable('COPYRIGHT_ID', $entry->getEntryId());
            $this->tpl->setVariable('COPYRIGHT_TITLE', $entry->getTitle());
            $this->tpl->setVariable('COPYRIGHT_DESCRIPTION', $entry->getDescription());
            $this->tpl->parseCurrentBlock();
        }
        
        $this->tpl->setCurrentBlock('copyright_selection');
        if (!$found) {
            $this->tpl->setVariable('COPYRIGHT_CHECKED', 'checked="checked"');
        }
        $this->tpl->setVariable('COPYRIGHT_ID', 0);
        $this->tpl->setVariable('COPYRIGHT_TITLE', $this->lng->txt('meta_cp_own'));
        
        $this->tpl->setVariable("TXT_COPYRIGHT", $this->lng->txt('meta_copyright'));
        if (!$found) {
            $this->tpl->setVariable('COPYRIGHT_VAL', $desc);
        }
    }
}
