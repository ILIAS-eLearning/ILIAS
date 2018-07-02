<?php

require_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("./Services/Form/classes/class.ilTextInputGUI.php");


/**
 * Plugin object GUI class. Baseclass for all GUI action in ILIAS
 *
 * @ilCtrl_isCalledBy ilObjComponentProviderExampleGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjComponentProviderExampleGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 */
class ilObjComponentProviderExampleGUI  extends ilObjectPluginGUI {
    const VALUES_FIELD_NAME = "values";
    const SAVE_CMD = "saveForm";

    /**
     * @var \ilTemplate
     */
    protected $ilTemplate;

    /**
     * @var \ilCtrl
     */
    protected $ilCtrl;

	/**
	 * Called after parent constructor. It's possible to define some plugin special values
	 */
	protected function afterConstructor() {
        global $DIC;
        $this->ilTemplate = $DIC->ui()->mainTemplate();
        $this->ilCtrl = $DIC->ctrl();
	}

	/**
	* Get type.  Same value as choosen in plugin.php
	*/
	final function getType() {
		return "xlep";
	}

	/**
	* Handles all commmands of this class, centralizes permission checks
	*/
	function performCommand($cmd) {
		switch ($cmd) {
            case self::SAVE_CMD:
                $this->saveForm();
            case "showContent":
                $this->ilTemplate->setContent($this->showContent());
                break;
			default:
                throw new \InvalidArgumentException("Unknown Command: '$cmd'");
		}
	}

    /**
     * Save values provided from form.
     */
    protected function saveForm() {
        $db = $this->plugin->settingsDB();
        $settings = $db->getFor((int)$this->object->getId());
        $settings = $settings->withProvidedStrings($_POST[self::VALUES_FIELD_NAME]);
        $db->update($settings);
        $this->ilCtrl->redirect($this, "showContent");
    }

    /**
     * Show the edit form.
     *
     * @return string
     */
    public function showContent() {
        $db = $this->plugin->settingsDB();
        $settings = $db->getFor((int)$this->object->getId());

        $form = new \ilPropertyFormGUI();
        $form->setTitle($this->plugin->txt("settings_form_title"));
        $form->setFormAction($this->ilCtrl->getFormAction($this));
        $form->addCommandButton(self::SAVE_CMD, $this->txt("save"));

        $input = new \ilTextInputGUI($this->plugin->txt("values"), self::VALUES_FIELD_NAME);
        $input->setMulti(true);
        $input->setMaxLength(64);
        $input->setValue($settings->providedStrings());

        $form->addItem($input);

        return $form->getHTML();
    }

	/**
	* After object has been created -> jump to this command
	*/
	function getAfterCreationCmd() {
		return "showContent";
	}

	/**
	* Get standard command
	*/
	function getStandardCmd() {
		return "showContent";
	}
}
