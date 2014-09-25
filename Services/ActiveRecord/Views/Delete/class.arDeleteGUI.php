<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');

/**
 * GUI-Class arDeleteGUI
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version 2.0.6
 *
 */
class arDeleteGUI {

	/**
	 * @var  ActiveRecord
	 */
	protected $record;
	/**
	 * @var arGUI
	 */
	protected $parent_gui;
	/**
	 * @var  ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilConfirmationTableGUI
	 */
	protected $gui;
	/**
	 * @var string
	 */
	protected $lng_prefix = "";
	/**
	 * @var string
	 */
	protected $message = "";
	/**
	 * @var ilTemplate
	 */
	protected $tpl = NULL;


	/**
	 * @param              $parent_gui
	 * @param ActiveRecord $record
	 * @param ilPlugin     $plugin_object
	 */
	public function __construct(arGUI $parent_gui, ActiveRecord $record, ilPlugin $plugin_object = NULL) {
		global $ilCtrl, $tpl;

		$this->record = $record;
		$this->parent_gui = $parent_gui;
		$this->ctrl = $ilCtrl;
		$this->tpl = $tpl;
		$this->message = $this->txt("confirm_delete_record");
		$this->ctrl->saveParameter($parent_gui, 'ar_id');

		$this->initGUI();
	}



	protected function initGUI() {

		include_once("Services/Utilities/classes/class.ilConfirmationTableGUI.php");
		$this->gui = new ilConfirmationTableGUI(true);
		$this->gui->setFormName($this->txt("delete_item"));
		$this->saveParameter();
		$this->setActionTarget();
		$this->setCommandButtons();
		$this->setFormAction();
		$this->gui->setData($this->getItems());
	}


	protected function saveParameter() {
		$this->ctrl->saveParameter($parent_gui, 'ar_id');
	}


	protected function getItems() {
		return array(
			array(
				"var" => 'id',
				"id" => $this->record->getID(),
				"text" => $this->txt("object") . $this->record->getID(),
				"img" => "./templates/default/images/icon_file.png"
			)
		);
	}


	protected function setActionTarget() { }


	protected function setCommandButtons() {
		$this->gui->addCommandButton('deleteItem', $this->txt('confirm', false));
		$this->gui->addCommandButton('index', $this->txt('cancel', false));
	}


	protected function setFormAction() {
		$this->gui->setFormAction($this->ctrl->getFormAction($this->parent_gui));
	}


    /**
     * @param $message
     */
    protected function setMessage($message) {
		$this->message = $message;
	}


    /**
     * @return string
     */
    public function getHTML() {
		ilUtil::sendQuestion($this->message);

		return $this->gui->getHTML();
	}


    /**
     * @param $txt
     * @param bool $plugin_txt
     * @return string
     */
    protected function txt($txt, $plugin_txt = true) {
		return $this->parent_gui->txt($txt, $plugin_txt);
	}
}

?>