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

namespace ILIAS\Repository\HTML;

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP;
use ILIAS\FileDelivery\Delivery;

class HTMLUtil
{
    public function __construct()
    {
    }

    public function escape(string $input): string
    {
        return htmlentities($input);
    }

    public function strip(string $input): string
    {
        // see https://www.ilias.de/mantis/view.php?id=19727
        $str = \ilUtil::stripSlashes($input);
        if ($str !== $input) {
            $str = \ilUtil::stripSlashes(str_replace("<", "< ", $input));
        }
        return $str;
    }
}
