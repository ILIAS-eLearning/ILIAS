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
 * Class AbstractOperation
 *
 * Date: 25.03.13
 * Time: 15:37
 * @author Thomas Joußen <tjoussen@databay.de>
 */
abstract class ilAssLacAbstractOperation extends ilAssLacAbstractComposite
{
    /**
     * @var bool
     */
    protected $negated = false;

    /**
     * @return string
     */
    abstract public function getPattern(): string;

    /**
     * @param boolean $negated
     */
    public function setNegated($negated): void
    {
        $this->negated = $negated;
    }

    /**
     * @return boolean
     */
    public function isNegated(): bool
    {
        return $this->negated;
    }
}
