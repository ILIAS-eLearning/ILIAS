<?php

class ilOrgUnitTypeTableGUI extends ilTable2GUI
{

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var array
     */
    protected $columns = array(
        'title', 'description', 'default_language', 'icon',
    );


    public function __construct($parent_obj, $parent_cmd)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $ilTabs = $DIC['ilTabs'];
        $lng = $DIC['lng'];
        $this->ctrl = $ilCtrl;
        $this->tabs = $ilTabs;
        $this->lng = $lng;
        $this->setPrefix('orgu_types_table');
        $this->setId('orgu_types_table');
        parent::__construct($parent_obj, $parent_cmd);
        $this->setRowTemplate('tpl.types_row.html', 'Modules/OrgUnit');
        $this->initColumns();
        $this->addColumn($this->lng->txt('action'));
        $this->buildData();
        $this->setFormAction($this->ctrl->getFormAction($this->parent_obj));
    }

    /**
     * Pass data to row template
     *
     * @param array $set
     */
    public function fillRow($set)
    {
        $this->tpl->setVariable('TITLE', $set['title']);
        $this->tpl->setVariable('DESCRIPTION', $set['description']);
        $this->tpl->setVariable('DEFAULT_LANG', $set['default_language']);
        $this->tpl->setVariable('ICON', $set['icon']);
        $this->ctrl->setParameterByClass("ilorgunittypegui", "type_id", $set['id']);
        $selection = new ilAdvancedSelectionListGUI();
        $selection->setListTitle($this->lng->txt('Actions'));
        $selection->setId('action_orgu_type' . $set['id']);
        $selection->addItem($this->lng->txt('edit'), 'edit', $this->ctrl->getLinkTargetByClass('ilorgunittypegui', 'edit'));
        $selection->addItem($this->lng->txt('delete'), 'delete', $this->ctrl->getLinkTargetByClass('ilorgunittypegui', 'delete'));
        $this->tpl->setVariable('ACTIONS', $selection->getHTML());
    }

    /**
     * Add columns
     */
    protected function initColumns()
    {
        foreach ($this->columns as $column) {
            $this->addColumn($this->lng->txt($column), $column);
        }
    }

    /**
     * Build and set data for table.
     */
    protected function buildData()
    {
        $types = ilOrgUnitType::getAllTypes();
        $data = array();
        /** @var $type ilOrgUnitType */
        foreach ($types as $type) {
            $row = array();
            $row['id'] = $type->getId();
            $row['title'] = $type->getTitle($type->getDefaultLang());
            $row['default_language'] = $type->getDefaultLang();
            $row['description'] = $type->getDescription($type->getDefaultLang());
            $row['icon'] = $type->getIcon();
            $data[] = $row;
        }
        $this->setData($data);
    }
}
