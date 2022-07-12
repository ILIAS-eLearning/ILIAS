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
 
namespace ILIAS\UI\Interfaces;

use ILIAS\UI\Component as C;

/**
 * Some Random Comment
 */
interface ProperEntry
{
    /**
     * ---
     * description:
     *   purpose:
     *   composition:
     *   effect:
     *
     * rules:
     *   usage:
     *   ordering:
     *   responsiveness:
     *   accessibility:
     * ---
     *
     * @return  tests\UI\Crawler\Fixture\LoopFactory
     */
    public function component1();

    /**
     * ---
     * description:
     *   purpose:
     *   composition:
     *   effect:
     *
     * rules:
     *   usage:
     *   ordering:
     *   responsiveness:
     *   accessibility:
     * ---
     *
     * @return  tests\UI\Crawler\Fixture\ComponentsTreeFixture\Component2\Factory
     */
    public function component2();
}
