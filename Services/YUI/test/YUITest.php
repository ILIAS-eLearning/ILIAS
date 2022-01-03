<?php

use PHPUnit\Framework\TestCase;

/**
 * Test session repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class YUITest extends TestCase
{
    protected function tearDown() : void
    {
    }

    /**
     * Test sort
     */
    public function testPath()
    {
        $path = ilYuiUtil::getLocalPath("test.js");
        $this->assertEquals(
            "./libs/bower/bower_components/yui2/build/test.js",
            $path
        );
    }
}
