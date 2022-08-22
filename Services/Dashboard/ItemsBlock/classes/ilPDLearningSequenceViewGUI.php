<?php declare(strict_types=1);

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

class ilPDLearningSequenceViewGUI extends ilPDSelectedItemsBlockViewGUI
{
    public function getGroups() : array
    {
        if ($this->viewSettings->isSortedByLocation()) {
            return $this->groupItemsByLocation();
        } elseif ($this->viewSettings->isSortedByAlphabet()) {
            return $this->sortItemsByAlphabetInOneGroup();
        }

        return $this->groupItemsByType();
    }

    public function getScreenId() : string
    {
        return 'learning_sequence';
    }

    public function getTitle() : string
    {
        return $this->lng->txt('dash_learningsequences');
    }

    public function supportsSelectAll() : bool
    {
        return true;
    }

    public function getIntroductionHtml() : string
    {
        return '';
    }
}