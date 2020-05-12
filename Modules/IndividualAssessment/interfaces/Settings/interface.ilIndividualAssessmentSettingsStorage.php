<?php
/**
 * A general storage interface for Individual assessment settings.
 * @author Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
 */
interface ilIndividualAssessmentSettingsStorage
{
    /**
     * Create a entry corresponding to $settings
     */
    public function createSettings(ilIndividualAssessmentSettings $settings);

    /**
     * Load settings corresponding to obj
     */
    public function loadSettings(ilObjIndividualAssessment $obj) : \ilIndividualAssessmentSettings;

    /**
     * Update settings entry.
     */
    public function updateSettings(ilIndividualAssessmentSettings $settings);

    /**
     * Load info-screen settings corresponding to obj
     */
    public function loadInfoSettings(ilObjIndividualAssessment $obj) : \ilIndividualAssessmentInfoSettings;
    
    /**
     * Update info-screen settings entry.
     */
    public function updateInfoSettings(ilIndividualAssessmentInfoSettings $settings);

    /**
     * Delete settings entry corresponding to obj
     */
    public function deleteSettings(ilObjIndividualAssessment $obj);
}
