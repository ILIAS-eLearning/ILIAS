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
 ********************************************************************
 */

use PHPUnit\Framework\TestCase;

/**
 * Test class ilPollBlock
 * @author Thomas Famula <famula@leifos.de>
 */
class PollBlockTest extends TestCase
{
    protected ilPollBlock $poll_block;

    protected function setGlobalVariable(string $name, $value): void
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = static function (\ILIAS\DI\Container $c) use ($value) {
            return $value;
        };
    }


    protected function setUp(): void
    {
        parent::setUp();

        $dic = new ILIAS\DI\Container();
        $GLOBALS['DIC'] = $dic;

        $db = $this->createMock(ilDBInterface::class);
        $lng = $this->createMock(ilLanguage::class);

        $this->setGlobalVariable(
            "ilDB",
            $db
        );
        $this->setGlobalVariable(
            "lng",
            $lng
        );

        $this->poll_block = new ilPollBlock();
    }

    protected function tearDown(): void
    {
    }

    public function testSetTitle(): void
    {
        $pb = $this->poll_block;
        $pb->setTitle("A poll block");

        $this->assertEquals(
            "A poll block",
            $pb->getTitle()
        );
    }
}
