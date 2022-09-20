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

    /**
     * ilAssOrderingImagesInputGUI constructor.
     *
     * @param assOrderingQuestion $questionOBJ
     * @param string $postVar
     */
    public function __construct(ilAssOrderingFormValuesObjectsConverter $converter, $postVar)
    {
        require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssOrderingDefaultElementFallback.php';
        $manipulator = new ilAssOrderingDefaultElementFallback();
        $this->addFormValuesManipulator($manipulator);

        parent::__construct('', $postVar);

        $this->addFormValuesManipulator($converter);

        self::$instanceCounter++;
    }

    public static $instanceCounter = 0;

    /**
     * FOR COMPATIBILITY ONLY
     *
     * @param $stylingDisabled
     */
    public function setStylingDisabled($stylingDisabled): void
    {
    }

    /**
     * FOR COMPATIBILITY ONLY
     *
     * @return bool
     */
    public function getStylingDisabled(): bool
    {
        return false;
    }

    /**
     * @param ilAssOrderingElementList $elementList
     */
    public function setElementList(ilAssOrderingElementList $elementList): void
    {
        $this->setIdentifiedMultiValues($elementList->getRandomIdentifierIndexedElements());
    }

    /**
     * @param integer $questionId
     * @return ilAssOrderingElementList
     */
    public function getElementList($questionId): ilAssOrderingElementList
    {
        require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssOrderingElementList.php';
        return ilAssOrderingElementList::buildInstance($questionId, $this->getIdentifiedMultiValues());
    }

    /**
     * @param string $filenameInput
     * @return bool
     */
    protected function isValidFilenameInput($filenameInput): bool
    {
        /* @var ilAssOrderingElement $filenameInput */
        return (bool) strlen($filenameInput->getContent());
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
