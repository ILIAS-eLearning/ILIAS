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

declare(strict_types=1);

namespace ILIAS\Services\Help\ScreenId;

trait ClassNameToScreenId
{
    private static string $REGEX = '/il(Object|Obj|)(.*)GUI/mi';

    protected function classNameToScreenId(string $classname): ?string
    {
        $classname = preg_replace(self::$REGEX, "$2", $classname);
        $classname = $this->snakeToCamel($classname);

        return $classname;
    }

    protected function snakeToCamel(string $command): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $command));
    }
}
