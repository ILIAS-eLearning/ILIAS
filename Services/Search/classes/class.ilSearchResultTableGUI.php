<?php

declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* TableGUI class for search results
*
* @author Alex Killing <alex.killing@gmx.de>
*
* @ingroup ServicesSearch
*/
class ilSearchResultTableGUI extends ilTable2GUI
{
    protected ilObjUser $user;
    protected ilSearchResultPresentation $presenter;
    protected ilObjectDefinition $objDefinition;

    /**
    * Constructor
    */
    public function __construct($a_parent_obj, $a_parent_cmd, ilSearchResultPresentation $a_presenter)
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->presenter = $a_presenter;
        $this->objDefinition = $DIC['objDefinition'];

        $this->setId("ilSearchResultsTable");

        $this->setId('search_' . $this->user->getId());
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setTitle($this->lng->txt("search_results"));
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
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.search_result_row.html", "Services/Search");
        //$this->disable("footer");
        $this->setEnableTitle(true);
        $this->setEnableNumInfo(false);
        $this->setShowRowsSelector(false);
    }

    public function numericOrdering(string $a_field): bool
    {
        switch ($a_field) {
            case 'relevance':
                return true;
        }
        return parent::numericOrdering($a_field);
    }

    /**
     * Get selectable columns
     * @return array
     */
    public function getSelectableColumns(): array
    {
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
    protected function fillRow(array $a_set): void
    {
        $obj_id = $a_set["obj_id"];
        $ref_id = $a_set["ref_id"];
        $type = $a_set['type'];
        $title = $a_set['title'];
        $description = $a_set['description'];
        $relevance = $a_set['relevance'];

        if (!$type) {
            return;
        }

        $item_list_gui = ilLuceneSearchObjectListGUIFactory::factory($type);
        $item_list_gui->initItem($ref_id, $obj_id, $type, $title, $description);
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


        if ($this->enabledRelevance()) {
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



        if (!$this->objDefinition->isPlugin($type)) {
            $type_txt = $this->lng->txt('icon') . ' ' . $this->lng->txt('obj_' . $type);
            $icon = ilObject::_getIcon((int) $obj_id, 'small', $type);
        } else {
            $type_txt = ilObjectPlugin::lookupTxtById($type, "obj_" . $type);
            $icon = ilObject::_getIcon((int) $obj_id, 'small', $type);
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

    protected function enabledRelevance(): bool
    {
        return
            ilSearchSettings::getInstance()->enabledLucene() &&
            ilSearchSettings::getInstance()->isRelevanceVisible();
    }
}
