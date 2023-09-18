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
class ilMultiFilesSubmitRecursiveSlashesStripper implements ilFormValuesManipulator
{
    /**
     * @var string
     */
    protected $postVar = null;

    /**
     * @return string
     */
    public function getPostVar(): ?string
    {
        return $this->postVar;
    }

    /**
     * @param string $postVar
     */
    public function setPostVar($postVar): void
    {
        $this->postVar = $postVar;
    }

    /**
     * @param array $inputValues
     * @return array $inputValues
     */
    public function manipulateFormInputValues(array $inputValues): array
    {
        return $inputValues;
    }

    /**
     * @param array $submitValues
     * @return array $submitValues
     */
    public function manipulateFormSubmitValues(array $submitValues): array
    {
        $this->manipulateFileSubmitValues();
        return $submitValues;
    }

    /**
     * perform the strip slashing on files submit
     */
    protected function manipulateFileSubmitValues(): void
    {
        if ($_FILES) {
            $_FILES[$this->getPostVar()] = ilArrayUtil::stripSlashesRecursive(
                $_FILES[$this->getPostVar()]
            );
        }
    }
}
