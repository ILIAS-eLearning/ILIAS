<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Example cache class. This class shoul fit two purposes
 * - As an example of the abstract ilCache class
 * - As a class that is used by unit tests for testing the ilCache class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExampleCache extends ilCache
{
    public function __construct()
    {
        parent::__construct("ServicesCache", "Example", false);
        $this->setExpiresAfter(5);		// only five seconds to make a hit
    }
}
