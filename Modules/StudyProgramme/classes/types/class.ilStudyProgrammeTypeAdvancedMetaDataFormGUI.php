<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php');
require_once('./Services/Form/classes/class.ilMultiSelectInputGUI.php');

/**
 * Class ilStudyProgrammeTypeAdvancedMetaDataFormGUI
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 */
class ilStudyProgrammeTypeAdvancedMetaDataFormGUI extends ilPropertyFormGUI
{

    /**
     * @var ilStudyProgrammeType
     */
    protected $type;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var
     */
    protected $parent_gui;


    public function __construct($parent_gui, ilStudyProgrammeType $type)
    {
        global $DIC;
        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $this->parent_gui = $parent_gui;
        $this->type = $type;
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->lng->loadLanguageModule('meta');
        $this->initForm();
    }


    /**
     * Save object (create or update)
     *
     * @return bool
     */
    public function saveObject()
    {
        if (!$this->fillObject()) {
            return false;
        }
        return true;
    }

    /**
     * Add all fields to the form
     */
    protected function initForm()
    {
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
        $this->setTitle($this->lng->txt('prg_type_assign_amd_sets'));
        $options = array();
        $records = ilStudyProgrammeType::getAvailableAdvancedMDRecords();
        /** @var ilAdvancedMDRecord $record */
        foreach ($records as $record) {
            $options[$record->getRecordId()] = $record->getTitle();
        }
        $selected = array();
        $records_selected = $this->type->getAssignedAdvancedMDRecordIds();
        foreach ($records_selected as $record_id) {
            $selected[] = $record_id;
        }
        $item = new ilMultiSelectInputGUI($this->lng->txt('prg_type_available_amd_sets'), 'amd_records');
        $item->setOptions($options);
        $item->setValue($selected);
        $this->addItem($item);
        $this->addCommandButton('updateAMD', $this->lng->txt('save'));
    }

    /**
     * Check validity of form and pass values from form to object
     *
     * @return bool
     */
    protected function fillObject()
    {
        $this->setValuesByPost();
        if (!$this->checkInput()) {
            return false;
        }
        try {
            // Assign and deassign amd records. A plugin could prevent those actions.
            $record_ids_selected = (array) $this->getInput('amd_records');
            $record_ids = $this->type->getAssignedAdvancedMDRecordIds(true);
            $record_ids_removed = array_diff($record_ids, $record_ids_selected);
            $record_ids_added = array_diff($record_ids_selected, $record_ids);
            foreach ($record_ids_added as $record_id) {
                $this->type->assignAdvancedMDRecord($record_id);
            }
            foreach ($record_ids_removed as $record_id) {
                $this->type->deassignAdvancedMdRecord($record_id);
            }
            return true;
        } catch (ilException $e) {
            ilUtil::sendFailure($e->getMessage());
            return false;
        }
    }
}
