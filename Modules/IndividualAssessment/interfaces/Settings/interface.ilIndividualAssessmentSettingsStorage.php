<?php
/**
 * A general storage interface for Individual assessment settings.
 * @author Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
 */
require_once 'Modules/IndividualAssessment/classes/class.ilObjIndividualAssessment.php';
require_once "Modules/IndividualAssessment/exceptions/class.ilIndividualAssessmentException.php";
interface ilIndividualAssessmentSettingsStorage
{
    /**
     * Create a entry corresponding to $settings
     *
     * @param	ilIndividualAssessmentSettings	$settings
     */
    public function createSettings(ilIndividualAssessmentSettings $settings);

    /**
     * Load settings corresponding to obj
     *
     * @param	ilObjIndividualAssessment	$obj
     * @return	ilIndividualAssessmentSettings	$settings
     */
    public function loadSettings(ilObjIndividualAssessment $obj);

    /**
     * Update settings entry.
     *
     * @param	ilIndividualAssessmentSettings	$settings
     */
    public function updateSettings(ilIndividualAssessmentSettings $settings);

    /**
     * Load info-screen settings corresponding to obj
     *
     * @param	ilObjIndividualAssessment	$obj
     * @return	ilIndividualAssessmentSettings	$settings
     */
    public function loadInfoSettings(ilObjIndividualAssessment $obj);
    
    /**
     * Update info-screen settings entry.
     *
     * @param	ilIndividualAssessmentSettings	$settings
     */
    public function updateInfoSettings(ilIndividualAssessmentInfoSettings $settings);

    /**
     * Delete settings entry corresponding to obj
     *
     * @param	ilObjIndividualAssessment	$obj
     * @return	ilIndividualAssessmentSettings	$settings
     */
    public function deleteSettings(ilObjIndividualAssessment $obj);
}
