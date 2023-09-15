<?php

use PHPUnit\Framework\TestCase;

/**
 * Test cache
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilCacheTest extends TestCase
{
    // PHP8-Review: Redundant method override
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
    }

    /**
     * Test set expires
     */
    public function testSetExpires()
    {
        $ex_cache = new ilExampleCache();

        $this->assertEquals(
            5,
            $ex_cache->getExpiresAfter()
        );
    }
}
