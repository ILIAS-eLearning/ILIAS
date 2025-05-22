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
 * @author Alexander Killing <killing@leifos.de>
 */
class NewsContextTest extends TestCase
{
    protected function tearDown(): void
    {
    }

    /**
     * Test admin view
     */
    public function testContextProperties(): void
    {
        $context = new ilNewsContext(
            1,
            "otype",
            2,
            "osubtype"
        );

        $this->assertEquals(
            1,
            $context->getObjId()
        );
        $this->assertEquals(
            "otype",
            $context->getObjType()
        );
        $this->assertEquals(
            2,
            $context->getSubId()
        );
        $this->assertEquals(
            "osubtype",
            $context->getSubType()
        );
    }
}
