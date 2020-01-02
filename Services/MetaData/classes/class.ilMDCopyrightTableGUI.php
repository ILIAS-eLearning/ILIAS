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
* @ingroup ServicesAdvancedMetaData
*/
include_once('Services/Table/classes/class.ilTable2GUI.php');
include_once('Services/MetaData/classes/class.ilMDCopyrightSelectionEntry.php');

class ilMDCopyrightTableGUI extends ilTable2GUI
{
    protected $lng = null;
    protected $ctrl;
    protected $parent_obj;
    protected $has_write; // [bool]
    
    /**
     * Constructor
     *
     * @access public
     * @param
     *
     */
    public function __construct($a_parent_obj, $a_parent_cmd = '', $a_has_write = false)
    {
        global $lng,$ilCtrl;
        
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->has_write = (bool) $a_has_write;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        if ($this->has_write) {
            $this->addColumn('', 'f', 1);
        }
        $this->addColumn($this->lng->txt('title'), 'title', "30%");
        $this->addColumn($this->lng->txt('md_used'), 'used', "5%");
        $this->addColumn($this->lng->txt('md_copyright_preview'), 'preview', "50%");
        
        if ($this->has_write) {
            $this->addColumn('', 'edit', "15%");
        }
        
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.show_copyright_row.html", "Services/MetaData");
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");
    }
    
    /**
     * Fill row
     *
     * @access public
     * @param
     *
     */
    public function fillRow($a_set)
    {
        if ($this->has_write) {
            $this->tpl->setVariable('VAL_ID', $a_set['id']);
        }
        $this->tpl->setVariable('VAL_TITLE', $a_set['title']);
        if (strlen($a_set['description'])) {
            $this->tpl->setVariable('VAL_DESCRIPTION', $a_set['description']);
        }
        $this->tpl->setVariable('VAL_USAGE', $a_set['used']);
        $this->tpl->setVariable('VAL_PREVIEW', $a_set['preview']);
        
        if ($this->has_write) {
            $this->ctrl->setParameter($this->getParentObject(), 'entry_id', $a_set['id']);
            $this->tpl->setVariable('EDIT_LINK', $this->ctrl->getLinkTarget($this->getParentObject(), 'editEntry'));
            $this->ctrl->clearParameters($this->getParentObject());

            $this->tpl->setVariable('TXT_EDIT', $this->lng->txt('edit'));
        }
    }
    
    /**
     * Parse records
     *
     * @access public
     * @param array array of record objects
     *
     */
    public function parseSelections()
    {
        foreach (ilMDCopyrightSelectionEntry::_getEntries() as $entry) {
            $tmp_arr['id'] = $entry->getEntryId();
            $tmp_arr['title'] = $entry->getTitle();
            $tmp_arr['description']	= $entry->getDescription();
            $tmp_arr['used'] = $entry->getUsage();
            $tmp_arr['preview'] = $entry->getCopyright();
            
            $entry_arr[] = $tmp_arr;
        }
        $this->setData($entry_arr ? $entry_arr : array());
    }
}
