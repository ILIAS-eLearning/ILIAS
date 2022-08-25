<?php

declare(strict_types=1);

/* Copyright (c) 2018 - Denis Klöpfer <denis.kloepfer@concepts-and-training.de> - Extended GPL, see LICENSE */

/**
 * A general storage interface for Individual assessment settings.
 */
interface ilIndividualAssessmentSettingsStorage
{
    /**
     * Create an entry corresponding to $settings
     */
    public function createSettings(ilIndividualAssessmentSettings $settings): void;

    /**
     * Load settings corresponding to obj
     */
    public function loadSettings(ilObjIndividualAssessment $obj): ilIndividualAssessmentSettings;

    /**
     * Update settings entry.
     */
    public function updateSettings(ilIndividualAssessmentSettings $settings): void;

    /**
     * Load info-screen settings corresponding to obj
     */
    public function loadInfoSettings(ilObjIndividualAssessment $obj): ilIndividualAssessmentInfoSettings;

    /**
     * Update info-screen settings entry.
     */
    public function updateInfoSettings(ilIndividualAssessmentInfoSettings $settings): void;

    /**
     * Delete settings entry corresponding to obj
     */
    public function deleteSettings(ilObjIndividualAssessment $obj): void;
}
