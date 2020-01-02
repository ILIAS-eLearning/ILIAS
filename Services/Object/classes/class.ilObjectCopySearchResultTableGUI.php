<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

include_once './Services/Table/classes/class.ilTable2GUI.php';
require_once('./Services/Repository/classes/class.ilObjectPlugin.php');

/**
 * Presentation of search results
 *
 * Used for object cloning
 *
 * @version $Id$
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 * @ingroup ServicesObject
 */
class ilObjectCopySearchResultTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;

    private $type = '';
    private $selected_reference = null;
    
    /**
     *
     * @param object $a_parent_class
     * @param string $a_parent_cmd
     * @return
     */
    public function __construct($a_parent_class, $a_parent_cmd, $a_type)
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->obj_definition = $DIC["objDefinition"];
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $ilUser = $DIC->user();
        $objDefinition = $DIC["objDefinition"];
        
        $this->setId('obj_copy_' . $a_type);
        parent::__construct($a_parent_class, $a_parent_cmd);
        $this->type = $a_type;
        
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
                
        if (!$objDefinition->isPlugin($this->type)) {
            $title = $this->lng->txt('obj_' . $this->type . '_duplicate');
        } else {
            include_once "Services/Component/classes/class.ilPlugin.php";
            $plugin = ilObjectPlugin::getPluginObjectByType($this->type);
            $title = $plugin->txt('obj_' . $this->type . '_duplicate');
        }
        
        $this->setTitle($title);
        $ilUser->getPref('search_max_hits');
        
        $this->addColumn($this->lng->txt('search_title_description'), 'title', '99%');
        
        $this->setEnableHeader(true);
        $this->setRowTemplate("tpl.obj_copy_search_result_row.html", "Services/Object");
        $this->setEnableTitle(true);
        $this->setEnableNumInfo(true);
        $this->setDefaultOrderField('title');
        $this->setShowRowsSelector(true);
        
        if ($objDefinition->isContainer($this->type)) {
            $this->addCommandButton('saveSource', $this->lng->txt('btn_next'));
        } else {
            $this->addCommandButton('saveSource', $title);
        }
        
        $this->addCommandButton('cancel', $this->lng->txt('btn_back'));
    }
    
    /**
     * Set selected reference
     * @param int $a_selected_reference
     * @return
     */
    public function setSelectedReference($a_selected_reference)
    {
        $this->selected_reference = $a_selected_reference;
    }
    
    /**
     * get selected reference
     * @return
     */
    public function getSelectedReference()
    {
        return $this->selected_reference;
    }
    
    /**
     * Parse search results
     * @param object $a_res
     * @return
     */
    public function parseSearchResults($a_res)
    {
        foreach ($a_res as $obj_id => $references) {
            $r['title'] 	= ilObject::_lookupTitle($obj_id);
            $r['desc']		= ilObject::_lookupDescription($obj_id);
            $r['obj_id']	= $obj_id;
            $r['refs']		= $references;
            
            $rows[] = $r;
        }
        
        $this->setData($rows ? $rows : array());
    }
    
    /**
     * fill table rows
     * @param array $set
     * @return
     */
    protected function fillRow($set)
    {
        $this->tpl->setVariable('VAL_TITLE', $set['title']);
        if (strlen($set['desc'])) {
            $this->tpl->setVariable('VAL_DESC', $set['desc']);
        }
        $this->tpl->setVariable('TXT_PATHES', $this->lng->txt('pathes'));
        
        foreach ((array) $set['refs'] as $reference) {
            include_once './Services/Tree/classes/class.ilPathGUI.php';
            $path = new ilPathGUI();
            
            $this->tpl->setCurrentBlock('path');
            $this->tpl->setVariable('VAL_ID', $reference);
            $this->tpl->setVariable('VAL_PATH', $path->getPath(ROOT_FOLDER_ID, $reference));
            
            if ($reference == $this->getSelectedReference()) {
                $this->tpl->setVariable('VAL_CHECKED', 'checked="checked"');
            }
            
            $this->tpl->parseCurrentBlock();
        }
    }
}
