<?php

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class UIHookPluginGUITest extends TestCase
{
    //protected $backupGlobals = false;

    protected function setUp() : void
    {
        parent::setUp();
    }

    protected function tearDown() : void
    {
    }

    /**
     * Test get HTML return an array
     */
    public function testGetHTMLReturnsArray()
    {
        $plugin_gui = new ilUIHookPluginGUI();
        $res = $plugin_gui->getHTML("Test", "test", []);

        $this->assertIsArray(
            $res
        );
    }
}
