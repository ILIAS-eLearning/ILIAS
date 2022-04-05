<?php

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class MepClipboardSessionRepositoryTest extends TestCase
{
    protected \ILIAS\MediaPool\Clipboard\ClipboardSessionRepository $clipboard;

    protected function setUp() : void
    {
        parent::setUp();
        $this->clipboard = new \ILIAS\MediaPool\Clipboard\ClipboardSessionRepository();
    }

    protected function tearDown() : void
    {
    }

    public function testFolder() : void
    {
        $clipboard = $this->clipboard;
        $clipboard->setFolder(4);
        $this->assertEquals(
            4,
            $clipboard->getFolder()
        );
    }

    public function testIds() : void
    {
        $clipboard = $this->clipboard;
        $clipboard->setIds([3,5,7]);
        $this->assertEquals(
            [3,5,7],
            $clipboard->getIds()
        );
    }
}
