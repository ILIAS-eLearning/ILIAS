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

namespace ILIAS\Filesystem\Definitions;

use ILIAS\DI\Container;

/**
 * @author                 Fabian Schmid <fabian@sr.solutions>
 */
class Definitions
{
    protected SuffixDefinitions $suffix_definitions;

    public function __construct(Container $c)
    {
        $this->suffix_definitions = $c['filesystem.definitions'];
    }

    public function suffix(): SuffixDefinitions
    {
        return $this->suffix_definitions;
    }
}
