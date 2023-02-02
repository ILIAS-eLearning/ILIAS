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
class ilIdentifiedMultiFilesJsPositionIndexRemover extends ilIdentifiedMultiValuesJsPositionIndexRemover
{
    protected $postVar = null;

    public function getPostVar()
    {
        return $this->postVar;
    }

    public function setPostVar($postVar): void
    {
        $this->postVar = $postVar;
    }

    public function manipulateFormInputValues(array $inputValues): array
    {
        // KEEP THIS INTERFACE METHOD OVERWRITTEN THIS LIKE (!)
        return $inputValues;
    }

    public function manipulateFormSubmitValues(array $values): array
    {
        if ($this->isFileSubmitAvailable()) {
            $this->prepareFileSubmit();
        }

        return $values;
    }

    protected function isFileSubmitAvailable(): bool
    {
        if (!isset($_FILES[$this->getPostVar()])) {
            return false;
        }

        if (!is_array($_FILES[$this->getPostVar()])) {
            return false;
        }

        if (!in_array('tmp_name', array_keys($_FILES[$this->getPostVar()]))) {
            return false;
        }

        return true;
    }

    protected function prepareFileSubmit(): void
    {
        $_FILES[$this->getPostVar()] = $this->prepareMultiFilesSubmitValues(
            $_FILES[$this->getPostVar()]
        );
    }

    protected function prepareMultiFilesSubmitValues($filesSubmitValues): array
    {
        return $this->removePositionIndexLevels($filesSubmitValues);
    }
}
