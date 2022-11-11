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

class ilAssOrderingImagesInputGUI extends ilMultipleImagesInputGUI
{
    public const POST_VARIABLE_NAME = 'ordering';

    public function __construct(ilAssOrderingFormValuesObjectsConverter $converter, string $postVar)
    {
        $manipulator = new ilAssOrderingDefaultElementFallback();
        $this->addFormValuesManipulator($manipulator);

        parent::__construct('', $postVar);

        $this->addFormValuesManipulator($converter);

        self::$instanceCounter++;
    }

    public static $instanceCounter = 0;

    public function setStylingDisabled($stylingDisabled): void
    {
    }

    public function getStylingDisabled(): bool
    {
        return false;
    }

    public function setElementList(ilAssOrderingElementList $elementList): void
    {
        $this->setIdentifiedMultiValues($elementList->getRandomIdentifierIndexedElements());
    }

    /**
     * @param integer $questionId
     */
    public function getElementList($questionId): ilAssOrderingElementList
    {
        return ilAssOrderingElementList::buildInstance($questionId, $this->getIdentifiedMultiValues());
    }

    public function setPending(string $a_val): void
    {
        $this->pending = $a_val;
    }

    public function getPending(): string
    {
        return $this->pending;
    }
}
