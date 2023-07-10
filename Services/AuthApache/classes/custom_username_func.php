<?php

declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

final class ApacheCustom
{
    public static function getUsername(): string
    {
        /*
         * enter your custom login-name resolve function here
         *
         * if you are using the "auto create account" feature
         * be sure to return a valid username IN ANY CASE
         */
        return '';
    }
}
