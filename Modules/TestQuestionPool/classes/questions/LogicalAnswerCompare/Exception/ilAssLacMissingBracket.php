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
 * Class ilAssLacAnswerIndexNotExist
 * @package
 *
 * Date: 25.03.13
 * Time: 15:15
 * @author Thomas Joußen <tjoussen@databay.de>
 * @author Björn Heyser <bheyser@databay.de>
 */
class ilAssLacMissingBracket extends ilAssLacException implements ilAssLacFormAlertProvider
{
    /**
     * @var string
     */
    protected $bracket;

    /**
     * @param string $bracket
     */
    public function __construct($bracket)
    {
        $this->bracket = $bracket;

        parent::__construct(sprintf(
            'There is a bracket "%s" missing in the condition',
            $this->getBracket()
        ));
    }

    /**
     * @return string
     */
    public function getBracket(): string
    {
        return $this->bracket;
    }

    /**
     * @param ilLanguage $lng
     * @return string
     */
    public function getFormAlert(ilLanguage $lng): string
    {
        return sprintf(
            $lng->txt("ass_lac_missing_bracket"),
            $this->getBracket()
        );
    }
}
