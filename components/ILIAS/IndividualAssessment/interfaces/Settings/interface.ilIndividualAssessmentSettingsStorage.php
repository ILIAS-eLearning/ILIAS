<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
