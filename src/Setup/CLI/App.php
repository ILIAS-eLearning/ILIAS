<?php

declare(strict_types=1);

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

namespace ILIAS\Setup\CLI;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

/**
 * The ILIAS-setup-console-application.
 *
 * TODO: Add some metainformation to the app, such as name.
 */
class App extends Application
{
    public const NAME = "The ILIAS Setup";

    public function __construct(Command ...$commands)
    {
        parent::__construct(self::NAME);
        foreach ($commands as $c) {
            $this->add($c);
        }
    }
}
