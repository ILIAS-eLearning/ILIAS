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
class AccessibilityCriterionConfigTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
    }

    public function testToJson()
    {
        $config = new ilAccessibilityCriterionConfig(["foo" => "bar"]);
        $this->assertEquals(
            '{"foo":"bar"}',
            $config->toJson()
        );
    }
}
