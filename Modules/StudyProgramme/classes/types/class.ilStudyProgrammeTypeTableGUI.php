<?php
require_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once('./Modules/StudyProgramme/classes/model/class.ilStudyProgrammeType.php');
require_once('./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php');

class ilStudyProgrammeTypeTableGUI extends ilTable2GUI {

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

        $this->setRowTemplate('tpl.types_row.html', 'Modules/StudyProgramme');
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
        $icon = "";
        $type = new ilStudyProgrammeType($set['id']);

        if(is_file($type->getIconPath(true))) {
            $icon = $type->getIconPath(true);
        }

        $this->tpl->setVariable('TITLE', $set['title']);
        $this->tpl->setVariable('DESCRIPTION', $set['description']);
        $this->tpl->setVariable('DEFAULT_LANG', $set['default_language']);

        if($set["icon"]) {
            $this->tpl->setCurrentBlock("icon");#
            $this->tpl->setVariable('ICON', $icon);
            $this->tpl->setVariable('ICON_ALT', $set["icon"]);
            $this->tpl->parseCurrentBlock();
        }

        $this->ctrl->setParameterByClass("ilstudyprogrammetypegui", "type_id", $set['id']);
        $selection = new ilAdvancedSelectionListGUI();
        $selection->setListTitle($this->lng->txt('actions'));
        $selection->setId('action_prg_type' . $set['id']);
        $selection->addItem($this->lng->txt('edit'), 'edit', $this->ctrl->getLinkTargetByClass('ilstudyprogrammetypegui', 'edit'));
        $selection->addItem($this->lng->txt('delete'), 'delete', $this->ctrl->getLinkTargetByClass('ilstudyprogrammetypegui', 'delete'));
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
        $types = ilStudyProgrammeType::getAllTypes();
        $data = array();
        /** @var $type ilStudyProgrammeType */
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