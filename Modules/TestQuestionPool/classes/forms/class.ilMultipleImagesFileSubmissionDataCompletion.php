<?php

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
 * @author        Björn Heyser <bheyser@databay.de>
 */
class ilMultipleImagesFileSubmissionDataCompletion implements ilFormValuesManipulator
{
    protected stdClass $dodging_files;

    public function __construct(stdClass $dodging_files)
    {
        $this->dodging_files = $dodging_files;
    }

    public function manipulateFormInputValues(array $inputValues) : array
    {
        return $inputValues;
    }
    
    public function manipulateFormSubmitValues(array $submitValues) : array
    {
        $this->populateStoredFileCustomUploadProperty($submitValues);

        return $submitValues;
    }

    protected function populateStoredFileCustomUploadProperty(array $submitValues) : void
    {
        foreach ($submitValues as $identifier => $storedFilename) {
            $this->dodging_files->{$identifier} = $storedFilename;
        }
    }
}
