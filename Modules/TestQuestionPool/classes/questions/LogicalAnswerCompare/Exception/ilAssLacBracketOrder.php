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
 * Class BracketOrder.
 */
class ilAssLacBracketOrder extends ilAssLacException
    implements ilAssLacFormAlertProvider
{
    /**
     * @var int
     */
    protected int $position;

    /**
     * @param int $position
     */
    public function __construct(int $position)
    {
        $this->position = $position;

        parent::__construct(sprintf(
            'The order of brackets in the condition is wrong at position %d',
            $this->getPosition()
        ));
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param ilLanguage $lng
     * @return string
     */
    public function getFormAlert(ilLanguage $lng): string
    {
        return sprintf(
            $lng->txt("ass_lac_wrong_bracket_order"),
            $this->getPosition()
        );
    }
}
