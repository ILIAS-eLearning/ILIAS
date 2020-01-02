<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
require_once('./Services/Repository/classes/class.ilObjectPlugin.php');

/**
* TableGUI class for search results
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesSearch
*/
class ilSearchResultTableGUI extends ilTable2GUI
{
    
    /**
    * Constructor
    */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_presenter)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];
        $lng = $DIC['lng'];

        $this->setId("ilSearchResultsTable");

        $this->presenter = $a_presenter;
        $this->setId('search_' . $GLOBALS['DIC']['ilUser']->getId());
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setTitle($lng->txt("search_results"));
        $this->setLimit(999);
        //		$this->setId("srcres");
        
        //$this->addColumn("", "", "1", true);
        #$this->addColumn($this->lng->txt("type"), "type", "1");
        #$this->addColumn($this->lng->txt("search_title_description"), "title_sort");
        $this->addColumn($this->lng->txt("type"), "type", "1");
        $this->addColumn($this->lng->txt("search_title_description"), "title");

        $all_cols = $this->getSelectableColumns();
        foreach ($this->getSelectedColumns() as $col) {
            $this->addColumn($all_cols[$col]['txt'], $col, '50px');
        }

        if ($this->enabledRelevance()) {
            #$this->addColumn($this->lng->txt('lucene_relevance_short'),'s_relevance','50px');
            $this->addColumn($this->lng->txt('lucene_relevance_short'), 'relevance', '50px');
            $this->setDefaultOrderField("s_relevance");
            $this->setDefaultOrderDirection("desc");
        }
        
        
        $this->addColumn($this->lng->txt("actions"), "", "10px");
        
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.search_result_row.html", "Services/Search");
        //$this->disable("footer");
        $this->setEnableTitle(true);
        $this->setEnableNumInfo(false);
        $this->setShowRowsSelector(false);
        
        include_once "Services/Object/classes/class.ilObjectActivation.php";
    }
    
    public function numericOrdering($a_field)
    {
        switch ($a_field) {
            case 'relevance':
                return true;
        }
        
        
        return parent::numericOrdering($a_field);
    }
    
    /**
     * Get selectable columns
     * @return
     */
    public function getSelectableColumns()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        
        return array('create_date' =>
                        array(
                            'txt' => $this->lng->txt('create_date'),
                            'default' => false
            )
        );
    }
    
    
    /**
    * Fill table row
    */
    protected function fillRow($a_set)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $objDefinition = $DIC['objDefinition'];

        $obj_id = $a_set["obj_id"];
        $ref_id = $a_set["ref_id"];
        $type 	= $a_set['type'];
        $title 	= $a_set['title'];
        $description = $a_set['description'];
        $relevance = $a_set['relevance'];
        
        if (!$type) {
            return false;
        }
        
        include_once './Services/Search/classes/Lucene/class.ilLuceneSearchObjectListGUIFactory.php';
        $item_list_gui = ilLuceneSearchObjectListGUIFactory::factory($type);
        $item_list_gui->initItem($ref_id, $obj_id, $title, $description);
        $item_list_gui->setContainerObject($this->parent_obj);
        $item_list_gui->setSearchFragment($this->presenter->lookupContent($obj_id, 0));
        $item_list_gui->setSeparateCommands(true);
        
        ilObjectActivation::addListGUIActivationProperty($item_list_gui, $a_set);
        
        $this->presenter->appendAdditionalInformation($item_list_gui, $ref_id, $obj_id, $type);
        
        $this->tpl->setVariable("ACTION_HTML", $item_list_gui->getCommandsHTML());

        if ($html = $item_list_gui->getListItemHTML($ref_id, $obj_id, $title, $description)) {
            $item_html[$ref_id]['html'] = $html;
            $item_html[$ref_id]['type'] = $type;
        }
            
        $this->tpl->setVariable("HREF_IMG", $item_list_gui->default_command["link"]);
        
        global $DIC;

        $lng = $DIC['lng'];
        
        if ($this->enabledRelevance()) {
            include_once "Services/UIComponent/ProgressBar/classes/class.ilProgressBar.php";
            $pbar = ilProgressBar::getInstance();
            $pbar->setCurrent($relevance);
            
            $this->tpl->setCurrentBlock('relev');
            $this->tpl->setVariable('REL_PBAR', $pbar->render());
            $this->tpl->parseCurrentBlock();
        }
        

        $this->tpl->setVariable("ITEM_HTML", $html);
        
        foreach ($this->getSelectedColumns() as $field) {
            switch ($field) {
                case 'create_date':
                    $this->tpl->setCurrentBlock('creation');
                    $this->tpl->setVariable('CREATION_DATE', ilDatePresentation::formatDate(new ilDateTime(ilObject::_lookupCreationDate($obj_id), IL_CAL_DATETIME)));
                    $this->tpl->parseCurrentBlock();
            }
        }
        
        

        if (!$objDefinition->isPlugin($type)) {
            $type_txt = $lng->txt('icon') . ' ' . $lng->txt('obj_' . $type);
            $icon = ilObject::_getIcon($obj_id, 'small', $type);
        } else {
            include_once("./Services/Component/classes/class.ilPlugin.php");
            $type_txt = ilObjectPlugin::lookupTxtById($type, "obj_" . $type);
            $icon = ilObject::_getIcon($obj_id, 'small', $type);
        }

        $this->tpl->setVariable(
            "TYPE_IMG",
            ilUtil::img(
                $icon,
                $type_txt,
                '',
                '',
                0,
                '',
                'ilIcon'
            )
        );
    }
    
    /**
     * Check if relevance is visible
     * @return
     */
    protected function enabledRelevance()
    {
        return ilSearchSettings::getInstance()->enabledLucene() and ilSearchSettings::getInstance()->isRelevanceVisible();
    }
}
