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
 * Exercise exceptions class
 *
 * @author Roland Küstermann <roland@kuestermann.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExerciseException extends ilException
{
    public static int $ID_MISMATCH = 0;
    public static int $ID_DEFLATE_METHOD_MISMATCH = 1;
}
