<?php
/**
 * A general storage interface for manual assessment settings.
 * @author Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
 */
require_once 'Modules/ManualAssessment/classes/class.ilObjManualAssessment.php';
require_once "Modules/ManualAssessment/exceptions/class.ilManualAssessmentException.php";
interface ilManualAssessmentSettingsStorage {
	/**
	 * Create a entry corresponding to $settings
	 *
	 * @param	ilManualAssessmentSettings	$settings
	 */
	public function createSettings(ilManualAssessmentSettings $settings);

	/**
	 * Load settings corresponding to obj
	 *
	 * @param	ilObjManualAssessment	$obj
	 * @return	ilManualAssessmentSettings	$settings
	 */
	public function loadSettings(ilObjManualAssessment $obj);

	/**
	 * Update settings entry.
	 *
	 * @param	ilManualAssessmentSettings	$settings
	 */
	public function updateSettings(ilManualAssessmentSettings $settings);

	/**
	 * Load info-screen settings corresponding to obj
	 *
	 * @param	ilObjManualAssessment	$obj
	 * @return	ilManualAssessmentSettings	$settings
	 */
	public function loadInfoSettings(ilObjManualAssessment $obj);
	
	/**
	 * Update info-screen settings entry.
	 *
	 * @param	ilManualAssessmentSettings	$settings
	 */
	public function updateInfoSettings(ilManualAssessmentInfoSettings $settings);

	/**
	 * Delete settings entry corresponding to obj
	 *
	 * @param	ilObjManualAssessment	$obj
	 * @return	ilManualAssessmentSettings	$settings
	 */
	public function deleteSettings(ilObjManualAssessment $obj);
}