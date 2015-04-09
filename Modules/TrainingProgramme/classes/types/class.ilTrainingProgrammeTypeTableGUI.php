<?php
require_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once('./Modules/TrainingProgramme/classes/model/class.ilTrainingProgrammeType.php');

class ilTrainingProgrammeTypeTableGUI extends ilTable2GUI {

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


	/**
	 * @param        $parent_obj
	 * @param string $parent_cmd
	 */
    public function __construct($parent_obj, $parent_cmd) {
        global $ilCtrl, $ilTabs, $lng;
        $this->ctrl = $ilCtrl;
        $this->tabs = $ilTabs;
        $this->lng = $lng;
        $this->setPrefix('prg_types_table');
        $this->setId('prg_types_table');

        parent::__construct($parent_obj, $parent_cmd);

        $this->setRowTemplate('tpl.types_row.html', 'Modules/TrainingProgramme');
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
    public function fillRow($set){
        $this->tpl->setVariable('TITLE', $set['title']);
        $this->tpl->setVariable('DESCRIPTION', $set['description']);
        $this->tpl->setVariable('DEFAULT_LANG', $set['default_language']);
        $this->tpl->setVariable('ICON', $set['icon']);
        $this->ctrl->setParameterByClass("iltrainingprogrammetypegui", "type_id", $set['id']);
        $selection = new ilAdvancedSelectionListGUI();
        $selection->setListTitle($this->lng->txt('actions'));
        $selection->setId('action_prg_type' . $set['id']);
        $selection->addItem($this->lng->txt('edit'), 'edit', $this->ctrl->getLinkTargetByClass('iltrainingprogrammetypegui', 'edit'));
        $selection->addItem($this->lng->txt('delete'), 'delete', $this->ctrl->getLinkTargetByClass('iltrainingprogrammetypegui', 'delete'));
        $this->tpl->setVariable('ACTIONS', $selection->getHTML());
    }

    /**
     * Add columns
     */
    protected function initColumns() {
        foreach ($this->columns as $column) {
            $this->addColumn($this->lng->txt($column), $column);
        }
    }

    /**
     * Build and set data for table.
     */
    protected function buildData() {
        $types = ilTrainingProgrammeType::getAllTypes();
        $data = array();
        /** @var $type ilTrainingProgrammeType */
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