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

use PHPUnit\Framework\TestCase;

/**
 * Test session repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class YUITest extends TestCase
{
    protected function tearDown(): void
    {
    }

    /**
     * Test sort
     */
    public function testPath(): void
    {
        $path = ilYuiUtil::getLocalPath("test.js");
        $this->assertEquals(
            "./libs/bower/bower_components/yui2/build/test.js",
            $path
        );
    }
}
