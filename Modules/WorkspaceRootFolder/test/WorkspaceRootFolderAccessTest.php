<?php

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class WorkspaceRootFolderAccessTest extends TestCase
{
    protected function setUp() : void
    {
        parent::setUp();
    }

    protected function tearDown() : void
    {
    }

    /**
     * Test access commands
     */
    public function testAccessCmds()
    {
        $cmds = ilObjWorkspaceRootFolderAccess::_getCommands();

        $this->assertIsArray($cmds);
    }
}
