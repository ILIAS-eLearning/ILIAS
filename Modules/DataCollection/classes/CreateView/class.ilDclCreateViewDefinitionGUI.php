<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDclCreateViewDefinitionGUI
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 *
 * @ilCtrl_Calls ilDclCreateViewDefinitionGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector
 * @ilCtrl_Calls ilDclCreateViewDefinitionGUI: ilPublicUserProfileGUI, ilPageObjectGUI
 */
class ilDclCreateViewDefinitionGUI extends ilPageObjectGUI
{

    /**
     * @var ilDclCreateViewDefinition
     */
    public $obj;
    /**
     * @var ilCtrl
     */
    public $ctrl;
    /**
     * @var ilDclTableView
     */
    public $tableview;
    /**
     * @var ilDclCreateViewTableGUI
     */
    protected $table_gui;


    /**
     * @param     $tableview_id
     * @param int $a_definition_id
     */
    public function __construct($tableview_id, $a_definition_id = 0)
    {
        global $DIC;
        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];
        /**
         * @var $ilCtrl ilCtrl
         */
        $this->ctrl = $ilCtrl;
        $this->tableview = ilDclTableView::findOrGetInstance($tableview_id);

        // we always need a page object - create on demand
        if (!ilPageObject::_exists('dclf', $tableview_id)) {
            $viewdef = new ilDclCreateViewDefinition();
            $viewdef->setId($tableview_id);
            $viewdef->setParentId(ilObject2::_lookupObjectId($_GET['ref_id']));
            $viewdef->setActive(false);
            $viewdef->create();
        }

        parent::__construct("dclf", $tableview_id);

