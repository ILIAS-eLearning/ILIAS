<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';
include_once './Modules/WebResource/classes/class.ilLinkResourceItems.php';
include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
include_once './Services/Container/classes/class.ilContainerSorting.php';
include_once './Services/Container/classes/class.ilContainer.php';
include_once './Services/Container/classes/class.ilContainerSorting.php';
include_once './Services/Container/classes/class.ilContainerSortingSettings.php';


/**
* TableGUI class for search results
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesWebResource
*/

class ilWebResourceLinkTableGUI extends ilTable2GUI
{
    protected $editable = false;
    protected $web_res = null;
    
    protected $link_sort_mode = null;
    protected $link_sort_enabled = false;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_sorting = false)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];
        $ilCtrl = $DIC['ilCtrl'];
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        // Initialize
        if ($ilAccess->checkAccess('write', '', $this->getParentObject()->object->getRefId())) {
            $this->editable = true;
        }
        
        $this->enableLinkSorting($a_sorting);
        $this->web_res = new ilLinkResourceItems($this->getParentObject()->object->getId());
        
        
        $this->setTitle($lng->txt('web_resources'));
        
        if ($this->isEditable()) {
            if ($this->isLinkSortingEnabled()) {
                $this->setLimit(9999);
                $this->addColumn($lng->txt('position'), '', '10px');
                $this->addColumn($lng->txt('title'), '', '90%');
                $this->addColumn('', '', '10%');
                
                $this->addMultiCommand('saveSorting', $this->lng->txt('sorting_save'));
            } else {
                $this->addColumn($lng->txt('title'), '', '90%');
                $this->addColumn('', '', '10%');
            }
        } else {
            $this->addColumn($lng->txt('title'), '', '100%');
        }
        
        $this->initSorting();
        
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($this->getParentObject()));
        $this->setRowTemplate("tpl.webr_link_row.html", 'Modules/WebResource');
        $this->setEnableTitle(true);
        $this->setEnableNumInfo(false);
    }
    
    /**
     * Enable sorting of links
     * @param object $a_status
     * @return
     */
    public function enableLinkSorting($a_status)
    {
        $this->link_sort_enabled = $a_status;
    }
    
    /**
     * Check if link sorting is enabled
     * @return
     */
    public function isLinkSortingEnabled()
    {
        return (bool) $this->link_sort_enabled;
    }
    
    /**
     * Parse Links
     * @return
     */
    public function parse()
    {
        $rows = array();
        
        $items = $this->getWebResourceItems()->getActivatedItems();
        $items = $this->getWebResourceItems()->sortItems($items);
        
        include_once "Services/Form/classes/class.ilFormPropertyGUI.php";
        include_once "Services/Form/classes/class.ilLinkInputGUI.php";
        
        $counter = 1;
        foreach ($items as $link) {
            /* now done in ObjLinkRessourceGUI::callLink()
            if(ilParameterAppender::_isEnabled())
            {
                $link = ilParameterAppender::_append($link);
            }
            */
            $tmp['position'] = ($counter++) * 10;
            $tmp['title'] = $link['title'];
            $tmp['description'] = $link['description'];
            $tmp['target'] = $link['target'];
            $tmp['link_id'] = $link['link_id'];
            $tmp['internal'] = ilLinkInputGUI::isInternalLink($link["target"]);
            
            $rows[] = $tmp;
        }
        $this->setData($rows);
    }
    
    /**
     * @see ilTable2GUI::fillRow()
     */
    protected function fillRow($a_set)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        
        $ilCtrl->setParameterByClass(get_class($this->getParentObject()), 'link_id', $a_set['link_id']);
        
        $this->tpl->setVariable('TITLE', $a_set['title']);
        if (strlen($a_set['description'])) {
            $this->tpl->setVariable('DESCRIPTION', $a_set['description']);
        }
        // $this->tpl->setVariable('TARGET',$a_set['target']);
        $this->tpl->setVariable(
            'TARGET',
            $ilCtrl->getLinkTarget($this->parent_obj, "callLink")
        );
        
        if (!$a_set['internal']) {
            $this->tpl->setVariable('FRAME', ' target="_blank"');
            $this->tpl->touchBlock('noopener');
        }
        
        if (!$this->isEditable()) {
            return;
        }
        
        if ($this->isLinkSortingEnabled()) {
            $this->tpl->setVariable('VAL_POS', $a_set['position']);
            $this->tpl->setVariable('VAL_ITEM', $a_set['link_id']);
        }
        
        $actions = new ilAdvancedSelectionListGUI();
        $actions->setSelectionHeaderClass("small");
        $actions->setItemLinkClass("xsmall");
        
        $actions->setListTitle($lng->txt('actions'));
        $actions->setId($a_set['link_id']);
        
        $actions->addItem(
            $lng->txt('edit'),
            '',
            $ilCtrl->getLinkTargetByClass(get_class($this->getParentObject()), 'editLink')
        );
        $actions->addItem(
            $lng->txt('webr_deactivate'),
            '',
            $ilCtrl->getLinkTargetByClass(get_class($this->getParentObject()), 'deactivateLink')
        );
        $actions->addItem(
            $lng->txt('delete'),
            '',
            $ilCtrl->getLinkTargetByClass(get_class($this->getParentObject()), 'confirmDeleteLink')
        );
        $this->tpl->setVariable('ACTION_HTML', $actions->getHTML());
    }
    
    
    
    
    /**
     * Get Web resource items object
     * @return object	ilLinkResourceItems
     */
    protected function getWebResourceItems()
    {
        return $this->web_res;
    }
    
    
    /**
     * Check if links are editable
     * @return
     */
    protected function isEditable()
    {
        return (bool) $this->editable;
    }
    
    protected function initSorting()
    {
        $this->link_sort_mode = ilContainerSortingSettings::_lookupSortMode(
            $this->getParentObject()->object->getId()
        );
    }
}
