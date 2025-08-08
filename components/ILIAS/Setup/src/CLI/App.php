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

namespace ILIAS\Setup\CLI;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use ILIAS\Component\Component;
use ILIAS\Component\EntryPoint;

/**
 * The ILIAS-setup-console-application.
 *
 * TODO: Add some metainformation to the app, such as name.
 */
class App extends Application implements EntryPoint
{
    public const NAME = "The ILIAS Setup";

    public function __construct(
        Command ...$commands
    ) {
        parent::__construct(self::NAME);
        foreach ($commands as $c) {
            $this->add($c);
        }
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function enter(): int
    {
        $this->verifyIliasRoot();
        return $this->run();
    }

    private function verifyIliasRoot(): void
    {
        $ilroot = realpath(__DIR__ . '/../../../../../');
        if (getcwd() !== $ilroot) {
            $msg = "Please run the setup from ILIAS root - "
                . "there are components using relative pathes.\n";
            print $msg;
            exit();
        }
    }
}
