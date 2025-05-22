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
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class MepClipboardSessionRepositoryTest extends TestCase
{
    protected \ILIAS\MediaPool\Clipboard\ClipboardSessionRepository $clipboard;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clipboard = new \ILIAS\MediaPool\Clipboard\ClipboardSessionRepository();
    }

    protected function tearDown(): void
    {
    }

    public function testFolder(): void
    {
        $clipboard = $this->clipboard;
        $clipboard->setFolder(4);
        $this->assertEquals(
            4,
            $clipboard->getFolder()
        );
    }

    public function testIds(): void
    {
        $clipboard = $this->clipboard;
        $clipboard->setIds([3,5,7]);
        $this->assertEquals(
            [3,5,7],
            $clipboard->getIds()
        );
    }
}
