<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php');
require_once('./Services/Form/classes/class.ilMultiSelectInputGUI.php');

/**
 * Class ilStudyProgrammeTypeAdvancedMetaDataFormGUI
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 */
class ilStudyProgrammeTypeAdvancedMetaDataFormGUI extends ilPropertyFormGUI {

    /**
     * @var ilStudyProgrammeTypeRepository
     */
    protected $type_repository;

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


    public function __construct($parent_gui, ilStudyProgrammeTypeRepository $type_repository) {
        global $DIC;
        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $this->parent_gui = $parent_gui;
        $this->type_repository = $type_repository;
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
    public function saveObject(ilStudyProgrammeType $type) {
        $type = $this->fillObject($type);
        if (!$type) {
            return false;
        }
        $this->type_repository->updateType($type);
        return true;
    }


    protected function initForm()
    {
        /** @var ilAdvancedMDRecord $record */
        $records = $this->type_repository->readAllAMDRecords();
        $options = array();
        foreach ($records as $record) {
            $options[$record->getRecordId()] = $record->getTitle();
        }
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
        $this->setTitle($this->lng->txt('prg_type_assign_amd_sets'));

        $item = new ilMultiSelectInputGUI($this->lng->txt('prg_type_available_amd_sets'), 'amd_records');
        $item->setOptions($options);
        $this->addItem($item);
        $this->addCommandButton('updateAMD', $this->lng->txt('save'));
    }


    /**
     * Add all fields to the form
     */
    public function fillForm(ilStudyProgrammeType $type)
    {
        $selected = array();
        $records_selected = $this->type_repository->readAssignedAMDRecordsByType($type->getId());
        foreach ($records_selected as $record_id) {
            $selected[] = $record_id;
        }
        $item = $this->getItemByPostVar('amd_records');
        $item->setValue($selected);

    }

    /**
     * Check validity of form and pass values from form to object
     *
     * @return bool
     */
    protected function fillObject(ilStudyProgrammeType $type) {
        $this->setValuesByPost();
        if (!$this->checkInput()) {
            return null;
        }
        try {
            // Assign and deassign amd records. A plugin could prevent those actions.
            $record_ids_selected = (array) $this->getInput('amd_records');
            $record_ids = $this->type_repository->readAssignedAMDRecordIdsByType($type->getId(),true);
            $record_ids_removed = array_diff($record_ids, $record_ids_selected);
            $record_ids_added = array_diff($record_ids_selected, $record_ids);
            foreach ($record_ids_added as $record_id) {
                $type->assignAdvancedMDRecord($record_id);
            }
            foreach ($record_ids_removed as $record_id) {
                $type->deassignAdvancedMdRecord($record_id);
            }
        } catch (ilException $e) {
            ilUtil::sendFailure($e->getMessage());
            return null;
        }
        return $type;
    }

}