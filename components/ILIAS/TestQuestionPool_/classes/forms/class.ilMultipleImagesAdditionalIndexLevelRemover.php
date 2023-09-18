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
class ilMultipleImagesAdditionalIndexLevelRemover implements ilFormValuesManipulator
{
    protected $filesSubmissionProperties = array(
        'name', 'tmp_name', 'type', 'error', 'size'
    );

    protected $postVar;

    public function getPostVar()
    {
        return $this->postVar;
    }

    public function setPostVar($postVar): void
    {
        $this->postVar = $postVar;
    }

    protected function getFilesSubmissionProperties(): array
    {
        return $this->filesSubmissionProperties;
    }
    public function manipulateFormInputValues(array $inputValues): array
    {
        return $inputValues;
    }

    public function manipulateFormSubmitValues(array $submitValues): array
    {
        $submitValues = $this->removeAdditionalSubFieldsLevelFromSubmitValues($submitValues);

        if ($_FILES) {
            $_FILES[$this->getPostVar()] = $this->removeAdditionalSubFieldsLevelFromFilesSubmit(
                $_FILES[$this->getPostVar()]
            );
        }

        return $submitValues;
    }
    protected function isSubFieldAvailable($values, $subFieldName): bool
    {
        if (!is_array($values)) {
            return false;
        }

        if (!isset($values[$subFieldName])) {
            return false;
        }

        if (!is_array($values[$subFieldName])) {
            return false;
        }

        return true;
    }

    protected function isIteratorSubfieldAvailable($values): bool
    {
        return $this->isSubFieldAvailable($values, ilMultipleImagesInputGUI::ITERATOR_SUBFIELD_NAME);
    }

    protected function isUploadSubfieldAvailable($values): bool
    {
        return $this->isSubFieldAvailable($values, ilMultipleImagesInputGUI::IMAGE_UPLOAD_SUBFIELD_NAME);
    }

    protected function removeAdditionalSubFieldsLevelFromSubmitValues($values): array
    {
        if (!$this->isIteratorSubfieldAvailable($values)) {
            return $values;
        }

        $storedImages = $values[ilMultipleImagesInputGUI::STORED_IMAGE_SUBFIELD_NAME] ?? [];
        $actualValues = array();

        foreach ($values[ilMultipleImagesInputGUI::ITERATOR_SUBFIELD_NAME] as $index => $value) {
            if (!isset($storedImages[$index])) {
                $actualValues[$index] = '';
                continue;
            }

            $actualValues[$index] = $storedImages[$index];
        }

        return $actualValues;
    }

    protected function removeAdditionalSubFieldsLevelFromFilesSubmitProperty($uploadProperty)
    {
        if (!$this->isUploadSubfieldAvailable($uploadProperty)) {
            return $uploadProperty;
        }

        foreach ($uploadProperty as $subField => $submittedFile) {
            foreach ($submittedFile as $identifier => $uploadValue) {
                $uploadProperty[$identifier] = $uploadValue;
            }

            unset($uploadProperty[$subField]);
        }

        return $uploadProperty;
    }

    protected function removeAdditionalSubFieldsLevelFromFilesSubmit($filesSubmit)
    {
        foreach ($this->getFilesSubmissionProperties() as $uploadProperty) {
            if (!isset($filesSubmit[$uploadProperty])) {
                continue;
            }

            $filesSubmit[$uploadProperty] = $this->removeAdditionalSubFieldsLevelFromFilesSubmitProperty(
                $filesSubmit[$uploadProperty]
            );
        }

        return $filesSubmit;
    }
}
