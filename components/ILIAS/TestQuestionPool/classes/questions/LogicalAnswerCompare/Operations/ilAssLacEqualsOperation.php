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
 * Class Equals
 *
 * Date: 25.03.13
 * Time: 14:57
 * @author Thomas Joußen <tjoussen@databay.de>
 */
class ilAssLacEqualsOperation extends ilAssLacAbstractOperation
{
    /**
     * @var string
     */
    public static $pattern = "=";

    /**
     * Get a human readable description of the Composite element
     * @return string
     */
    public function getDescription(): string
    {
        return 'mit genau ';
    }

    public function getPattern(): string
    {
        return self::$pattern;
    }
}
