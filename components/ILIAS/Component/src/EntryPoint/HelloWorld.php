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

namespace ILIAS\Component\EntryPoint;

use ILIAS\Component\EntryPoint;
use ILIAS\Component\Component;

/**
 * A simple entrypoint that just says hello, for testing and documentation
 * purpose.
 */
class HelloWorld extends Base
{
    public function enter(): int
    {
        echo "entry point \"" . $this->getName() . "\" says: \"Hello World!\"\n";
        return 0;
    }
}
