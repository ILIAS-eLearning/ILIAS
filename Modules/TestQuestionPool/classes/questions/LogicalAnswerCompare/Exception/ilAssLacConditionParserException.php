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
 * Class ConditionParserException
 *
 * Date: 02.04.14
 * Time: 15:40
 * @author Thomas Joußen <tjoussen@databay.de>
 * @author Björn Heyser <bheyser@databay.de>
 */
class ilAssLacConditionParserException extends ilAssLacException implements ilAssLacFormAlertProvider
{
    /**
     * @var int
     */
    protected $column;

    /**
     * @param int $column
     */
    public function __construct($column)
    {
        $this->column = $column;

        parent::__construct(sprintf(
            'The expression at position "%s" is not valid',
            $this->getColumn()
        ));
    }

    /**
     * @return int
     */
    public function getColumn(): int
    {
        return $this->column;
    }

    /**
     * @param ilLanguage $lng
     * @return string
     */
    public function getFormAlert(ilLanguage $lng): string
    {
        return sprintf(
            $lng->txt("ass_lac_invalid_statement"),
            $this->getColumn()
        );
    }
}