        $table = new ilDclCreateViewTableGUI($this);
        $this->table_gui = $table;
        $this->tpl->setContent($table->getHTML());

    }


    /**
     * execute command
     */
    public function executeCommand()
    {
        global $DIC;
        $ilLocator = $DIC['ilLocator'];
        $lng = $DIC['lng'];

        $next_class = $this->ctrl->getNextClass($this);

        $viewdef = $this->getPageObject();
        if ($viewdef) {
            $this->ctrl->setParameter($this, "dclv", $viewdef->getId());
            $title = $lng->txt("dcl_view_viewdefinition");
        }

        switch ($next_class) {
            case "ilpageobjectgui":
                throw new ilCOPageException("Deprecated. ilDclDetailedViewDefinitionGUI gui forwarding to ilpageobject");
            default:
                if ($viewdef) {
                    $this->setPresentationTitle($title);
                    $ilLocator->addItem($title, $this->ctrl->getLinkTarget($this, "preview"));
                }

                return parent::executeCommand();
        }
    }


    /**
     *
     */
    protected function activate()
    {
        $page = $this->getPageObject();
        $page->setActive(true);
        $page->update();
        $this->ctrl->redirect($this, 'edit');
    }


    /**
     *
     */
    protected function deactivate()
    {
        $page = $this->getPageObject();
        $page->setActive(false);
        $page->update();
        $this->ctrl->redirect($this, 'edit');
    }


    /**
     * confirmDelete
     */
    public function confirmDelete()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];

        $conf = new ilConfirmationGUI();
        $conf->setFormAction($ilCtrl->getFormAction($this));
        $conf->setHeaderText($lng->txt('dcl_confirm_delete_detailed_view_title'));

        $conf->addItem('tableview', (int) $this->tableview_id, $lng->txt('dcl_confirm_delete_detailed_view_text'));

        $conf->setConfirm($lng->txt('delete'), 'deleteView');
        $conf->setCancel($lng->txt('cancel'), 'cancelDelete');

        $tpl->setContent($conf->getHTML());
    }


    /**
     * cancelDelete
     */
    public function cancelDelete()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $ilCtrl->redirect($this, "edit");
    }


    /**
     *
     */
    public function deleteView()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        if ($this->tableview_id && ilDclDetailedViewDefinition::exists($this->tableview_id)) {
            $pageObject = new ilDclDetailedViewDefinition($this->tableview_id);
            $pageObject->delete();
        }

        ilUtil::sendSuccess($lng->txt("dcl_empty_detailed_view_success"), true);

        // Bug fix for mantis 22537: Redirect to settings-tab instead of fields-tab. This solves the problem and is more intuitive.
        $ilCtrl->redirectByClass("ilDclTableViewEditGUI", "editGeneralSettings");
    }


    /**
     * Release page lock
     * overwrite to redirect properly
     */
    public function releasePageLock()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $this->getPageObject()->releasePageLock();
        ilUtil::sendSuccess($lng->txt("cont_page_lock_released"), true);
        $ilCtrl->redirectByClass('ilDclTableViewGUI', "show");
    }


    /**
     * Finalizing output processing
     *
     * @param string $a_output
     *
     * @return string
     */
    public function postOutputProcessing($a_output)
    {
        // You can use this to parse placeholders and the like before outputting

        if ($this->getOutputMode() == ilPageObjectGUI::PREVIEW) {
            //page preview is not being used inside DataCollections - if you are here, something's probably wrong

            //
            //			// :TODO: find a suitable presentation for matched placeholders
            //			$allp = ilDataCollectionRecordViewViewdefinition::getAvailablePlaceholders($this->table_id, true);
            //			foreach ($allp as $id => $item) {
            //				$parsed_item = new ilTextInputGUI("", "fields[" . $item->getId() . "]");
            //				$parsed_item = $parsed_item->getToolbarHTML();
            //
            //				$a_output = str_replace($id, $item->getTitle() . ": " . $parsed_item, $a_output);
            //			}
        } // editor
        else {
            if ($this->getOutputMode() == ilPageObjectGUI::EDIT) {
                $allp = $this->getPageObject()->getAvailablePlaceholders();

                // :TODO: find a suitable markup for matched placeholders
                foreach ($allp as $item) {
                    $a_output = str_replace($item, "<span style=\"color:green\">" . $item . "</span>", $a_output);
                }
            }
        }

        return $a_output;
    }


    /**
     * Save table entries
     */
    public function saveTable() {
        $f = new ilDclDefaultValueFactory();
        foreach ($_POST as $key => $value) {
            if (strpos($key, "default_") === 0) {
                $parts = explode("_", $key);
                $id = $parts[1];
                $data_type_id = intval($parts[2]);

                // Delete all field values associated with this id
                $existing_values = ilDclTableViewBaseDefaultValue::findAll($data_type_id, $id);

                if (!is_null($existing_values)) {
                    foreach ($existing_values as $existing_value) {
                        $existing_value->delete();
                    }
                }

                // Create fields
                if ($value !== '') {
                    // Check number field
                    if ($data_type_id === ilDclDatatype::INPUTFORMAT_NUMBER) {
                        if (!ctype_digit($value)) {
                            ilUtil::sendFailure($this->lng->txt('dcl_tableview_default_value_fail'), true);
                            $this->ctrl->saveParameter($this, 'tableview_id');
                            $this->ctrl->redirect($this, 'presentation');
                        }
                    }

                    $default_value = $f->create($data_type_id);
                    $default_value->setTviewSetId($id);
                    $default_value->setValue($value);
                    $default_value->create();
                }
            }
        }
        /**
         * @var ilDclTableViewFieldSetting $setting
         */
        foreach ($this->tableview->getFieldSettings() as $setting) {

            if (!$setting->getFieldObject()->isStandardField()) {

                // Radio Inputs
                foreach (array("RadioGroup") as $attribute) {
                    $selection_key = $attribute . '_' . $setting->getField();
                    $selection = $_POST[$selection_key];
                    $selected_radio_attribute = explode("_", $selection)[0];

                    foreach (array("LockedCreate", "RequiredCreate", "VisibleCreate", "NotVisibleCreate") as $radio_attribute) {
                        $result = false;

                        if ($selected_radio_attribute === $radio_attribute) {
                            $result = true;
                        }

                        $setting->{'set' . $radio_attribute}($result);
                    }
                }

                // Text Inputs
                foreach (array("DefaultValue") as $attribute) {
                    $key = $attribute . '_' . $setting->getField();
                    $setting->{'set' . $attribute}($_POST[$key]);
                }

                $setting->update();
            }
        }

        // Set Workflow flag to true
        $view = ilDclTableView::getCollection()->where(array("id" => filter_input(INPUT_GET, "tableview_id")))->first();
        if (!is_null($view)) {
            $view->setStepC(true);
            $view->save();
        }

        ilUtil::sendSuccess($this->lng->txt('dcl_msg_tableview_updated'), true);
        $this->ctrl->saveParameter($this, 'tableview_id');
        $this->ctrl->redirect($this, 'presentation');
    }
}
