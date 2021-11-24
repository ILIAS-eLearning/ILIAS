<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Presentation of search results
 *
 * Used for object cloning
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
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
            $r['title'] = ilObject::_lookupTitle($obj_id);
            $r['desc'] = ilObject::_lookupDescription($obj_id);
            $r['obj_id'] = $obj_id;
            $r['refs'] = $references;
            
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
            $path = new ilPathGUI();
            
            $this->tpl->setCurrentBlock('path');
            $this->tpl->setVariable('VAL_ID', $reference);
            $this->tpl->setVariable('VAL_PATH', $path->getPath(ROOT_FOLDER_ID, (int) $reference));
            
            if ($reference == $this->getSelectedReference()) {
                $this->tpl->setVariable('VAL_CHECKED', 'checked="checked"');
            }
            
            $this->tpl->parseCurrentBlock();
        }
    }
}
