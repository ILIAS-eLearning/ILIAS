<?php
use CaT\Plugins\TalentAssessment;

include_once("./Services/Repository/classes/class.ilObjectPlugin.php");

/**
 * Application class for talent assessment repository object.
 *
 * @author 		Stefan Hecken <stefan.hecken@concepts-and-training.de> 
 */
class ilObjTalentAssessment extends ilObjectPlugin implements TalentAssessment\ObjTalentAssessment{
	const PLUGIN_TYPE = "xtas";

	/**
	 * @var TalentAssessment|null
	 */
	protected $settings;

	/**
	 * @var ilActions|null
	 */
	protected $actions;

	function __construct($a_ref_id = 0) {
		$this->settings = null;
		$this->actions = null;

		parent::__construct($a_ref_id);

		// read settings again from the database if we already
		// have a ref id, as 0 for ref_id means, we are just
		// creating the object.
		if ($a_ref_id !== 0) {
			$this->doRead();
		}
	}

	/**
	 * @return ilActions
	 */
	public function getActions() {
		if ($this->actions === null) {
			$this->actions = new TalentAssessment\ilActions($this, $this->getSettingsDB(), $this->getObservatorDB(), $this->getObservationsDB());
		}
		return $this->actions;
	}

	/**
	 * Get type.
	 */
	final function initType() {
		$this->setType(self::PLUGIN_TYPE);
	}

	/**
	 * Create object
	 */
	function doCreate() {
	}

	/**
	 * Read data from db
	 */
	function doRead() {
		if($this->plugin) {
			$this->settings = $this->getSettingsDB()->select((int)$this->getId());
		}
	}

	/**
	 * Update data
	 */
	function doUpdate() {
		$this->getSettingsDB()->update($this->settings);
	}

	/**
	 * Delete data from db
	 */
	function doDelete() {
		$this->getSettingsDB()->delete((int)$this->getId());
	}

	/**
	 * Do Cloning
	 */
	function doCloneObject($a_target_id, $a_copy_id, $new_obj) {
		$new_obj->setSettings($this->settings);
	}

	// Custom stuff
	/**
	 * @throws	LogicExxeption if object already has settings.
	 * @return	null
	 */
	public function setSettings(TalentAssessment\Settings\TalentAssessment $settings) {
		if ($this->settings !== null) {
			throw new \LogicException("Object already initialized.");
		}
		$this->settings = $settings;
	}

	/**
	 * @param	Closure	$update		function from Settings/WBD to Settings/WBD
	 * @return	null
	 */
	public function updateSettings(\Closure $update) {
		$this->settings = $update($this->getSettings());
	}

	/**
	 * @throws	LogicException if object is not properly initialized.
	 * @return	Settings\WBD
	 */
	public function getSettings() {
		if ($this->settings === null) {
			throw new \LogicException("Object not properly initialized.");
		}
		return $this->settings;
	}

	/**
	 * @return	$DB
	 */
	public function getSettingsDB() {
		return $this->plugin->getSettingsDB();
	}

	/**
	 * @return	$DB
	 */
	public function getObservatorDB() {
		return $this->plugin->getObservatorDB();
	}

	/**
	 * @return	$DB
	 */
	public function getObservationsDB() {
		return $this->plugin->getObservationsDB();
	}
}