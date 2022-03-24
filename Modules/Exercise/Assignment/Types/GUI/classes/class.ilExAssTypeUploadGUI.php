<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Upload type gui implementations
 *
 * @author Alex Killing <killing@leifos.de>
 */
class ilExAssTypeUploadGUI implements ilExAssignmentTypeGUIInterface
{
    use ilExAssignmentTypeGUIBase;

    /**
     * @inheritdoc
     */
    public function addEditFormCustomProperties(ilPropertyFormGUI $form) : void
    {
    }

    /**
     * @inheritdoc
     */
    public function importFormToAssignment(ilExAssignment $ass, ilPropertyFormGUI $form) : void
    {
    }

    /**
     * @inheritdoc
     */
    public function getFormValuesArray(ilExAssignment $ass) : array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getOverviewContent(ilInfoScreenGUI $a_info, ilExSubmission $a_submission) : void
    {
    }
}
