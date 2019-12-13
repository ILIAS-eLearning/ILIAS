<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilCmiXapiLrsTypesTableGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiLrsTypesTableGUI extends ilTable2GUI
{
    const TABLE_ID = 'cmix_lrs_types_table';
    
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $this->setId(self::TABLE_ID);
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->setFormAction($DIC->ctrl()->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate('tpl.cmix_lrs_types_table_row.html', 'Modules/CmiXapi');
        
        $this->setTitle($DIC->language()->txt('tbl_lrs_types_header'));
        //$this->setDescription($DIC->language()->txt('tbl_lrs_types_header_info'));
        
        $this->initColumns();
    }
    
    protected function initColumns()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $this->addColumn($DIC->language()->txt('tbl_lrs_type_title'), 'title');
        $this->addColumn($DIC->language()->txt('tbl_lrs_type_availability'), 'availability');
        $this->addColumn($DIC->language()->txt('tbl_lrs_type_usages'), 'usages');
        $this->addColumn('', '', '1%');
    }
    
    public function fillRow($data)
    {
        $this->tpl->setVariable('LRS_TYPE_TITLE', $data['title']);
        $this->tpl->setVariable('LRS_TYPE_AVAILABILITY', $this->getAvailabilityLabel($data['availability']));
        $this->tpl->setVariable('LRS_TYPE_USAGES', $data['usages'] ? $data['usages'] : '');
        $this->tpl->setVariable('ACTIONS', $this->getActionsList($data)->getHTML());
    }
    
    protected function getAvailabilityLabel($availability)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        return $DIC->language()->txt('conf_availability_' . $availability);
    }
    
    protected function getActionsList($data)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $DIC->ctrl()->setParameter($this->parent_obj, 'lrs_type_id', $data['lrs_type_id']);
        
        $link = $DIC->ctrl()->getLinkTarget(
            $this->parent_obj,
            ilObjCmiXapiAdministrationGUI::CMD_SHOW_LRS_TYPE_FORM
        );
        
        $DIC->ctrl()->setParameter($this->parent_obj, 'lrs_type_id', '');
        
        $actionList = new ilAdvancedSelectionListGUI();
        $actionList->setListTitle($DIC->language()->txt('actions'));
        $actionList->addItem($DIC->language()->txt('edit'), '', $link);
        
        return $actionList;
    }
}
