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

namespace ILIAS\COPage\ID;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ContentIdGenerator implements ContentIdGeneratorInterface
{
    public function __construct()
    {
    }

    public function generate(): string
    {
        $random = new \ilRandom();
        return md5((string) ($random->int(1, 9999999) + str_replace(" ", "", (string) microtime())));
    }
}
