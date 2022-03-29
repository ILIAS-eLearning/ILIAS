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

    protected function tearDown() : void
    {
    }

    /**
     * Test get HTML return an array
     */
    public function testGetHTMLReturnsArray() : void
    {
        $plugin_gui = new ilUIHookPluginGUI();
        $res = $plugin_gui->getHTML("Test", "test", []);

        $this->assertIsArray(
            $res
        );
    }
}
